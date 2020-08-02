<?php

namespace OhDear\Status\SlashCommand;

use DateTimeImmutable;

interface StatusUpdateInterface
{
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
}
