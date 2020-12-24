<?php

namespace SimpleDaemon\Command;

use Doctrine\ORM\EntityManager;
use SimpleDaemon\Entity\Tasks;
use SimpleDaemon\Repository\TasksRepository;
use SimpleDaemon\Service\TelegramService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpml\Classification\KNearestNeighbors;
use Phpml\Dataset\CsvDataset;
use Phpml\Regression\LeastSquares;

class TasksCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TelegramService
     */
    private $telegramService;

    /**
     * TasksController constructor.
     * @param EntityManager $entityManager
     * @param TelegramService $telegramService
     */
    public function __construct(EntityManager $entityManager, TelegramService $telegramService)
    {
        $this->entityManager = $entityManager;
        $this->telegramService = $telegramService;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            // имя команды (часть после "bin/console")
            ->setName('app:work')
            // краткое описание, отображающееся при запуске "php bin/console list"
            ->setDescription('Worked a new task.')
            // параметр задержки, добавлен для реализации множественного запуска планировщиком
            ->addArgument('sleep', InputArgument::OPTIONAL, 'задержка')
            // полное описание команды, отображающееся при запуске команды
            // с опцией "--help"
            ->setHelp('This command allows you to work a task...');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sleep = $input->getArgument('sleep');
        if ($sleep) {
            $output->writeln([
                'Task will start running in ' . $sleep,
                '============',
                '',
            ]);
            sleep((int)$sleep);
        }

        // выводит множество строк в консоль (добавляя "\n" в конце каждой строки)
        $output->writeln([
            'Task work start',
            '============',
            '',
        ]);

        $this->generateAction();

        // выводит сообщение с последующим "\n"
        $output->writeln('Ух ты!');
        // выводит сообщение, не добавляя "\n" в конце строки
        $output->write('Вы обработали задачу');
    }

    /**
     * Генерация прогноза
     */
    public function generateAction()
    {
        try {
            /** @var TasksRepository $tasksRepository */
            $tasksRepository = $this->entityManager->getRepository(Tasks::class);
            $activeInstancesCount = $tasksRepository->getCount(Tasks::STATUS_IN_PROGRESS, Tasks::TYPE_FIBONACCI);

            if ($activeInstancesCount >= 10) {
                return false;
            }
            $result = [];
            $tasks = $tasksRepository->getNewTasksByType(Tasks::TYPE_LEAST_SQUARES, 1);

            if (!empty($tasks)) {
                /** @var Tasks $task */
                foreach ($tasks as $task) {
                    try {
                        $tasksRepository->updateTasksById($task->getId(), Tasks::STATUS_IN_PROGRESS);

                        $params = json_decode($task->getParams(), true);
                        $chatId = $params['message']['chat']['id'] ?? 835529146;

                        $targetDate = isset($params['toDate']) && $params['toDate']
                            ? date('Y-m-d', strtotime($params['toDate']))
                            : date('Y-m-d', strtotime('+ 7 day'));

                        $alias = 'tsla';

                        $result['plus'] = $this->neuralAnalysis($alias, $targetDate);
                        $result['minus'] = $this->neuralAnalysisMinus($alias, $targetDate);

                        /* набросок реализации фибоначи
                         $from = $result['minus'] ?? 1;
                         $to = $result['plus'] ?? 30;
                         $to = $to + 7; // перспектива на будушее = 1 еденица по умолчанию

                         for ($n = $from; $n <= $to; $n++) {
                             $result['plus'] = $this->fibonacciPlus($n);
                         }

                         for ($n = $from; $n <= $to; $n++) {
                             $result['minus'] = $this->fibonacciMinus($n);
                         }*/

                        $message = $this->generateMessage($targetDate, $alias, $result, $chatId);

                        $this->log('debug', [$message]);

                        $tasksRepository->updateTasksById($task->getId(), Tasks::STATUS_COMPLETED);

                    } catch (\Exception $e) {
                        $tasksRepository->setTaskErrorById($task->getId(), $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log('critical', [$e->getMessage(), $e->getTraceAsString()]);
        }
    }

    /**
     * @param null $targetDate
     * @param array $params
     * @param int $chatId
     * @return array
     */
    private function generateMessage($targetDate, $alias, $params = [], $chatId = 835529146)
    {

        $text = 'Результат ' . strtoupper($alias) . ': ' . PHP_EOL;
        if (isset($params['plus']) && $params['plus']) {
            $text .= ' - положительный прогноз на ' . $targetDate . ': ' . number_format($params['plus'], 2, ',', ' ') . PHP_EOL;
        }

        if (isset($params['minus']) && $params['minus']) {
            $text .= ' - отрицательный прогноз на ' . $targetDate . ': ' . number_format($params['minus'], 2, ',', ' ') . PHP_EOL;
        }

        $text .= PHP_EOL . PHP_EOL . 'Прогноз котировок основан на ML (machine learning, метод: Линейная регрессия наименьших квадратов).';

        $response = $this->telegramService->sendTelegram(
            'sendMessage',
            [
                'chat_id' => $chatId,
                'text' => $text
            ]
        );

        return $response;
    }

    private function fibonacciPlus($n)
    {
        if ($n < 3) {
            return 1;
        } else {
            return $this->fibonacciPlus($n - 1) + $this->fibonacciPlus($n - 2);
        }
    }

    private function fibonacciMinus($n)
    {
        if ($n < 3) {
            return 1;
        } else {
            return $this->fibonacciMinus($n - 1) - $this->fibonacciMinus($n - 2);
        }
    }

    /**
     * @param $alias
     * @param null $date
     * @return array|mixed
     */
    private function neuralAnalysis($alias, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d', strtotime('+ 7 day'));
        }

        $dataset = new CsvDataset(__DIR__ . '/../../nasdaq/' . strtoupper($alias) . '/' . strtoupper($alias) . '_data.csv', 2, true, ',');

        $samples = $dataset->getSamples();
        $targets = $dataset->getTargets();

        foreach ($samples as &$sample) {
            $sample[0] = strtotime($sample[0]);
        }

        // $samples = [['2010-06-29', 19.0], ['2010-06-30', 25.79], ['2010-07-01', 25.0], ['2010-07-02', 23.0], ['2020-12-11', 615.01], ['2020-12-14', 619.0], ['2020-12-15', 643.28], ['2020-12-16', 628.23], ['2020-12-17', 628.19], ['2020-12-18', 668.9],];
        // $targets = [23.89, 23.83, 21.96, 19.2, 609.99, 639.83, 633.25, 622.77, 655.9, 695.0,];

        $getCountDaysByPeriod = $this->getCountDaysByPeriod($date);

        $getWeekdaysToPeriod = $this->getWeekdaysToPeriod($getCountDaysByPeriod);

        $regression = new LeastSquares();
        $regression->train($samples, $targets);

        $predict = [array_pop($targets)];

        if (count($getWeekdaysToPeriod)) {
            foreach ($getWeekdaysToPeriod as $key => $value) {
                $predict = $regression->predict([
                    [strtotime($value), $predict[0]],
                ]);
            }
        }

        return $predict[0];
    }

    /**
     * @param $alias
     * @param null $date
     * @return array|mixed
     */
    private function neuralAnalysisMinus($alias, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d', strtotime('+ 7 day'));
        }

        $dataset = new CsvDataset(__DIR__ . '/../../nasdaq/' . strtoupper($alias) . '/' . strtoupper($alias) . '_data.csv', 2, true, ',');

        $samples = $dataset->getSamples();
        $targets = $dataset->getTargets();

        foreach ($samples as &$sample) {
            $sample[0] = strtotime($sample[0]);
        }

        $getCountDaysByPeriod = $this->getCountDaysByPeriod($date);

        $getWeekdaysToPeriod = $this->getWeekdaysToPeriod($getCountDaysByPeriod);

        $regression = new LeastSquares();
        $regression->train($samples, $targets);

        $predict = $startPredict = [array_pop($targets)];

        if (count($getWeekdaysToPeriod)) {
            foreach ($getWeekdaysToPeriod as $key => $value) {
                $predict = $regression->predict([
                    [strtotime($value), $predict[0]],
                ]);
            }
        }

        return ($startPredict[0] - ($predict[0] - $startPredict[0]));
    }


    private function getWeekdaysToPeriod($dateCount)
    {
        $result = [];

        for ($i = 1; $i <= $dateCount; $i++) {
            $d = date("D", strtotime("+$i day"));
            if ($d == "Sun" || $d == "Sat") {
                continue;
            } else {
                $result[] = date("Y-m-d", strtotime("+$i day"));
            }
        }

        return $result;
    }

    private function getCountDaysByPeriod($to, $from = null)
    {
        if (!$from) {
            $from = time(); // текущее время (метка времени)
        } else {
            $from = strtotime($from);
        }
        $to = strtotime($to); // какая-то дата в строке (1 января 2017 года)
        $datediff = $from - $to; // получим разность дат (в секундах)

        return abs(floor($datediff / (60 * 60 * 24))); // вычислим количество дней из разности дат
    }

    /**
     * Сохранение лог файла
     *
     * @param $message
     * @param array $data данные для записи
     * @param $filename
     */
    private function log($message, $data, $filename = './var/log/tasksCommand.log')
    {
        $logText = PHP_EOL . '*****************start**********************' . PHP_EOL;
        $logText .= PHP_EOL . '***********' . date('Y-m-d H:i:s') . '*********' . PHP_EOL;
        $logText .= $message . ' ';
        $logText .= json_encode($data) . PHP_EOL;
        $logText .= '******************end**********************' . PHP_EOL;

        file_put_contents($filename, $logText, FILE_APPEND);
    }

}