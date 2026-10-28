<?php

declare(strict_types=1);

namespace Webmasterskaya\Unisender\Exception;

final class ApiException extends \RuntimeException implements UnisenderExceptionInterface
{
    public function __construct(
        string $message,
        private readonly ?string $apiCode = null,
        private readonly int $statusCode = 0,
        private readonly string $responseBody = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getApiCode(): ?string
    {
        return $this->apiCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}
