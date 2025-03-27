<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Dotenv\Dotenv;

// Загружаем переменные из .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$telegramToken = $_ENV['TELEGRAM_BOT_TOKEN'];
$weatherApiKey  = $_ENV['WEATHER_API_KEY'];
$client = new Client();

$telegramApiUrl = "https://api.telegram.org/bot$telegramToken/";
$update = json_decode(file_get_contents("php://input"), true);
file_put_contents('log.txt', print_r($update, true));

if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'];
    $userMessage = $update['message']['text'];

    // Отправляем запрос в OpenAI
    $client = new Client();
    try {
        $weatherResponse = $client->get("https://api.openweathermap.org/data/2.5/weather", [
            'query' => [
                'q' => $userMessage, // Город, который написал пользователь
                'appid' => $weatherApiKey,
                'units' => 'metric',
                'lang' => 'ru'
            ]
        ]);

        $weatherData = json_decode($weatherResponse->getBody(), true);
        $temp = $weatherData['main']['temp'];
        $desc = $weatherData['weather'][0]['description'];

        $replyText = "Погода в городе $userMessage: $temp, $desc.";

    } catch (Exception $e) {
        $replyText = "Ошибка: Не удалось получить погоду для '$userMessage'.";
    }

    // Отправляем ответ пользователю
    file_get_contents($telegramApiUrl . "sendMessage?chat_id=$chatId&text=" . urlencode($replyText));
}


