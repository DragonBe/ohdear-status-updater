<?php

namespace OhDearTest\Status\SlashCommand;

use OhDear\Status\SlashCommand\Message;
use OhDear\Status\SlashCommand\MessageInterface;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * Test to see we implement MessageInterface
     *
     * @covers \OhDear\Status\SlashCommand\Message::__construct
     */
    public function testMessageEntityImplementsMessageInterface(): void
    {
        $message = new Message();
        $this->assertInstanceOf(MessageInterface::class, $message);
    }

    /**
     * Test entity can set and retrieve data
     *
     * @covers \OhDear\Status\SlashCommand\Message
     */
    public function testMessageEntityCanSetValuesAtConstruct(): void
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
        $this->assertSame($data['token'], $message->getToken());
        $this->assertSame($data['team_id'], $message->getTeamId());
        $this->assertSame($data['team_domain'], $message->getTeamDomain());
        $this->assertSame($data['enterprise_id'], $message->getEnterpriseId());
        $this->assertSame($data['enterprise_name'], $message->getEnterpriseName());
        $this->assertSame($data['channel_id'], $message->getChannelId());
        $this->assertSame($data['channel_name'], $message->getChannelName());
        $this->assertSame($data['user_id'], $message->getUserId());
        $this->assertSame($data['user_name'], $message->getUserName());
        $this->assertSame($data['command'], $message->getCommand());
        $this->assertSame($data['text'], $message->getText());
        $this->assertSame($data['response_url'], $message->getResponseUrl());
        $this->assertSame($data['trigger_id'], $message->getTriggerId());
    }
}
