<?php

declare(strict_types=1);

namespace Webmasterskaya\Unisender\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\AbstractLogger;
use Webmasterskaya\Unisender\Client;
use Webmasterskaya\Unisender\Exception\ApiException;
use Webmasterskaya\Unisender\Exception\InvalidArgumentException;
use Webmasterskaya\Unisender\Exception\NetworkException;

final class ClientTest extends TestCase
{
    public function testDiscoversInstalledPsrImplementations(): void
    {
        self::assertInstanceOf(Client::class, new Client('secret'));
    }

    public function testSendsFormEncodedPostWithoutApiKeyInUri(): void
    {
        $captured = null;
        $httpClient = $this->client(static function (RequestInterface $request) use (&$captured): ResponseInterface {
            $captured = $request;

            return new Response(200, [], '{"result":{"id":42}}');
        });
        $factory = new HttpFactory();
        $client = new Client('secret', httpClient: $httpClient, requestFactory: $factory, streamFactory: $factory);

        $result = $client->send('createList', ['title' => 'News']);

        self::assertSame(['result' => ['id' => 42]], $result);
        self::assertInstanceOf(RequestInterface::class, $captured);
        self::assertSame('POST', $captured->getMethod());
        self::assertSame('https://api.unisender.com/ru/api/createList', (string) $captured->getUri());
        self::assertStringNotContainsString('secret', (string) $captured->getUri());
        parse_str((string) $captured->getBody(), $body);
        self::assertSame('secret', $body['api_key']);
        self::assertSame('News', $body['title']);
        self::assertSame('json', $body['format']);
    }

    public function testDynamicMethodUsesItsFirstArgumentAsParameters(): void
    {
        $captured = null;
        $factory = new HttpFactory();
        $client = new Client(
            'secret',
            ['lang' => 'en'],
            $this->client(static function (RequestInterface $request) use (&$captured): ResponseInterface {
                $captured = $request;

                return new Response(200, [], '{"result":{"id":7}}');
            }),
            $factory,
            $factory,
        );

        $result = $client->createField(['name' => 'City', 'type' => 'string']);

        self::assertSame(['result' => ['id' => 7]], $result);
        self::assertInstanceOf(RequestInterface::class, $captured);
        self::assertSame('https://api.unisender.com/en/api/createField', (string) $captured->getUri());
        parse_str((string) $captured->getBody(), $body);
        self::assertSame('City', $body['name']);
    }

    public function testApiErrorContainsDiagnosticContext(): void
    {
        $factory = new HttpFactory();
        $client = new Client(
            'secret',
            httpClient: $this->client(static fn(): ResponseInterface => new Response(
                400,
                [],
                '{"code":"invalid_arg","error":"title is required","result":""}',
            )),
            requestFactory: $factory,
            streamFactory: $factory,
        );

        try {
            $client->send('createList');
            self::fail('Expected ApiException.');
        } catch (ApiException $exception) {
            self::assertSame('invalid_arg', $exception->getApiCode());
            self::assertSame(400, $exception->getStatusCode());
            self::assertStringContainsString('title is required', $exception->getMessage());
        }
    }

    public function testRejectsUnsafeMethodName(): void
    {
        $factory = new HttpFactory();
        $client = new Client('secret', httpClient: $this->client(
            static fn(): ResponseInterface => new Response(200, [], '{"result":{}}'),
        ), requestFactory: $factory, streamFactory: $factory);

        $this->expectException(InvalidArgumentException::class);
        $client->send('../createList');
    }

    public function testRejectsEmptyApiKeyBeforeDiscoveringDependencies(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API key must not be empty');

        new Client('   ');
    }

    public function testRejectsUnsupportedLanguageBeforeDiscoveringDependencies(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported language "de"');

        new Client('secret', ['lang' => 'DE']);
    }

    public function testCustomBaseUriIsNormalized(): void
    {
        $captured = null;
        $factory = new HttpFactory();
        $client = new Client(
            'secret',
            httpClient: $this->client(static function (RequestInterface $request) use (&$captured): ResponseInterface {
                $captured = $request;

                return new Response(200, [], '{"result":[]}');
            }),
            requestFactory: $factory,
            streamFactory: $factory,
            baseUri: 'https://gateway.example.test/root',
        );

        $client->getLists();

        self::assertInstanceOf(RequestInterface::class, $captured);
        self::assertSame(
            'https://gateway.example.test/root/ru/api/getLists',
            (string) $captured->getUri(),
        );
    }

    public function testReservedParametersCannotBeOverridden(): void
    {
        $captured = null;
        $factory = new HttpFactory();
        $client = new Client(
            'real-secret',
            httpClient: $this->client(static function (RequestInterface $request) use (&$captured): ResponseInterface {
                $captured = $request;

                return new Response(200, [], '{"result":[]}');
            }),
            requestFactory: $factory,
            streamFactory: $factory,
        );

        $client->send('getLists', ['api_key' => 'fake-secret', 'format' => 'html']);

        self::assertInstanceOf(RequestInterface::class, $captured);
        parse_str((string) $captured->getBody(), $body);
        self::assertSame('real-secret', $body['api_key']);
        self::assertSame('json', $body['format']);
    }

    public function testRequestIsAnAliasForSend(): void
    {
        $factory = new HttpFactory();
        $client = new Client(
            'secret',
            httpClient: $this->client(static fn(): ResponseInterface => new Response(
                200,
                [],
                '{"result":[1],"warnings":[{"warning":"notice"}]}',
            )),
            requestFactory: $factory,
            streamFactory: $factory,
        );

        self::assertSame(
            ['result' => [1], 'warnings' => [['warning' => 'notice']]],
            $client->request('getLists'),
        );
    }

    public function testDynamicMethodRejectsNonArrayArgument(): void
    {
        $factory = new HttpFactory();
        $client = new Client(
            'secret',
            httpClient: $this->client(static fn(): ResponseInterface => new Response(200, [], '{"result":[]}')),
            requestFactory: $factory,
            streamFactory: $factory,
        );

        $this->expectException(InvalidArgumentException::class);
        $client->__call('getLists', ['invalid']);
    }

    public function testDynamicMethodRejectsMultipleArguments(): void
    {
        $factory = new HttpFactory();
        $client = new Client(
            'secret',
            httpClient: $this->client(static fn(): ResponseInterface => new Response(200, [], '{"result":[]}')),
            requestFactory: $factory,
            streamFactory: $factory,
        );

        $this->expectException(InvalidArgumentException::class);
        $client->__call('getLists', [[], []]);
    }

    public function testTransportErrorIsWrappedAsNetworkException(): void
    {
        $factory = new HttpFactory();
        $transportException = new class ('Connection failed') extends \RuntimeException implements ClientExceptionInterface {};
        $httpClient = new class ($transportException) implements ClientInterface {
            public function __construct(private readonly ClientExceptionInterface $exception) {}

            #[\Override]
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw $this->exception;
            }
        };
        $client = new Client(
            'secret',
            httpClient: $httpClient,
            requestFactory: $factory,
            streamFactory: $factory,
        );

        try {
            $client->getLists();
            self::fail('Expected NetworkException.');
        } catch (NetworkException $exception) {
            self::assertSame('Connection failed', $exception->getMessage());
            self::assertSame($transportException, $exception->getPrevious());
        }
    }

    public function testInvalidJsonThrowsApiException(): void
    {
        $exception = $this->captureApiException(new Response(200, [], '<html>broken</html>'));

        self::assertSame(200, $exception->getStatusCode());
        self::assertSame('<html>broken</html>', $exception->getResponseBody());
        self::assertStringContainsString('invalid JSON', $exception->getMessage());
        self::assertInstanceOf(\JsonException::class, $exception->getPrevious());
    }

    public function testScalarJsonThrowsApiException(): void
    {
        $exception = $this->captureApiException(new Response(200, [], 'true'));

        self::assertStringContainsString('unexpected response', $exception->getMessage());
    }

    public function testResponseWithoutResultThrowsApiException(): void
    {
        $exception = $this->captureApiException(new Response(200, [], '{"warnings":[]}'));

        self::assertStringContainsString('does not contain a result', $exception->getMessage());
    }

    public function testHttpErrorWithoutApiErrorUsesStatusMessage(): void
    {
        $exception = $this->captureApiException(new Response(503, [], '{"result":""}'));

        self::assertSame(503, $exception->getStatusCode());
        self::assertSame('UniSender returned HTTP 503.', $exception->getMessage());
        self::assertNull($exception->getApiCode());
    }

    public function testApiErrorOnSuccessfulHttpStatusThrows(): void
    {
        $exception = $this->captureApiException(new Response(
            200,
            [],
            '{"code":"invalid_arg","error":"Bad value","result":""}',
        ));

        self::assertSame('invalid_arg', $exception->getApiCode());
        self::assertSame('Bad value', $exception->getMessage());
    }

    public function testBigIntegersArePreservedAsStrings(): void
    {
        $factory = new HttpFactory();
        $client = new Client(
            'secret',
            httpClient: $this->client(static fn(): ResponseInterface => new Response(
                200,
                [],
                '{"result":{"id":9223372036854775808}}',
            )),
            requestFactory: $factory,
            streamFactory: $factory,
        );

        $response = $client->getMessage();

        $result = $response['result'];
        self::assertIsArray($result);
        self::assertSame('9223372036854775808', $result['id']);
    }

    public function testApiKeyIsNotWrittenToLogs(): void
    {
        $logger = new MemoryLogger();
        $factory = new HttpFactory();
        $client = new Client(
            'real-secret',
            httpClient: $this->client(static fn(): ResponseInterface => new Response(200, [], '{"result":[]}')),
            requestFactory: $factory,
            streamFactory: $factory,
            logger: $logger,
        );

        $client->send('getLists', ['api_key' => 'caller-secret', 'limit' => 10]);

        $encodedRecords = json_encode($logger->records, JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('real-secret', $encodedRecords);
        self::assertStringNotContainsString('caller-secret', $encodedRecords);
        self::assertStringContainsString('limit', $encodedRecords);
    }

    private function client(\Closure $handler): ClientInterface
    {
        return new class ($handler) implements ClientInterface {
            public function __construct(private readonly \Closure $handler) {}

            #[\Override]
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $response = ($this->handler)($request);

                if (!$response instanceof ResponseInterface) {
                    throw new \RuntimeException('Handler must return a PSR-7 response.');
                }

                return $response;
            }
        };
    }

    private function captureApiException(ResponseInterface $response): ApiException
    {
        $factory = new HttpFactory();
        $client = new Client(
            'secret',
            httpClient: $this->client(static fn(): ResponseInterface => $response),
            requestFactory: $factory,
            streamFactory: $factory,
        );

        try {
            $client->getLists();
            self::fail('Expected ApiException.');
        } catch (ApiException $exception) {
            return $exception;
        }
    }
}

final class MemoryLogger extends AbstractLogger
{
    /** @var list<array{level: mixed, message: string, context: array<array-key, mixed>}> */
    public array $records = [];

    /**
     * Parameters intentionally have no native types for compatibility with
     * both PSR-3 1.x and the typed PSR-3 2.x/3.x interface.
     *
     * @param mixed                   $level
     * @param string|\Stringable      $message
     * @param array<array-key, mixed> $context
     */
    #[\Override]
    public function log($level, $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }
}
