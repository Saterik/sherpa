<?php

declare(strict_types=1);

namespace App\Exception;

class HttpException extends \RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 500,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function notFound(string $message = 'Resource not found'): self
    {
        return new self($message, 404);
    }

    public static function badRequest(string $message = 'Bad request'): self
    {
        return new self($message, 400);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self($message, 403);
    }

    public static function conflict(string $message = 'Conflict'): self
    {
        return new self($message, 409);
    }

    public static function unprocessableEntity(string $message = 'Unprocessable entity'): self
    {
        return new self($message, 422);
    }

    public static function tooManyRequests(string $message = 'Too many requests'): self
    {
        return new self($message, 429);
    }

    public static function internalServerError(string $message = 'Internal server error'): self
    {
        return new self($message, 500);
    }
}