<?php

namespace OhDearTest\Status;

use DomainException;
use InvalidArgumentException;
use OhDear\Status\HydratorInterface;
use OhDear\Status\SlackHandler;
use OhDear\Status\SlackHandlerFactory;
use OhDear\Status\SlashCommand\MessageInterface;
use OhDear\Status\SlashCommand\StatusUpdate;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SlackHandlerTest extends TestCase
{
    /**
     * @var string
     */
    private $goodSlackMessage = 'token=abc'
        . '&team_id=T123'
        . '&team_domain=test'
        . '&channel_id=C987ABC'
        . '&channel_name=status'
        . '&user_id=U01234AB'
        . '&user_name=test_user'
        . '&command=%2Fohdear'
        . '&text=info+%E2%80%98Slack+test%E2%80%99+%E2%80%98Sending+from+Slack%E2%80%99'
        . '&response_url=https%3A%2F%2Fhooks.slack.com%2Fcommands%2F'
        . '&trigger_id=123467890';

    /**
     * Testing that handler throws an exception when the status page
     * id of Oh Dear! is missing in the request.
     *
     * @covers \OhDear\Status\SlackHandler::__construct
     * @covers \OhDear\Status\SlackHandler::handleRequest
     * @covers \OhDear\Status\SlackHandler::requireFieldsProvided
     */
    public function testHandlerThrowsExceptionForMissingStatusPageId(): void
    {
        $message = $this->createStub(MessageInterface::class);
        $update = $this->createStub(StatusUpdate::class);
        $hydrator = $this->createStub(HydratorInterface::class);
        $post = [];
        $get = [
            SlackHandler::OHDEAR_API_TOKEN => 'abc',
        ];
        $slackHandler = new SlackHandler($post, $get, $hydrator, $message, $update);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Something went wrong processing Oh Dear! configuration');
        $slackHandler->handleRequest();
        $this->fail('Expected exception was not thrown for missing page id');
    }

    /**
     * Testing that handler throws an exception when the API key
     * of Oh Dear! is missing in the request.
     *
     * @covers \OhDear\Status\SlackHandler::__construct
     * @covers \OhDear\Status\SlackHandler::handleRequest
     * @covers \OhDear\Status\SlackHandler::requireFieldsProvided
     */
    public function testHandlerThrowsExceptionForMissingOhDearToken(): void
    {
        $message = $this->createStub(MessageInterface::class);
        $update = $this->createStub(StatusUpdate::class);
        $hydrator = $this->createStub(HydratorInterface::class);
        $post = [];
        $get = [
            SlackHandler::OHDEAR_STATUS_PAGE_ID => '123',
        ];
        $slackHandler = new SlackHandler($post, $get, $hydrator, $message, $update);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Something went wrong processing Oh Dear! configuration');
        $slackHandler->handleRequest();
        $this->fail('Expected exception was not thrown for missing API token');
    }

    /**
     * Returns bad post data that should trigger an exception
     *
     * @return array
     */
    public function badPostDataProvider(): array
    {
        return [
            'missing CMD field' => ['foo=bar&bar=baz&text=123', SlackHandler::SLACK_CMD_COMMAND],
            'missing TXT field' => ['foo=bar&bar=baz&command=foobar', SlackHandler::SLACK_CMD_TEXT],
        ];
    }

    /**
     * Test handler throws exception for missing fields coming
     * from Slack.
     *
     * @param string $rawPost
     * @param string $missingField
     *
     * @covers \OhDear\Status\SlackHandler::__construct
     * @covers \OhDear\Status\SlackHandler::handleRequest
     * @covers \OhDear\Status\SlackHandler::requireFieldsProvided
     * @dataProvider badPostDataProvider
     */
    public function testHandlerThrowsExceptionForMissingSlackFields(string $rawPost, string $missingField): void
    {
        $message = $this->createStub(MessageInterface::class);
        $update = $this->createStub(StatusUpdate::class);
        $hydrator = $this->createStub(HydratorInterface::class);
        $getData = [
            SlackHandler::OHDEAR_STATUS_PAGE_ID => '123',
            SlackHandler::OHDEAR_API_TOKEN => '789123abc098',
        ];
        $postData = [];
        parse_str($rawPost, $postData);
        $slackHandler = new SlackHandler($postData, $getData, $hydrator, $message, $update);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Something went wrong processing Slack data');
        $slackHandler->handleRequest();
        $this->fail('Expected exception was not thrown for missing Slack argument ' . $missingField);
    }

    /**
     * Tests we can handle correct arguments
     *
     * @covers \OhDear\Status\SlackHandler::__construct
     * @covers \OhDear\Status\SlackHandler::handleRequest
     * @covers \OhDear\Status\SlackHandler::requireFieldsProvided
     * @covers \OhDear\Status\SlackHandler::prepareStatusUpdate
     * @covers \OhDear\Status\SlackHandler::scrubInput
     * @covers \OhDear\Status\SlackHandler::splitText
     * @covers \OhDear\Status\SlackHandler::updateOhDear
     */
    public function testHandlerProcessesValidArguments(): void
    {
        $rawPost = 'command=%2Fohdear&text=info%20%22Hellow%22%20%22World%21%22';
        $message = $this->createStub(MessageInterface::class);
        $update = $this->createStub(StatusUpdate::class);
        $hydrator = $this->createStub(HydratorInterface::class);

        $messageData = [];
        $statusData = [];
        $postData = [];
        parse_str($rawPost, $postData);

        $message->method('getText')
            ->will($this->returnValue($postData['text']));
        $hydrator->method('hydrate')
            ->will($this->onConsecutiveCalls($message, $update));
        $hydrator->method('extract')
            ->will($this->onConsecutiveCalls($messageData, $statusData));

        $getData = [
            SlackHandler::OHDEAR_STATUS_PAGE_ID => '123',
            SlackHandler::OHDEAR_API_TOKEN => '789123abc098',
        ];
        $slackHandler = new SlackHandler($postData, $getData, $hydrator, $message, $update);
        SlackHandler::$testMode = true;
        try {
            $slackHandler->handleRequest();
        } catch (DomainException $domainException) {
            $this->fail('Exception should not be triggered here');
        }
        $this->assertTrue(true);
    }
}
