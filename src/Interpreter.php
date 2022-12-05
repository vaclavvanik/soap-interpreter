<?php

declare(strict_types=1);

namespace VaclavVanik\Soap\Interpreter;

use SoapHeader;

interface Interpreter
{
    /**
     * Creates soap Request from given method and it's parameters and soap headers
     *
     * @param array<mixed, mixed>    $parameters
     * @param array<int, SoapHeader> $soapHeaders
     *
     * @throws Exception\SoapFault if soap fault thrown.
     * @throws Exception\ValueError if required argument is incorrect.
     * @throws Exception\Exception if any other error occurs.
     */
    public function request(string $operation, array $parameters = [], array $soapHeaders = []): Request;

    /**
     * Convert soap method's response body to Response
     *
     * @throws Exception\SoapFault if soap fault thrown.
     * @throws Exception\ValueError if required argument is incorrect.
     * @throws Exception\Exception if any other error occurs.
     */
    public function response(string $operation, string $response): Response;
}
