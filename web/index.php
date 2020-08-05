<?php

use OhDear\Status\SlackHandler;
use OhDear\Status\SlashCommand\Message;
use OhDear\Status\SlashCommand\ReflectionHydrator;
use OhDear\Status\SlashCommand\Response;
use OhDear\Status\SlashCommand\StatusUpdate;

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Brussels');

header('Content-Type: application/json');

$message = new Message();
$update = new StatusUpdate();
$hydrator = new ReflectionHydrator();

$slackHandler = new SlackHandler($_POST, $_GET, $hydrator, $message, $update);
$response = new Response();
try {
    $update = $slackHandler->handleRequest();
} catch (DomainException $domainException) {
    echo $response->returnException($domainException);
    return;
} catch (InvalidArgumentException $invalidArgumentException) {
    echo $response->returnException($invalidArgumentException);
    return;
} catch (RuntimeException $runtimeException) {
    echo $response->returnException($runtimeException);
    return;
}
echo $response->returnStatus($update);
