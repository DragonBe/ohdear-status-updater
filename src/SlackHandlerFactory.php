<?php

namespace OhDear\Status;

use OhDear\Status\SlashCommand\Message;
use OhDear\Status\SlashCommand\ReflectionHydrator;
use OhDear\Status\SlashCommand\StatusUpdate;

class SlackHandlerFactory
{
    public static function create(array $postData = [], array $getData = []): SlackHandler
    {
        $reflectionHydrator = new ReflectionHydrator();
        $messagePrototype = new Message();
        $statusUpdatePrototype = new StatusUpdate();
        return new SlackHandler($postData, $getData, $reflectionHydrator, $messagePrototype, $statusUpdatePrototype);
    }
}
