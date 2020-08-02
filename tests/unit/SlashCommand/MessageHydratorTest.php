<?php

namespace OhDearTest\Status\SlashCommand;

use OhDear\Status\SlashCommand\Message;
use OhDear\Status\SlashCommand\ReflectionHydrator;
use OhDear\Status\SlashCommand\MessageInterface;
use PHPUnit\Framework\TestCase;

class MessageHydratorTest extends TestCase
{
    /**
     * Test to see if hydrator can hydrate data into an
     * object.
     *
     * @covers \OhDear\Status\SlashCommand\ReflectionHydrator::hydrate
     * @covers \OhDear\Status\SlashCommand\ReflectionHydrator::camelToSnakeConvert
     * @covers \OhDear\Status\SlashCommand\Message::__construct
     * @covers \OhDear\Status\SlashCommand\Message::getToken
     * @covers \OhDear\Status\SlashCommand\Message::getTeamId
     */
    public function testHydrationOfData(): void
    {
        $object = new Message();

        $data = [
            'token' => 'Foo',
            'team_id' => 'Bar',
            'team_domain' => 'foobar.com',
            'channel_id' => 'CN0001',
            'channel_name' => 'testing',
            'user_id' => 'UN0001',
            'user_name' => 'Tester',
            'command' => '/ohdear',
            'text' => 'info "Innie Minnie Mynnie Moo',
            'response_url' => 'https://slack.com',
            'trigger_id' => '123.456.789',
        ];

        $hydrator = new ReflectionHydrator();
        $newObject = $hydrator->hydrate($object, $data);

        $this->assertInstanceOf(
            MessageInterface::class,
            $newObject,
            'Expecting object to be returned implementing ' . MessageInterface::class
        );
        $this->assertSame(
            $data['token'],
            $newObject->getToken(),
            'Expecting value for property token to be the same'
        );
        $this->assertSame(
            $data['team_id'],
            $newObject->getTeamId(),
            'Expecting value for property teamId to be the same'
        );
    }

    /**
     * Testing that we can extract data of an object
     *
     * @covers \OhDear\Status\SlashCommand\Message::__construct
     * @covers \OhDear\Status\SlashCommand\ReflectionHydrator::extract
     * @covers \OhDear\Status\SlashCommand\ReflectionHydrator::camelToSnakeConvert
     */
    public function testExtractionOfData(): void
    {
        $data = [
            'token' => 'Foo',
            'team_id' => 'Bar',
            'team_domain' => 'foobar.com',
            'enterprise_id' => '',
            'enterprise_name' => '',
            'channel_id' => 'CN0001',
            'channel_name' => 'testing',
            'user_id' => 'UN0001',
            'user_name' => 'Tester',
            'command' => '/ohdear',
            'text' => 'info "Innie Minnie Mynnie Moo',
            'response_url' => 'https://slack.com',
            'trigger_id' => '123.456.789',
        ];
        $message = new Message(
            $data['token'],
            $data['team_id'],
            $data['team_domain'],
            $data['enterprise_id'],
            $data['enterprise_name'],
            $data['channel_id'],
            $data['channel_name'],
            $data['user_id'],
            $data['user_name'],
            $data['command'],
            $data['text'],
            $data['response_url'],
            $data['trigger_id'],
        );

        $hydrator = new ReflectionHydrator();
        $resultData = $hydrator->extract($message);
        $this->assertSame(
            $data,
            $resultData,
            'Expected data extraction failed to match expectation'
        );
    }
}
