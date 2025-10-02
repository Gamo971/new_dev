<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv->load();
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PHP Cursor Starter</title>
</head>
<body>
    <h1>Ça marche 🎉</h1>
    <p>ENV: <?= htmlspecialchars($_ENV['APP_ENV'] ?? 'local', ENT_QUOTES) ?></p>
    <p>Horodatage: <?= date('c') ?></p>
</body>
</html>