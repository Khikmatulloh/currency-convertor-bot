<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use PDO;

class BotHandler
{
    const TOKEN = "7298564599:AAFWbOtHEMxyARXeOUh696bFqyUOf5RR7NQ";
    const API = "https://api.telegram.org/bot".self::TOKEN."/";

    private Client $http;
    private PDO $pdo;

    public function __construct()
    {
        $this->http = new Client(['base_uri' => self::API]);
        $this->pdo = new PDO('mysql:host=localhost;dbname=conventor1', 'root', 'root');
    }

    public function handleStartCommand(int $chatId): void
    {
        $this->sendMessage($chatId, 'Welcome to Currency Converter Bot. Please chose conversion type:', [
            [
                ['text' => 'USD > UZS', 'callback_data' => 'usd2uzs'],
                ['text' => 'UZS > USD', 'callback_data' => 'uzs2usd']
            ]
        ]);
    }

    public function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];

        switch ($data) {
            case 'usd2uzs':
                $this->sendMessage($chatId, 'Please enter the amount in USD:');
                $this->storeConversionType($chatId, 'usd2uzs');
                break;
            case 'uzs2usd':
                $this->sendMessage($chatId, 'Please enter the amount in UZS:');
                $this->storeConversionType($chatId, 'uzs2usd');
                break;
            default:
                $this->sendMessage($chatId, 'Unknown command.');
                break;
        }
    }

    public function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'];

        if (is_numeric($text)) {
            $conversionType = $this->getLatestConversionType($chatId);
            if ($conversionType) {
                $this->storeAmount($chatId, (float)$text);
                $result = $this->calculateConversion($conversionType, (float)$text);
                $this->sendMessage($chatId, "Conversion result: $result");
            } else {
                $this->sendMessage($chatId, "Please select a conversion type first.");
            }
        } else {
            $this->handleStartCommand($chatId);
        }
    }

    private function storeConversionType(int $chatId, string $conversionType): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO conversions (chat_id, conversion_type) VALUES (:chat_id, :conversion_type)");
        $stmt->execute(['chat_id' => $chatId, 'conversion_type' => $conversionType]);
    }

    private function storeAmount(int $chatId, float $amount): void
    {
        $stmt = $this->pdo->prepare("UPDATE conversions SET amount = :amount WHERE chat_id = :chat_id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute(['amount' => $amount, 'chat_id' => $chatId]);
    }

    private function getLatestConversionType(int $chatId): ?string
    {
        $stmt = $this->pdo->prepare("SELECT conversion_type FROM conversions WHERE chat_id = :chat_id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute(['chat_id' => $chatId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['conversion_type'] : null;
    }

    private function calculateConversion(string $conversionType, float $amount): float
    {
        $conversionRate = [
            'usd2uzs' => 12632.88,
            'uzs2usd' => 1 / 12632.88,
        ];

        return $amount * $conversionRate[$conversionType];
    }

    private function sendMessage(int $chatId, string $text, array $keyboard = []): void
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        if (!empty($keyboard)) {
            $params['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
        }

        $this->http->post('sendMessage', ['form_params' => $params]);
    }
}


