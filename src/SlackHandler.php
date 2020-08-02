<?php

namespace OhDear\Status;

use DomainException;
use InvalidArgumentException;
use OhDear\Status\SlashCommand\Message;
use OhDear\Status\SlashCommand\MessageInterface;
use OhDear\Status\SlashCommand\StatusUpdate;
use OhDear\Status\SlashCommand\StatusUpdateInterface;

class SlackHandler
{
    private const OHDEAR_STATUS_PAGE_URI = 'https://ohdear.app/api/status-page-updates';
    public const OHDEAR_STATUS_PAGE_ID = 'id';
    public const OHDEAR_API_TOKEN = 'token';
    public const SLACK_CMD_COMMAND = 'command';
    public const SLACK_CMD_TEXT = 'text';

    /**
     * @var bool
     */
    public static $testMode = false;

    /**
     * @var array
     */
    private $postData;

    /**
     * @var array
     */
    private $getData;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var MessageInterface
     */
    private $messagePrototype;

    /**
     * @var StatusUpdateInterface
     */
    private $statusUpdatePrototype;

    /**
     * SlackHandler constructor.
     *
     * @param array $postData
     * @param array $getData
     * @param HydratorInterface $hydrator
     * @param MessageInterface $messagePrototype
     * @param StatusUpdateInterface $statusUpdate
     */
    public function __construct(
        array $postData,
        array $getData,
        HydratorInterface $hydrator,
        MessageInterface $messagePrototype,
        StatusUpdateInterface $statusUpdate
    ) {
        $this->postData = $postData;
        $this->getData = $getData;
        $this->hydrator = $hydrator;
        $this->messagePrototype = $messagePrototype;
        $this->statusUpdatePrototype = $statusUpdate;
    }

    /**
     * Handles the request from Slack and passes it on
     * to Oh Dear! for processing.
     *
     * @throws DomainException
     */
    public function handleRequest(): void
    {
        $requiredGetFields = [
            self::OHDEAR_STATUS_PAGE_ID,
            self::OHDEAR_API_TOKEN,
        ];
        if (true !== $this->requireFieldsProvided($requiredGetFields, $this->getData)) {
            throw new InvalidArgumentException('Something went wrong processing Oh Dear! configuration');
        }
        $requiredPostFields = [
            self::SLACK_CMD_COMMAND,
            self::SLACK_CMD_TEXT,
        ];
        if (true !== $this->requireFieldsProvided($requiredPostFields, $this->postData)) {
            throw new InvalidArgumentException('Something went wrong processing Slack data');
        }

        $slackRequest = $this->hydrator->hydrate($this->messagePrototype, $this->postData);
        if (! $slackRequest instanceof MessageInterface) {
            throw new DomainException('We have problems processing slack request');
        }
        $statusUpdate = $this->prepareStatusUpdate($slackRequest);
        $this->updateOhDear($statusUpdate);
    }

    /**
     * Checks if all required fields are available in a
     * data set and if one is missing boolean FALSE will
     * be returned. Otherwise a boolean TRUE will be
     * returned.
     *
     * @param array $requiredFields
     * @param array $data
     * @return bool
     */
    private function requireFieldsProvided(array $requiredFields, array $data): bool
    {
        foreach ($requiredFields as $requiredField) {
            if (! array_key_exists($requiredField, $data)) {
                return false;
            }
        }
        return true;
    }

    private function prepareStatusUpdate(MessageInterface $slackMessage): StatusUpdateInterface
    {
        $text = $this->scrubInput($slackMessage->getText());
        return $this->splitText($text);
    }

    private function splitText(string $text): StatusUpdateInterface
    {
        $matches = [];
        $pattern = '/^(\w+)\s[^\w](.*)[^\w]\s[^\w](.*)[^\w]$/';
        preg_match($pattern, $text, $matches);

        $items = count($matches);
        if (4 !== $items) {
            throw new InvalidArgumentException('Text was not formatted correctly');
        }
        $allowedLevels = [
            StatusUpdate::OHDEAR_LEVEL_INFO,
            StatusUpdate::OHDEAR_LEVEL_WARN,
            StatusUpdate::OHDEAR_LEVEL_HIGH,
            StatusUpdate::OHDEAR_LEVEL_RESOLVED,
            StatusUpdate::OHDEAR_LEVEL_SCHEDULED,
        ];
        if (! in_array($matches[1], $allowedLevels)) {
            $matches[1] = StatusUpdate::OHDEAR_LEVEL_INFO;
        }
        $data = [
            'severity' => $matches[1],
            'title'    => $matches[2],
            'text'     => $matches[3],
        ];
        $statusUpdate = $this->hydrator->hydrate($this->statusUpdatePrototype, $data);
        if (! $statusUpdate instanceof StatusUpdateInterface) {
            throw new DomainException('We have problems generating a proper Oh Dear! status update');
        }
        return $statusUpdate;
    }

    /**
     * Data from Slack contains some fancy multibyte code
     * which is a bit hard to process. Therefore this scrub
     * process is used to clean up some of it.
     *
     * @param string $rawInput
     * @return string
     */
    private function scrubInput(string $rawInput): string
    {
            $search = [
                mb_chr(8216),
                mb_chr(8217),
                mb_chr(8220),
                mb_chr(8221),
            ];
            $replace = ["'", "'", '"', '"'];
            return str_replace($search, $replace, $rawInput);
    }

    private function updateOhDear(StatusUpdateInterface $statusUpdate): void
    {
        $data = $this->hydrator->extract($statusUpdate);
        $token = $this->getData[self::OHDEAR_API_TOKEN];
        $data['status_page_id'] = $this->getData[self::OHDEAR_STATUS_PAGE_ID];
        $payload = json_encode($data);
        if (self::$testMode) {
            return;
        }
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::OHDEAR_STATUS_PAGE_URI,
            CURLOPT_FAILONERROR    => false,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_POST           => true,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'SlackOhDearStatus/0.0.1',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => $payload,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
    }
}
