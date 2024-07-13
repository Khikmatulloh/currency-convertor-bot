<?php


require 'dashboard.php';

require 'vendor/autoload.php';

$update = json_decode(file_get_contents('php://input'),true);

if (isset($update)) {
    require 'bot.php';
    return;
}

$botHandler = new BotHandler();

if (isset($update['message'])) {
    $botHandler->handleMessage($update['message']);
} elseif (isset($update['callback_query'])) {
    $botHandler->handleCallbackQuery($update['callback_query']);
}

require 'db.php';

try {
    $pdo = DB::connect();
    $stmt = $pdo->query('SELECT  chat_id, amount, status, created_at FROM users');
    $conversions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    die();
}

?>

<!DOCTYPE html>
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
    <p align=right>1 USD = 12632.88 UZS</p>
    <table>
        <tr>
            <th>Chat ID</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
        <?php foreach ($conversions as $conversion): ?>
            <tr>
                <td><?php echo htmlspecialchars((string) $conversion['chat_id']); ?></td>
                <td><?php echo htmlspecialchars((string) $conversion['amount']); ?></td>
                <td><?php echo htmlspecialchars((string) $conversion['status']); ?></td>
                <td><?php echo htmlspecialchars((string) $conversion['created_at']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
