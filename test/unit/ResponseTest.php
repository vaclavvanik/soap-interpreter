<?php

declare(strict_types=1);

namespace VaclavVanikTest\Soap\Interpreter;

use PHPUnit\Framework\TestCase;
use VaclavVanik\Soap\Interpreter\Response;

final class ResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $result = 'foo';
        $headers = [];

        $response = new Response($result, $headers);

        $this->assertSame($result, $response->getResult());
        $this->assertSame($headers, $response->getHeaders());
    }
}
