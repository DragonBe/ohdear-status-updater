<?php

namespace OhDear\Status\SlashCommand;

interface MessageInterface
{
    /**
     * @return string
     */
    public function getToken(): string;

    /**
     * @return string
     */
    public function getTeamId(): string;

    /**
     * @return string
     */
    public function getTeamDomain(): string;

    /**
     * @return string
     */
    public function getEnterpriseId(): string;

    /**
     * @return string
     */
    public function getEnterpriseName(): string;

    /**
     * @return string
     */
    public function getChannelId(): string;

    /**
     * @return string
     */
    public function getChannelName(): string;

    /**
     * @return string
     */
    public function getUserId(): string;

    /**
     * @return string
     */
    public function getUserName(): string;

    /**
     * @return string
     */
    public function getCommand(): string;

    /**
     * @return string
     */
    public function getText(): string;

    /**
     * @return string
     */
    public function getResponseUrl(): string;

    /**
     * @return string
     */
    public function getTriggerId(): string;
}
