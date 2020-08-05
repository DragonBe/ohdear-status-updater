<?php

namespace OhDearTest\Status\SlashCommand;

use DateTimeImmutable;
use Exception;
use OhDear\Status\SlashCommand\Response;
use OhDear\Status\SlashCommand\StatusUpdateInterface;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /**
     * Test error response formats correctly
     *
     * @covers \OhDear\Status\SlashCommand\Response::returnException
     */
    public function testErrorResponseFormatsCorrectly(): void
    {
        $expectedResponse = json_encode([
            'response_type' => 'ephemeral',
            'text' => 'ERROR: This is an exception',
        ]);
        $exception = new Exception('This is an exception');
        $slackResponse = new Response();
        $response = $slackResponse->returnException($exception);
        $this->assertSame($expectedResponse, $response, 'Response does not match expected response');
    }

    /**
     * Test successful response formats correctly
     *
     * @covers \OhDear\Status\SlashCommand\Response::returnStatus
     */
    public function testSuccessfulResponseFormatsCorrectly(): void
    {
        $expectedResponse = json_encode([
            'response_type' => 'ephemeral',
            'text' => 'INFO: Foo is published on https://ohdear.app/status-pages/456 at 2020-03-12 08:30',
        ]);
        $time = new DateTimeImmutable('2020-03-12 08:30:00');
        $statusUpdate = $this->createStub(StatusUpdateInterface::class);
        $statusUpdate->method('getId')->will($this->returnValue(123));
        $statusUpdate->method('getSeverity')->will($this->returnValue('info'));
        $statusUpdate->method('getTitle')->will($this->returnValue('Foo'));
        $statusUpdate->method('getText')->will($this->returnValue('This is foo'));
        $statusUpdate->method('getTime')->will($this->returnValue($time));
        $statusUpdate->method('isPinned')->will($this->returnValue(false));
        $statusUpdate->method('getStatusPageId')->will($this->returnValue(456));
        $slackResponse = new Response();
        $response = $slackResponse->returnStatus($statusUpdate);
        $this->assertSame($expectedResponse, $response, 'Response does not match expected response');
    }
}
