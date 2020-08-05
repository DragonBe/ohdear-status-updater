<?php

namespace OhDear\Status;

use DomainException;
use InvalidArgumentException;
use OhDear\Status\SlashCommand\MessageInterface;
use OhDear\Status\SlashCommand\StatusUpdate;
use OhDear\Status\SlashCommand\StatusUpdateInterface;
use RuntimeException;
use function json_decode;

class SlackHandler
{
    private const OHDEAR_STATUS_PAGE_URI  = 'https://ohdear.app/api/status-page-updates';
    private const OHDEAR_USER_AGENT       = 'SlackOhDearStatus/0.0.1';
    private const OHDEAR_DEFAULT_SEVERITY = 0;
    public const OHDEAR_STATUS_PAGE_ID    = 'id';
    public const OHDEAR_API_TOKEN         = 'token';
    public const SLACK_CMD_COMMAND        = 'command';
    public const SLACK_CMD_TEXT           = 'text';

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
     * @return StatusUpdateInterface
     * @throws DomainException
     */
    public function handleRequest(): StatusUpdateInterface
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
        return $this->updateOhDear($statusUpdate, $slackRequest);
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

    /**
     * Prepare the Oh Dear! Status Update entity
     *
     * @param MessageInterface $slackMessage
     * @return StatusUpdateInterface
     */
    private function prepareStatusUpdate(MessageInterface $slackMessage): StatusUpdateInterface
    {
        $text = $this->scrubInput($slackMessage->getText());
        return $this->splitText($text);
    }

    /**
     * Split the provided text into meaningful components using
     * the single or double quote as separator for the text blocks.
     *
     * @param string $text
     * @return StatusUpdateInterface
     */
    private function splitText(string $text): StatusUpdateInterface
    {
        $matches = [];
        $pattern = '/^(\w+)\s[^\w](.*)[^\w]\s[^\w](.*)[^\w]$/';
        preg_match($pattern, $text, $matches);

        $items = count($matches);
        if (4 !== $items) {
            throw new InvalidArgumentException('Text was not formatted correctly');
        }
        list ($input, $severity, $title, $text) = $matches;
        unset($input);

        $allowedLevels = [
            StatusUpdate::OHDEAR_LEVEL_INFO,
            StatusUpdate::OHDEAR_LEVEL_WARN,
            StatusUpdate::OHDEAR_LEVEL_HIGH,
            StatusUpdate::OHDEAR_LEVEL_RESOLVED,
            StatusUpdate::OHDEAR_LEVEL_SCHEDULED,
        ];
        if (! in_array($severity, $allowedLevels)) {
            $severity = $allowedLevels[self::OHDEAR_DEFAULT_SEVERITY];
        }

        $data = [
            'severity' => $severity,
            'title'    => $title,
            'text'     => $text,
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

    /**
     * Update the Oh Dear! status page via API
     *
     * @param StatusUpdateInterface $statusUpdate
     * @param MessageInterface $slackRequest
     * @return StatusUpdateInterface
     * @throws RuntimeException
     */
    private function updateOhDear(
        StatusUpdateInterface $statusUpdate,
        MessageInterface $slackRequest
    ): StatusUpdateInterface {
        $data = $this->hydrator->extract($statusUpdate);
        $token = $this->getData[self::OHDEAR_API_TOKEN];
        $data['status_page_id'] = $this->getData[self::OHDEAR_STATUS_PAGE_ID];
        $payload = json_encode($data);
        if (self::$testMode) {
            return $statusUpdate;
        }
        $response = $this->makeRequest($token, $payload);
        $responseData = json_decode($response, true);
        $responseData['status_page_id'] = $this->getData[self::OHDEAR_STATUS_PAGE_ID];
        $responseData['reply_url'] = $slackRequest->getResponseUrl();
        $update = $this->hydrator->hydrate($statusUpdate, $responseData);
        if (! $update instanceof StatusUpdateInterface) {
            throw new DomainException('Issue creating a proper response');
        }
        return $update;
    }

    /**
     * Make an HTTP Request to the Oh Dear! API service and returns
     * the status code of the request.
     *
     * @param string $token
     * @param string $payload
     * @return string
     * @throws RuntimeException
     */
    private function makeRequest(string $token, string $payload): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::OHDEAR_STATUS_PAGE_URI,
            CURLOPT_FAILONERROR    => false,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_POST           => true,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => self::OHDEAR_USER_AGENT,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => $payload,
        ]);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $errorMessage = curl_error($ch);
        curl_close($ch);
        if (201 !== $statusCode) {
            throw new RuntimeException('Problem updating status: ' . $errorMessage);
        }
        if (false === $response) {
            throw new RuntimeException('Failure to update status page: ' . $errorMessage);
        }
        return $response;
    }

    /**
     * Make an HTTP Request to the Slack API service to post
     * response of our status update.
     *
     * @param string $responseUri
     * @param string $payload
     * @return string
     * @throws RuntimeException
     */
    public function updateSlack(string $responseUri, string $payload): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $responseUri,
            CURLOPT_FAILONERROR    => false,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_POST           => true,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => self::OHDEAR_USER_AGENT,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => $payload,
        ]);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $errorMessage = curl_error($ch);
        curl_close($ch);
        $range = range(200, 299);
        if (! in_array($statusCode, $range)) {
            throw new RuntimeException('Problem updating status: ' . $errorMessage);
        }
        if (false === $response) {
            throw new RuntimeException('Failure to update Slack: ' . $errorMessage);
        }
        return $response;
    }
}
