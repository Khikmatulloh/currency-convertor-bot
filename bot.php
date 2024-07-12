
<?php

require 'currency.php';
use GuzzleHttp\Client;

$token = "7298564599:AAFWbOtHEMxyARXeOUh696bFqyUOf5RR7NQ";
$tgApi = "https://api.telegram.org/bot$token/";

$http     = new Client(['base_uri' => $tgApi]);
$currency = new Currency();
if (isset($update->message)) {
    $message          = $update->message;
    $chat_id          = $message->chat->id;
    $type             = $message->chat->type;
    $miid             = $message->message_id;
    $name             = $message->from->first_name;
    $user             = $message->from->username ?? '';
    $fromid           = $message->from->id;
    $text             = $message->text;
    $title            = $message->chat->title;
    $chatuser         = $message->chat->username;
    $chatuser         = $chatuser ? $chatuser : "Shaxsiy Guruh!";
    $caption          = $message->caption;
    $entities         = $message->entities;
    $entities         = $entities[0];
    $left_chat_member = $message->left_chat_member;
    $new_chat_member  = $message->new_chat_member;
    $photo            = $message->photo;
    $video            = $message->video;
    $audio            = $message->audio;
    $voice            = $message->voice;
    // $reply            = $message->reply_markup;
    // $fchat_id         = $message->forward_from_chat->id;
    $fid              = $message->forward_from_message_id;
}

$input             = explode(':', $text);
$original_currency = $input[0];
$target_currency   = $input[1];
$amount            = (float) $input[2];

$converted_amount = $currency->convert(
    $chat_id,
    $original_currency,
    $target_currency,
    $amount);


$http->post('sendMessage', [
    'form_params' => [
        'chat_id' => $chat_id,
        'text'    => "$converted_amount $target_currency"
    ]
]);

$sql = "SELECT id, chat_id, conversion_type, amount, date FROM conversions";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Currency Converter</title>
        <style>
            table {
                width: 100%;
                border-collapse: collapse;
            }
            table, th, td {
                border: 1px solid black;
            }
            th, td {
                padding: 15px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
        </style>
    </head>
    <body>
        <h2>Currency Converter</h2>
        <p>1 UZS = 12632.88 USD</p>
        <table>
            <tr>
                <th>#</th>
                <th>Chat ID</th>
                <th>Conversion type</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["id"]. "</td>
                <td>" . $row["chat_id"]. "</td>
                <td>" . $row["conversion_type"]. "</td>
                <td>" . $row["amount"]. "</td>
                <td>" . $row["date"]. "</td>
              </tr>";
    }
    echo "</table>
    </body>
    </html>";
} else {
    echo "0 results";
}
$conn->close();
?> 