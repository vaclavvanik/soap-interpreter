<?php

declare(strict_types=1);

namespace VaclavVanik\Soap\Interpreter;

use SoapFault;

use function in_array;

use const SOAP_1_1;
use const SOAP_1_2;
use const SOAP_SINGLE_ELEMENT_ARRAYS;
use const WSDL_CACHE_NONE;

final class PhpInterpreter implements Interpreter
{
    /** @var Client */
    private $soapClient;

    /**
     * String URI or NULL if working in non-WSDL mode
     *
     * @var string|null
     */
    private $wsdl;

    /**
     * Bitmask of SOAP_SINGLE_ELEMENT_ARRAYS, SOAP_USE_XSI_ARRAY_TYPE, SOAP_WAIT_ONE_WAY_CALLS
     *
     * @var int
     */
    private $features;

    /**
     * URL of the SOAP server to send the request
     *
     * @var string|null
     */
    private $location;

    /**
     * Target namespace of the SOAP service
     *
     * @var string|null
     */
    private $uri;

    /** @var int|null */
    private $soapVersion;

    public const FEATURES_DEFAULT = SOAP_SINGLE_ELEMENT_ARRAYS;

    private function __construct(
        ?string $wsdl,
        ?string $uri = null,
        ?string $location = null,
        ?int $soapVersion = null,
        ?int $features = null
    ) {
        if ($soapVersion !== null && in_array($soapVersion, [SOAP_1_1, SOAP_1_2], true) === false) {
            throw new Exception\ValueError('Unsupported SOAP version');
        }

        if ($features === null) {
            $features = self::FEATURES_DEFAULT;
        }

        $this->wsdl = $wsdl;
        $this->uri = $uri;
        $this->location = $location;
        $this->soapVersion = $soapVersion;
        $this->features = $features;
    }

    /**
     * Creates interpreter in WSDL mode
     *
     * @param string      $wsdl        WSDL URI or filename
     * @param int|null    $soapVersion Should be one of either SOAP_1_1 or SOAP_1_2. If null, 1.1 is used
     * @param int|null    $features    Bitmask of SOAP_SINGLE_ELEMENT_ARRAYS, SOAP_USE_XSI_ARRAY_TYPE,
     * SOAP_WAIT_ONE_WAY_CALLS. Defaults is SOAP_SINGLE_ELEMENT_ARRAYS
     * @param string|null $location    URL of the SOAP server to send the request
     *
     * @throws Exception\ValueError
     */
    public static function fromWsdl(
        string $wsdl,
        ?int $soapVersion = null,
        ?int $features = null,
        ?string $location = null
    ): self {
        if ($wsdl === '') {
            throw new Exception\ValueError('Wsdl cannot be empty');
        }

        return new self($wsdl, null, $location, $soapVersion, $features);
    }

    /**
     * Creates interpreter in non-WSDL mode
     *
     * @param string   $uri         Target namespace of the SOAP service
     * @param string   $location    URL of the SOAP server to send the request
     * @param int|null $soapVersion Should be one of either SOAP_1_1 or SOAP_1_2. If null, 1.1 is used
     * @param int|null $features    Bitmask of SOAP_SINGLE_ELEMENT_ARRAYS, SOAP_USE_XSI_ARRAY_TYPE,
     * SOAP_WAIT_ONE_WAY_CALLS. Defaults is SOAP_SINGLE_ELEMENT_ARRAYS
     *
     * @throws Exception\ValueError
     */
    public static function fromNonWsdl(
        string $uri,
        string $location,
        ?int $soapVersion = null,
        ?int $features = null
    ): self {
        if ($uri === '') {
            throw new Exception\ValueError('Uri cannot be empty');
        }

        if ($location === '') {
            throw new Exception\ValueError('Location cannot be empty');
        }

        return new self(null, $uri, $location, $soapVersion, $features);
    }

    /** @inheritdoc */
    public function request(string $operation, array $parameters = [], array $soapHeaders = []): Request
    {
        $options = [];

        return $this->client()->request($operation, $parameters, $options, $soapHeaders);
    }

    public function response(string $operation, string $response): Response
    {
        try {
            $soapHeaders = [];
            $result = $this->client()->response($operation, $response, $soapHeaders);

            return new Response($result, $soapHeaders);
        } catch (SoapFault $e) {
            throw Exception\SoapFault::fromSoapFault($e);
        }
    }

    /** @throws Exception\WsdlParsingError */
    private function client(): Client
    {
        if ($this->soapClient) {
            return $this->soapClient;
        }

        try {
            $options = [
                'exceptions' => true,
                'features' => $this->features,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ];

            if ($this->location !== null) {
                $options['location'] = $this->location;
            }

            if ($this->uri !== null) {
                $options['uri'] = $this->uri;
            }

            if ($this->soapVersion !== null) {
                $options['soap_version'] = $this->soapVersion;
            }

            $this->soapClient = new Client($this->wsdl, $options);
        } catch (SoapFault $e) {
            throw Exception\WsdlParsingError::fromThrowable($e);
        }

        return $this->soapClient;
    }
}
