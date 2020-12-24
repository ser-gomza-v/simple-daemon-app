<?php

namespace SimpleDaemon\Service;

/**
 * Class TelegramService
 */
class TelegramService
{
    /**
     * @var string
     */
    const BOT_NAME = 'QuotesForecastBot';

    /**
     * @var string
     */
    const BOT_TITLE = 'Forecasts Quotes';

    /**
     * @var string
     */
    const BOT_DESC = 'This bot based on a self-learning neural network analyzes the history of stock and securities quotes on world exchanges and, upon request, gives forecasts.';

    /**
     * перед использованием приложения надо создать бота в телеграм и прописать так колбэк метод для отправки сообщений пользователей, таким запросом:
     * curl -XGET "https://api.telegram.org/тут_токен_телеграм_бота/setWebhook?url=https://domain.com/api/generate-task"
     * в продакшене важно что бы был подтверждённый ssl на хосте где работает сервис
     *
     * @var string
     */
    const TELEGRAM_URL = 'https://api.telegram.org/bot';

    /**
     * @var string
     */
    const TELEGRAM_TOKEN = 'тут_токен_телеграм_бота';


    /**
     * Функция вызова методов API
     *
     * @param $method
     * @param $response
     * @return array
     */
    public function sendTelegram($method, $response)
    {
        $apiUrl = self::TELEGRAM_URL . self::TELEGRAM_TOKEN . '/' . $method;

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

}
