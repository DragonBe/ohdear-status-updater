<?php

namespace OhDearTest\Status;

use OhDear\Status\SlackHandler;
use OhDear\Status\SlackHandlerFactory;
use PHPUnit\Framework\TestCase;

class SlackHandlerFactoryTest extends TestCase
{
    /**
     * Test to see if we can use a factory to create a fully
     * instantiated SlackHandler.
     *
     * @covers \OhDear\Status\SlackHandler::__construct
     * @covers \OhDear\Status\SlashCommand\Message::__construct
     * @covers \OhDear\Status\SlashCommand\StatusUpdate::__construct
     * @covers \OhDear\Status\SlackHandlerFactory::create
     */
    public function testFactoryReturnsSlackHandler(): void
    {
        $slackHandler = SlackHandlerFactory::create();
        $this->assertInstanceOf(SlackHandler::class, $slackHandler);
    }
}
