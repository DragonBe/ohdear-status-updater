<?php

namespace OhDear\Status\SlashCommand;

class Message implements MessageInterface
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $teamId;

    /**
     * @var string
     */
    private $teamDomain;

    /**
     * @var string
     */
    private $enterpriseId;

    /**
     * @var string
     */
    private $enterpriseName;

    /**
     * @var string
     */
    private $channelId;

    /**
     * @var string
     */
    private $channelName;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $command;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $responseUrl;

    /**
     * @var string
     */
    private $triggerId;

    /**
     * SlackSlashCommandMessage constructor.
     * @param string $token
     * @param string $teamId
     * @param string $teamDomain
     * @param string $enterpriseId
     * @param string $enterpriseName
     * @param string $channelId
     * @param string $channelName
     * @param string $userId
     * @param string $userName
     * @param string $command
     * @param string $text
     * @param string $responseUrl
     * @param string $triggerId
     */
    public function __construct(
        string $token = '',
        string $teamId = '',
        string $teamDomain = '',
        string $enterpriseId = '',
        string $enterpriseName = '',
        string $channelId = '',
        string $channelName = '',
        string $userId = '',
        string $userName = '',
        string $command = '',
        string $text = '',
        string $responseUrl = '',
        string $triggerId = ''
    ) {
        $this->token = $token;
        $this->teamId = $teamId;
        $this->teamDomain = $teamDomain;
        $this->enterpriseId = $enterpriseId;
        $this->enterpriseName = $enterpriseName;
        $this->channelId = $channelId;
        $this->channelName = $channelName;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->command = $command;
        $this->text = $text;
        $this->responseUrl = $responseUrl;
        $this->triggerId = $triggerId;
    }

    /**
     * @inheritdoc
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @inheritdoc
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }

    /**
     * @inheritdoc
     */
    public function getTeamDomain(): string
    {
        return $this->teamDomain;
    }

    /**
     * @inheritdoc
     */
    public function getEnterpriseId(): string
    {
        return $this->enterpriseId;
    }

    /**
     * @inheritdoc
     */
    public function getEnterpriseName(): string
    {
        return $this->enterpriseName;
    }

    /**
     * @inheritdoc
     */
    public function getChannelId(): string
    {
        return $this->channelId;
    }

    /**
     * @inheritdoc
     */
    public function getChannelName(): string
    {
        return $this->channelName;
    }

    /**
     * @inheritdoc
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @inheritdoc
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @inheritdoc
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @inheritdoc
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function getResponseUrl(): string
    {
        return $this->responseUrl;
    }

    /**
     * @inheritdoc
     */
    public function getTriggerId(): string
    {
        return $this->triggerId;
    }
}
