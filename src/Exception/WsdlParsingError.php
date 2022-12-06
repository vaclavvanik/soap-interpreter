<?php

declare(strict_types=1);

namespace VaclavVanik\Soap\Interpreter\Exception;

use RuntimeException;
use Throwable;

use function str_replace;
use function strpos;

final class WsdlParsingError extends RuntimeException implements Exception
{
    public static function fromThrowable(Throwable $e): self
    {
        if (strpos($e->getMessage(), 'SOAP-ERROR: ') === 0) {
            return new self(str_replace('SOAP-ERROR: ', '', $e->getMessage()), $e->getCode(), $e);
        }

        return new self($e->getMessage(), $e->getCode(), $e);
    }
}
