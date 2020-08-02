<?php

use OhDear\Status\SlackHandler;
use OhDear\Status\SlashCommand\Message;
use OhDear\Status\SlashCommand\ReflectionHydrator;
use OhDear\Status\SlashCommand\StatusUpdate;

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Brussels');

header('Content-Type: application/json');

$message = new Message();
$update = new StatusUpdate();
$hydrator = new ReflectionHydrator();

$slackHandler = new SlackHandler($_POST, $_GET, $hydrator, $message, $update);
try {
    $slackHandler->handleRequest();
} catch (DomainException $domainException) {
    echo json_encode([
        'status' => 'error',
        'type' => 'domain',
        'message' => $domainException->getMessage(),
    ]);
} catch (InvalidArgumentException $invalidArgumentException) {
    echo json_encode([
        'status' => 'error',
        'type' => 'invalid_argument',
        'message' => $invalidArgumentException->getMessage(),
    ]);
}
echo json_encode([
    'status' => 'success',
    'message' => 'Status page updated',
]);
