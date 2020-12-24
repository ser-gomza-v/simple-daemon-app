<?
$on = @mysqli_connect('localhost', 'rest', 'G9W4OH7m', 'simple_rest');
mysqli_set_charset($on, 'utf8');//кодировка БД
$dmp = 'files/dump.sql';
$GLOBALS['on'] = $on;

#SQL-ЗАПРОСЫ
function sql($q)
{
    @mysqli_query($GLOBALS['on'], $q);
}

define('TOKEN', '1051794234:AAF_F9PpCk0aYZI9zcRRlIM6-Ji-gZlgmEM');

// Функция вызова методов API.
function sendTelegram($method, $response)
{
    $ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/' . $method);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch);

    return $res;

}


$data = file_get_contents('php://input');
$data = json_decode($data, true);

if (empty($data['message']['chat']['id'])) {
    exit();
}

// Ответ на текстовые сообщения.
if (!empty($data['message']['text'])) {
    $text = $data['message']['text'];
    $params = json_encode($data, JSON_UNESCAPED_UNICODE);
    $error = null;
    try {

        $first = substr($text, 1);
        $lenght = strlen($text);
        if ($first == '$' && $lenght < 10) {
            $error = sendTelegram(
                'sendMessage',
                [
                    'chat_id' => $data['message']['chat']['id'],
                    'text' => 'Расчёт прогноза начался, совсем скоро вы получите результат!'
                ]
            );

            sql("INSERT INTO simple_rest.tasks (`type`, `status`, `params`, `text`, `error`)
			VALUES (1, 1, '{$params}', '{$text}', '{$error}');");
        } else {
            $error = sendTelegram(
                'sendMessage',
                [
                    'chat_id' => $data['message']['chat']['id'],
                    'text' => 'Верный запрос прогноза на примере акций Tesla Inc (TSLA) выглядит так $tsla. Прочие сообщения не обрабатываются. Благодарим за понимание.'
                ]
            );
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    exit();
//
//    // Отправка фото.
//    if (mb_stripos($text, 'фото') !== false) {
//        sendTelegram(
//            'sendPhoto',
//            array(
//                'chat_id' => $data['message']['chat']['id'],
//                'photo' => curl_file_create(__DIR__ . '/torin.jpg')
//            )
//        );
//
//        exit();
//    }
//
//    // Отправка файла.
//    if (mb_stripos($text, 'файл') !== false) {
//        sendTelegram(
//            'sendDocument',
//            array(
//                'chat_id' => $data['message']['chat']['id'],
//                'document' => curl_file_create(__DIR__ . '/example.xls')
//            )
//        );
//
//        exit();
//    }
}


