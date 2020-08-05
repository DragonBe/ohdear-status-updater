<?php

namespace OhDear\Status\SlashCommand;

use Exception;

class Response
{
    private const STATUS_PAGE_URL = 'https://ohdear.app/status-pages';

    /**
     * Returns a JSON encoded error message using Slack's preferred
     * syntax.
     *
     * @param Exception $exception
     * @return string
     * @see https://api.slack.com/interactivity/handling#responses
     * @see https://api.slack.com/interactivity/slash-commands#responding_to_commands
     */
    public function returnException(Exception $exception): string
    {
        return json_encode([
            'response_type' => 'ephemeral',
            'text' => 'ERROR: ' . $exception->getMessage(),
        ]);
    }

    /**
     * Returns a JSON encoded status message using Slack's preferred
     * syntax.
     *
     * @param StatusUpdateInterface $statusUpdate
     * @return string
     * @see https://api.slack.com/interactivity/handling#responses
     * @see https://api.slack.com/interactivity/slash-commands#responding_to_commands
     */
    public function returnStatus(StatusUpdateInterface $statusUpdate): string
    {
        return json_encode([
            'response_type' => 'ephemeral',
            'text' => sprintf(
                '%s: %s is published on %s at %s',
                strtoupper($statusUpdate->getSeverity()),
                $statusUpdate->getTitle(),
                sprintf('%s/%d', self::STATUS_PAGE_URL, $statusUpdate->getStatusPageId()),
                $statusUpdate->getTime()->format('Y-m-d H:i')
            ),
        ]);
    }
}
