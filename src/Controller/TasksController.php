<?php

namespace SimpleDaemon\Controller;

use SimpleDaemon\Entity\Tasks;
use SimpleDaemon\Service\TasksService;
use SimpleDaemon\Service\TelegramService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TasksController
{

    /**
     * @var TelegramService
     */
    private $telegramService;

    /**
     * @var TasksService
     */
    private $tasksService;

    /**
     * TasksController constructor.
     * @param TelegramService $telegramService
     * @param TasksService $tasksService
     */
    public function __construct(TelegramService $telegramService, TasksService $tasksService)
    {
        $this->telegramService = $telegramService;
        $this->tasksService = $tasksService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function generateAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!empty($data['message']['chat']['id']) && !empty($data['message']['text'])) {
            $text = $data['message']['text'];
            $params = json_encode($data, JSON_UNESCAPED_UNICODE);
            $error = null;
            try {
                $first = mb_substr($text, 0, 1);
                $length = strlen($text);

                if ($first == '$' && $length < 10) {
                    $this->telegramService->sendTelegram(
                        'sendMessage',
                        [
                            'chat_id' => $data['message']['chat']['id'],
                            'text' => 'Расчёт прогноза начался, совсем скоро вы получите результат!'
                        ]
                    );

                    $this->tasksService->createNewTask(Tasks::TYPE_LEAST_SQUARES, $params);
                } else {
                    $this->telegramService->sendTelegram(
                        'sendMessage',
                        [
                            'chat_id' => $data['message']['chat']['id'],
                            'text' => 'Верный запрос прогноза на примере акций Tesla Inc (TSLA) выглядит так: $tsla, запрос может быть до 10 символов, регист не важен. Прочие сообщения не обрабатываются. Благодарим за понимание.'
                        ]
                    );
                }

                $response = new JsonResponse([
                    'data' => [
                        'status' => 'success'
                    ],
                    200
                ]);

                return $response;
            } catch (\Exception $e) {
                $response = new JsonResponse([
                    'data' => [
                        'status' => 'failed',
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ],
                    500
                ]);
            }
        } else {
            $response = new JsonResponse([
                'data' => [
                    'status' => 'failed',
                    'message' => 'Укажите обязательные поля'
                ],
                400
            ]);
        }

        return $response;
    }

}