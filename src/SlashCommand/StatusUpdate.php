<?php

namespace OhDear\Status\SlashCommand;

use DateTimeImmutable;

class StatusUpdate implements StatusUpdateInterface
{
    public const OHDEAR_LEVEL_INFO = 'info';
    public const OHDEAR_LEVEL_WARN = 'warning';
    public const OHDEAR_LEVEL_HIGH = 'high';
    public const OHDEAR_LEVEL_RESOLVED = 'resolved';
    public const OHDEAR_LEVEL_SCHEDULED = 'scheduled';

    /**
     * @var string
     */
    private $severity;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $text;

    /**
     * @var DateTimeImmutable
     */
    private $time;

    /**
     * @var bool
     */
    private $pinned;

    /**
     * StatusUpdate constructor.
     * @param string $severity
     * @param string $title
     * @param string $message
     * @param DateTimeImmutable|null $time
     * @param bool $pinned
     */
    public function __construct(
        string $severity = self::OHDEAR_LEVEL_INFO,
        string $title = '',
        string $message = '',
        ?DateTimeImmutable $time = null,
        bool $pinned = false
    ) {
        $this->severity = $severity;
        $this->title = $title;
        $this->text = $message;
        if (null === $time) {
            $time = new DateTimeImmutable();
        }
        $this->time = $time;
        $this->pinned = $pinned;
    }

    /**
     * @inheritDoc
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @inheritDoc
     */
    public function getTime(): DateTimeImmutable
    {
        return $this->time;
    }

    /**
     * @inheritDoc
     */
    public function isPinned(): bool
    {
        return $this->pinned;
    }
}
