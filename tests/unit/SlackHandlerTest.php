<?php

namespace OhDearTest\Status;

use DomainException;
use InvalidArgumentException;
use OhDear\Status\HydratorInterface;
use OhDear\Status\SlackHandler;
use OhDear\Status\SlashCommand\Message;
use OhDear\Status\SlashCommand\MessageInterface;
use OhDear\Status\SlashCommand\ReflectionHydrator;
use OhDear\Status\SlashCommand\StatusUpdate;
use OhDear\Status\SlashCommand\StatusUpdateInterface;
use PHPUnit\Framework\TestCase;
use StdClass;

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
     * Test handler throws exception when hydration of data fails
     *
     * @covers \OhDear\Status\SlackHandler::__construct
     * @covers \OhDear\Status\SlackHandler::requireFieldsProvided
     * @covers \OhDear\Status\SlackHandler::handleRequest
     */
    public function testHandlerThrowsExceptionWhenHydrationFails(): void
    {
        $message = $this->createStub(MessageInterface::class);
        $update = $this->createStub(StatusUpdate::class);
        $hydrator = $this->createStub(HydratorInterface::class);
        $hydrator->method('hydrate')
            ->will($this->returnValue(new StdClass()));
        $post = [
            SlackHandler::SLACK_CMD_TEXT => 'foobar',
            SlackHandler::SLACK_CMD_COMMAND => '/ohdear',
        ];
        $get = [
            SlackHandler::OHDEAR_STATUS_PAGE_ID => 1,
            SlackHandler::OHDEAR_API_TOKEN => 1,
        ];
        $slackHandler = new SlackHandler($post, $get, $hydrator, $message, $update);
        $this->expectException(DomainException::class);
        $slackHandler->handleRequest();
        $this->fail('Expected exception was not triggered for failed hydration of data');
    }

    public function rawPostProvider(): array
    {
        return [
            'Update of level info' => [
                'command=%2Ffoo&text=info%20%22Hello%22%20%22World%21%22',
                'info',
            ],
            'Update of level warning' => [
                'command=%2Ffoo&text=warning%20%22Hello%22%20%22World%21%22',
                'warning',
            ],
            'Update of level high' => [
                'command=%2Ffoo&text=high%20%22Hello%22%20%22World%21%22',
                'high',
            ],
            'Update of level resolved' => [
                'command=%2Ffoo&text=resolved%20%22Hello%22%20%22World%21%22',
                'resolved',
            ],
            'Update of level scheduled' => [
                'command=%2Ffoo&text=scheduled%20%22Hello%22%20%22World%21%22',
                'scheduled',
            ],
            'Update of level wrong' => [
                'command=%2Ffoo&text=wrong%20%22Hello%22%20%22World%21%22',
                'info',
            ],
        ];
    }

    /**
     * Tests we can handle correct arguments
     *
     * @param string $rawPost
     * @param string $level
     *
     * @covers \OhDear\Status\SlashCommand\ReflectionHydrator::camelToSnakeConvert
     * @covers \OhDear\Status\SlashCommand\ReflectionHydrator::extract
     * @covers \OhDear\Status\SlashCommand\ReflectionHydrator::hydrate
     * @covers \OhDear\Status\SlashCommand\StatusUpdate::__construct
     * @covers \OhDear\Status\SlashCommand\StatusUpdate::getSeverity
     * @covers \OhDear\Status\SlackHandler::__construct
     * @covers \OhDear\Status\SlackHandler::handleRequest
     * @covers \OhDear\Status\SlackHandler::requireFieldsProvided
     * @covers \OhDear\Status\SlackHandler::prepareStatusUpdate
     * @covers \OhDear\Status\SlackHandler::scrubInput
     * @covers \OhDear\Status\SlackHandler::splitText
     * @covers \OhDear\Status\SlackHandler::updateOhDear
     * @dataProvider rawPostProvider
     */
    public function testHandlerProcessesValidArguments(string $rawPost, string $level): void
    {
        $message = $this->createStub(MessageInterface::class);
        $update = new StatusUpdate();
        $hydrator = new ReflectionHydrator();

        $postData = [];
        parse_str($rawPost, $postData);

        $message->method('getText')
            ->will($this->returnValue($postData['text']));
        $message->method('getCommand')
            ->will($this->returnValue($postData['command']));

        $getData = [
            SlackHandler::OHDEAR_STATUS_PAGE_ID => '123',
            SlackHandler::OHDEAR_API_TOKEN => '789123abc098',
        ];
        $slackHandler = new SlackHandler($postData, $getData, $hydrator, $message, $update);
        SlackHandler::$testMode = true;
        $statusUpdate = null;
        try {
            $statusUpdate = $slackHandler->handleRequest();
        } catch (DomainException $domainException) {
            $this->fail('Exception should not be triggered here');
        }
        $this->assertInstanceOf(
            StatusUpdateInterface::class,
            $statusUpdate,
            'Expecting an instance of Status Update Interface'
        );
        $this->assertSame(
            $level,
            $statusUpdate->getSeverity(),
            'Expecting the severity to be of type ' . $level
        );
    }

    public function rawUnscrubbedProvider(): array
    {
        return [
            'Update of multi-byte text 8216' => [
                'command=%2Ffoo&text=info%20%E2%80%98Hello%E2%80%98%20%E2%80%98World%21%E2%80%98',
                'Hello',
                'World!',
            ],
            'Update of multi-byte text 8217' => [
                'command=%2Ffoo&text=info%20%E2%80%99Hello%E2%80%99%20%E2%80%99World%21%E2%80%99',
                'Hello',
                'World!',
            ],
            'Update of multi-byte text 8220' => [
                'command=%2Ffoo&text=info%20%E2%80%9CHello%E2%80%9C%20%E2%80%9CWorld%21%E2%80%9C',
                'Hello',
                'World!',
            ],
            'Update of multi-byte text 8221' => [
                'command=%2Ffoo&text=info%20%E2%80%9DHello%E2%80%9D%20%E2%80%9DWorld%21%E2%80%9D',
                'Hello',
                'World!',
            ],
        ];
    }

    /**
     * Tests we can scrub text correctly
     *
     * @param string $rawPost
     * @param string $title
     * @param string $text
     *
     * @covers \OhDear\Status\SlashCommand\Message::__construct
     * @covers \OhDear\Status\SlashCommand\Message::getText
     * @covers \OhDear\Status\SlashCommand\ReflectionHydrator::camelToSnakeConvert
     * @covers \OhDear\Status\SlashCommand\ReflectionHydrator::extract
     * @covers \OhDear\Status\SlashCommand\ReflectionHydrator::hydrate
     * @covers \OhDear\Status\SlashCommand\StatusUpdate::__construct
     * @covers \OhDear\Status\SlashCommand\StatusUpdate::getText
     * @covers \OhDear\Status\SlashCommand\StatusUpdate::getTitle
     * @covers \OhDear\Status\SlackHandler::__construct
     * @covers \OhDear\Status\SlackHandler::handleRequest
     * @covers \OhDear\Status\SlackHandler::requireFieldsProvided
     * @covers \OhDear\Status\SlackHandler::prepareStatusUpdate
     * @covers \OhDear\Status\SlackHandler::scrubInput
     * @covers \OhDear\Status\SlackHandler::splitText
     * @covers \OhDear\Status\SlackHandler::updateOhDear
     * @dataProvider rawUnscrubbedProvider
     * @group Integration
     */
    public function testHandlerCanScrubTextCorrectly(string $rawPost, string $title, string $text): void
    {
        $message = new Message();
        $update = new StatusUpdate();
        $hydrator = new ReflectionHydrator();

        $postData = [];
        parse_str($rawPost, $postData);

        $getData = [
            SlackHandler::OHDEAR_STATUS_PAGE_ID => '123',
            SlackHandler::OHDEAR_API_TOKEN => '789123abc098',
        ];
        $slackHandler = new SlackHandler($postData, $getData, $hydrator, $message, $update);
        SlackHandler::$testMode = true;
        $statusUpdate = null;
        try {
            $statusUpdate = $slackHandler->handleRequest();
        } catch (DomainException $domainException) {
            $this->fail('Exception should not be triggered here');
        }
        $this->assertInstanceOf(
            StatusUpdateInterface::class,
            $statusUpdate,
            'Expecting an instance of Status Update Interface'
        );
        $this->assertSame(
            $title,
            $statusUpdate->getTitle(),
            'Expecting the title to be ' . $title
        );
        $this->assertSame(
            $text,
            $statusUpdate->getText(),
            'Expecting the text to be ' . $text
        );
    }

    /**
     * Tests handler throws exception for wrong formatted text
     *
     * @covers \OhDear\Status\SlackHandler::__construct
     * @covers \OhDear\Status\SlackHandler::handleRequest
     * @covers \OhDear\Status\SlackHandler::requireFieldsProvided
     * @covers \OhDear\Status\SlackHandler::prepareStatusUpdate
     * @covers \OhDear\Status\SlackHandler::scrubInput
     * @covers \OhDear\Status\SlackHandler::splitText
     */
    public function testHandlerThrowsExceptionForWrongFormattedText(): void
    {
        $rawPost = 'command=%2Fohdear&text=info';
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Text was not formatted correctly');
        $slackHandler->handleRequest();
        $this->fail('Expected exception was not thrown for wrong formatted text');
    }

    /**
     * Tests handler selects info as fall back status
     *
     * @covers \OhDear\Status\SlackHandler::__construct
     * @covers \OhDear\Status\SlackHandler::handleRequest
     * @covers \OhDear\Status\SlackHandler::requireFieldsProvided
     * @covers \OhDear\Status\SlackHandler::prepareStatusUpdate
     * @covers \OhDear\Status\SlackHandler::scrubInput
     * @covers \OhDear\Status\SlackHandler::splitText
     * @covers \OhDear\Status\SlackHandler::updateOhDear
     */
    public function testHandlerSelectsInfoAsFallBackStatus(): void
    {
        $rawPost = 'command=%2Fohdear&text=wrong%20%22Hellow%22%20%22World%21%22';
        $message = $this->createStub(MessageInterface::class);
        $update = $this->createStub(StatusUpdate::class);
        $update->method('getSeverity')
            ->will($this->returnValue('info'));
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
        $statusUpdate = null;
        try {
            $statusUpdate = $slackHandler->handleRequest();
        } catch (DomainException $domainException) {
            $this->fail('Exception should not be triggered here');
        }
        $this->assertInstanceOf(
            StatusUpdateInterface::class,
            $statusUpdate,
            'Expecting an instance of Status Update Interface'
        );
        $this->assertSame(
            StatusUpdate::OHDEAR_LEVEL_INFO,
            $statusUpdate->getSeverity(),
            'Expecting the severity to be of type info'
        );
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
    public function testHandlerFailsWhenStatusUpdateIsNotHydrated(): void
    {
        $rawPost = 'command=%2Fohdear&text=wrong%20%22Hellow%22%20%22World%21%22';
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
            ->will($this->onConsecutiveCalls($message, new StdClass()));
        $hydrator->method('extract')
            ->will($this->onConsecutiveCalls($messageData, $statusData));

        $getData = [
            SlackHandler::OHDEAR_STATUS_PAGE_ID => '123',
            SlackHandler::OHDEAR_API_TOKEN => '789123abc098',
        ];
        $slackHandler = new SlackHandler($postData, $getData, $hydrator, $message, $update);
        SlackHandler::$testMode = true;

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('We have problems generating a proper Oh Dear! status update');
        $slackHandler->handleRequest();
        $this->fail('Expected exception was not thrown for hydrating status update');
    }
}
