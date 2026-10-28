<?php

declare(strict_types=1);

namespace Webmasterskaya\Unisender;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Webmasterskaya\Unisender\Exception\ApiException;
use Webmasterskaya\Unisender\Exception\InvalidArgumentException;
use Webmasterskaya\Unisender\Exception\NetworkException;

/**
 * PSR-18 client for the UniSender API.
 *
 * API methods may be called through send() or as dynamic methods:
 * `$client->send('getLists')` and `$client->getLists()` are equivalent.
 *
 * Lists and contacts:
 * @method array<string, mixed> createList(array<string, mixed> $parameters = []) Create a contact list.
 * @method array<string, mixed> deleteList(array<string, mixed> $parameters = []) Delete a contact list.
 * @method array<string, mixed> exclude(array<string, mixed> $parameters = []) Exclude a contact from lists.
 * @method array<string, mixed> exportContacts(array<string, mixed> $parameters = []) Export contacts.
 * @method array<string, mixed> getContactCount(array<string, mixed> $parameters = []) Get the number of contacts in a list.
 * @method array<string, mixed> getLists(array<string, mixed> $parameters = []) Get contact lists.
 * @method array<string, mixed> getTotalContactsCount(array<string, mixed> $parameters = []) Get the total contact count.
 * @method array<string, mixed> importContacts(array<string, mixed> $parameters = []) Import contacts.
 * @method array<string, mixed> subscribe(array<string, mixed> $parameters = []) Subscribe a contact.
 * @method array<string, mixed> unsubscribe(array<string, mixed> $parameters = []) Unsubscribe a contact.
 * @method array<string, mixed> updateList(array<string, mixed> $parameters = []) Update a contact list.
 * @method array<string, mixed> getContact(array<string, mixed> $parameters = []) Get contact information.
 * @method array<string, mixed> isContactInLists(array<string, mixed> $parameters = []) Check whether a contact belongs to lists.
 *
 * Messages and campaigns:
 * @method array<string, mixed> cancelCampaign(array<string, mixed> $parameters = []) Cancel a scheduled campaign.
 * @method array<string, mixed> checkEmail(array<string, mixed> $parameters = []) Check email delivery status.
 * @method array<string, mixed> checkSms(array<string, mixed> $parameters = []) Check SMS delivery status.
 * @method array<string, mixed> createCampaign(array<string, mixed> $parameters = []) Create and schedule a campaign.
 * @method array<string, mixed> createEmailMessage(array<string, mixed> $parameters = []) Create an email message.
 * @method array<string, mixed> createSmsMessage(array<string, mixed> $parameters = []) Create an SMS message.
 * @method array<string, mixed> deleteMessage(array<string, mixed> $parameters = []) Delete a message.
 * @method array<string, mixed> getActualMessageVersion(array<string, mixed> $parameters = []) Get the active message version.
 * @method array<string, mixed> getWebVersion(array<string, mixed> $parameters = []) Get a campaign web-version URL.
 * @method array<string, mixed> sendEmail(array<string, mixed> $parameters = []) Send an individual email.
 * @method array<string, mixed> sendSms(array<string, mixed> $parameters = []) Send an individual SMS.
 * @method array<string, mixed> sendTestEmail(array<string, mixed> $parameters = []) Send a test email.
 * @method array<string, mixed> updateEmailMessage(array<string, mixed> $parameters = []) Update an email message.
 * @method array<string, mixed> updateOptInEmail(array<string, mixed> $parameters = []) Update the subscription confirmation email.
 * @method array<string, mixed> getSenderDomainList(array<string, mixed> $parameters = []) Get sender domains.
 *
 * Statistics:
 * @method array<string, mixed> getCampaignCommonStats(array<string, mixed> $parameters = []) Get aggregate campaign statistics.
 * @method array<string, mixed> getCampaignDeliveryStats(array<string, mixed> $parameters = []) Get campaign delivery statistics.
 * @method array<string, mixed> getCampaignStatus(array<string, mixed> $parameters = []) Get campaign status.
 * @method array<string, mixed> getMessages(array<string, mixed> $parameters = []) Get messages with their content.
 * @method array<string, mixed> getVisitedLinks(array<string, mixed> $parameters = []) Get link visit statistics.
 * @method array<string, mixed> listMessages(array<string, mixed> $parameters = []) List messages without their content.
 * @method array<string, mixed> getCampaigns(array<string, mixed> $parameters = []) Get campaigns.
 * @method array<string, mixed> getMessage(array<string, mixed> $parameters = []) Get a message.
 *
 * Templates:
 * @method array<string, mixed> createEmailTemplate(array<string, mixed> $parameters = []) Create an email template.
 * @method array<string, mixed> deleteTemplate(array<string, mixed> $parameters = []) Delete a template.
 * @method array<string, mixed> getTemplate(array<string, mixed> $parameters = []) Get a template.
 * @method array<string, mixed> getTemplates(array<string, mixed> $parameters = []) Get templates with their content.
 * @method array<string, mixed> listTemplates(array<string, mixed> $parameters = []) List templates without their content.
 * @method array<string, mixed> updateEmailTemplate(array<string, mixed> $parameters = []) Update an email template.
 *
 * Custom fields and tags:
 * @method array<string, mixed> createField(array<string, mixed> $parameters = []) Create a custom field.
 * @method array<string, mixed> deleteField(array<string, mixed> $parameters = []) Delete a custom field.
 * @method array<string, mixed> deleteTag(array<string, mixed> $parameters = []) Delete a tag.
 * @method array<string, mixed> getFields(array<string, mixed> $parameters = []) Get custom fields.
 * @method array<string, mixed> getTags(array<string, mixed> $parameters = []) Get tags.
 * @method array<string, mixed> updateField(array<string, mixed> $parameters = []) Update a custom field.
 * @method array<string, mixed> getContactFieldValues(array<string, mixed> $parameters = []) Get contact field values.
 *
 * Subscriber notes (method names follow the UniSender API spelling):
 * @method array<string, mixed> createSubsciberNote(array<string, mixed> $parameters = []) Create a subscriber note.
 * @method array<string, mixed> updateSubcriberNote(array<string, mixed> $parameters = []) Update a subscriber note.
 * @method array<string, mixed> deleteSubscriberNote(array<string, mixed> $parameters = []) Delete a subscriber note.
 * @method array<string, mixed> getSubscriberNote(array<string, mixed> $parameters = []) Get a subscriber note.
 * @method array<string, mixed> getSubscriberNotes(array<string, mixed> $parameters = []) Get subscriber notes.
 *
 * @see https://www.unisender.com/ru/support/category/api/
 */
final class Client
{
    public const API_BASE_URI = 'https://api.unisender.com/';

    /** @var list<string> */
    private const LANGUAGES = ['ru', 'en'];

    private readonly ClientInterface $httpClient;
    private readonly RequestFactoryInterface $requestFactory;
    private readonly StreamFactoryInterface $streamFactory;
    private readonly LoggerInterface $logger;
    private readonly string $language;
    private readonly string $baseUri;

    /**
     * PSR dependencies are discovered automatically unless explicitly injected.
     *
     * @param array{lang?: string} $options
     */
    public function __construct(
        #[\SensitiveParameter]
        private readonly string $api_key,
        array $options = [],
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?LoggerInterface $logger = null,
        string $baseUri = self::API_BASE_URI,
    ) {
        if (trim($this->api_key) === '') {
            throw new InvalidArgumentException('API key must not be empty.');
        }

        $language = strtolower(trim($options['lang'] ?? 'ru'));

        if (!in_array($language, self::LANGUAGES, true)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported language "%s". Expected one of: %s.',
                $language,
                implode(', ', self::LANGUAGES),
            ));
        }

        $this->language = $language;
        $this->baseUri = rtrim($baseUri, '/') . '/';
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Calls an API method and returns the complete response envelope.
     *
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     *
     * @throws ApiException
     * @throws NetworkException
     */
    public function send(string $method, array $parameters = []): array
    {
        $this->assertMethodName($method);

        $uri = sprintf('%s%s/api/%s', $this->baseUri, $this->language, $method);
        $payload = ['api_key' => $this->api_key, 'format' => 'json'] + $parameters;
        $body = http_build_query($payload, '', '&', PHP_QUERY_RFC1738);

        $request = $this->requestFactory->createRequest('POST', $uri)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8')
            ->withBody($this->streamFactory->createStream($body));

        $this->logger->debug('UniSender API request', [
            'method' => $method,
            'uri' => $uri,
            'parameters' => array_diff_key($parameters, ['api_key' => true]),
        ]);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            $this->logger->error('UniSender API transport error', [
                'method' => $method,
                'error' => $exception->getMessage(),
            ]);

            throw new NetworkException($exception->getMessage(), 0, $exception);
        }

        return $this->decodeResponse($response, $method);
    }

    /**
     * Alias for send(), useful for explicit transport-style calls.
     *
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    public function request(string $method, array $parameters = []): array
    {
        return $this->send($method, $parameters);
    }

    /**
     * @param list<mixed> $arguments
     * @return array<string, mixed>
     */
    public function __call(string $method, array $arguments): array
    {
        if (count($arguments) > 1 || (isset($arguments[0]) && !is_array($arguments[0]))) {
            throw new InvalidArgumentException(sprintf(
                '%s() expects a single array of parameters.',
                $method,
            ));
        }

        /** @var array<string, mixed> $parameters */
        $parameters = $arguments[0] ?? [];

        return $this->send($method, $parameters);
    }

    /** @return array<string, mixed> */
    private function decodeResponse(ResponseInterface $response, string $method): array
    {
        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($body, true, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ApiException(
                sprintf('UniSender returned invalid JSON for %s: %s', $method, $exception->getMessage()),
                statusCode: $statusCode,
                responseBody: $body,
                previous: $exception,
            );
        }

        if (!is_array($decoded)) {
            throw new ApiException(
                sprintf('UniSender returned an unexpected response for %s.', $method),
                statusCode: $statusCode,
                responseBody: $body,
            );
        }

        $apiError = isset($decoded['error']) && is_scalar($decoded['error'])
            ? (string) $decoded['error']
            : null;
        $apiCode = isset($decoded['code']) && is_scalar($decoded['code'])
            ? (string) $decoded['code']
            : null;

        if ($statusCode >= 400 || $apiError !== null) {
            $message = $apiError ?? sprintf('UniSender returned HTTP %d.', $statusCode);

            $this->logger->error('UniSender API error', [
                'method' => $method,
                'status' => $statusCode,
                'code' => $apiCode,
                'error' => $message,
            ]);

            throw new ApiException($message, $apiCode, $statusCode, $body);
        }

        if (!array_key_exists('result', $decoded)) {
            throw new ApiException(
                sprintf('UniSender response for %s does not contain a result.', $method),
                statusCode: $statusCode,
                responseBody: $body,
            );
        }

        $this->logger->info('UniSender API response', [
            'method' => $method,
            'status' => $statusCode,
        ]);

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private function assertMethodName(string $method): void
    {
        if (preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $method) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid API method name "%s".', $method));
        }
    }
}
