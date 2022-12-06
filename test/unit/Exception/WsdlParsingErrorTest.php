<?php

declare(strict_types=1);

namespace VaclavVanikTest\Soap\Interpreter\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use VaclavVanik\Soap\Interpreter\Exception\WsdlParsingError;

final class WsdlParsingErrorTest extends TestCase
{
    public function testFromThrowable(): void
    {
        $throwable = new Exception('Error message');

        $wsdl = WsdlParsingError::fromThrowable($throwable);

        $this->assertSame($throwable->getMessage(), $wsdl->getMessage());
        $this->assertSame($throwable->getCode(), $wsdl->getCode());
        $this->assertSame($throwable, $wsdl->getPrevious());
    }

    public function testFromThrowableOmitSoapError(): void
    {
        $message = 'Parsing WSDL: Couldn\'t load from \'invalid-wsdl\' : failed to load external entity "invalid-wsdl"';

        $throwable = new Exception('SOAP-ERROR: ' . $message);

        $wsdl = WsdlParsingError::fromThrowable($throwable);

        $this->assertSame($message, $wsdl->getMessage());
        $this->assertSame($throwable->getCode(), $wsdl->getCode());
        $this->assertSame($throwable, $wsdl->getPrevious());
    }
}
