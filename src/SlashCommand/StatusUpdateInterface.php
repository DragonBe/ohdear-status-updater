<?php

namespace OhDear\Status\SlashCommand;

use DateTimeImmutable;

interface StatusUpdateInterface
{
    /**
     * The ID of the status update
     *
     * @return int
     */
    public function getId(): int;

    /**
     * The level of status update
     *
     * @return string
     */
    public function getSeverity(): string;

    /**
     * The title of status update
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * The message of status update
     *
     * @return string
     */
    public function getText(): string;

    /**
     * The timestamp of status update
     *
     * @return DateTimeImmutable
     */
    public function getTime(): DateTimeImmutable;

    /**
     * Flag indicating status update was pinned
     *
     * @return bool
     */
    public function isPinned(): bool;

    /**
     * Retrieve the ID of the status page
     *
     * @return int
     */
    public function getStatusPageId(): int;

    /**
     * The response URL to reply back to Slack
     *
     * @return string
     */
    public function getResponseUrl(): string;
}
