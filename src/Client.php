<?php

declare(strict_types=1);

namespace VaclavVanik\Soap\Interpreter;

use SoapClient;
use SoapFault;
use SoapHeader;

/** @internal */
class Client extends SoapClient
{
    /** @var string */
    private $requestUri;

    /** @var string */
    private $requestBody;

    /** @var string */
    private $soapAction;

    /** @var int */
    private $soapVersion;

    /** @var string|null */
    private $soapResponse;

    /** @inheritdoc */
    public function __doRequest($request, $location, $action, $version, $oneWay = 0): ?string
    {
        if ($this->soapResponse !== null) {
            return $this->soapResponse;
        }

        $this->requestUri = (string) $location;
        $this->soapAction = (string) $action;
        $this->soapVersion = (int) $version;
        $this->requestBody = (string) $request;

        return '';
    }

    /**
     * @param array<mixed, mixed>                                            $parameters
     * @param array{location? : string, uri? : string, soapaction? : string} $options
     * @param array<int, SoapHeader>                                         $soapHeaders
     */
    public function request(
        string $operation,
        array $parameters = [],
        array $options = [],
        array $soapHeaders = []
    ): Request {
        if ($operation === '') {
            throw new Exception\ValueError('Operation cannot be empty');
        }

        $this->__soapCall($operation, $parameters, $options, $soapHeaders ?: null);

        return new Request($this->requestUri, $this->requestBody, $this->soapAction, $this->soapVersion);
    }

    /**
     * @param array<int, SoapHeader> $soapHeaders
     *
     * @return mixed
     *
     * @throws SoapFault
     * @throws Exception\ValueError
     */
    public function response(string $operation, string $response, array &$soapHeaders = [])
    {
        if ($operation === '') {
            throw new Exception\ValueError('Operation cannot be empty');
        }

        if ($response === '') {
            throw new Exception\ValueError('Response cannot be empty');
        }

        $this->soapResponse = $response;

        try {
            $soapHeaders = [];
            $result = $this->__soapCall($operation, [], [], null, $soapHeaders);
        } finally {
            $this->soapResponse = null;
        }

        return $result;
    }
}
