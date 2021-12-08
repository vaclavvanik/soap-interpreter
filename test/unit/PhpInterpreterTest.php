<?php

declare(strict_types=1);

namespace VaclavVanikTest\Soap\Interpreter;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use VaclavVanik\Soap\Interpreter\Exception\SoapFault;
use VaclavVanik\Soap\Interpreter\Exception\ValueError;
use VaclavVanik\Soap\Interpreter\PhpInterpreter;

use function file_get_contents;

use const SOAP_1_1;
use const SOAP_1_2;

final class PhpInterpreterTest extends TestCase
{
    public function testFromWsdlThrowValueErrorException(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Wsdl cannot be empty');

        PhpInterpreter::fromWsdl('');
    }

    /** @return iterable<string, array{string, string, int|null, string}> */
    public function provideInvalidUriLocation(): iterable
    {
        yield 'empty_uri' => [
            '',
            'location',
            null,
            'Uri cannot be empty',
        ];

        yield 'empty_location' => [
            'uri',
            '',
            null,
            'Location cannot be empty',
        ];

        yield 'invalid_soap' => [
            'uri',
            '',
            null,
            'Location cannot be empty',
        ];

        yield 'unsupported_soap_version' => [
            'uri',
            'location',
            0,
            'Unsupported SOAP version',
        ];
    }

    /** @dataProvider provideInvalidUriLocation */
    public function testFromUriLocationThrowValueErrorException(
        string $uri,
        string $location,
        ?int $soapVersion,
        string $exceptionMessage
    ): void {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage($exceptionMessage);

        PhpInterpreter::fromNonWsdl($uri, $location, $soapVersion);
    }

    public function testInvalidWsdlThrowSoapFaultException(): void
    {
        $this->expectException(SoapFault::class);
        $this->expectExceptionMessageMatches('/^SOAP-ERROR: Parsing WSDL/i');

        PhpInterpreter::fromWsdl('invalid-wsdl')->request('operation-name');
    }

    /** @return iterable<string, array{PhpInterpreter, string, array<string, string>, string, string, string, int}> */
    public function provideRequest(): iterable
    {
        $operation = 'sayHello';
        $parameters = ['name' => 'Venca'];

        $location = 'https://example.com/say-hello/';
        $reqUriSoap11 = 'https://example.com/soap11/say-hello';
        $reqUriSoap12 = 'https://example.com/soap12/say-hello';
        $reqSoapAction =  $location . '#' . $operation;

        yield 'wsdl11' => [
            PhpInterpreter::fromWsdl(__DIR__ . '/../fixtures/soap11.wsdl'),
            $operation,
            $parameters,
            $reqUriSoap11,
            __DIR__ . '/../fixtures/wsdl-request11.xml',
            $reqSoapAction,
            SOAP_1_1,
        ];

        yield 'wsdl12' => [
            PhpInterpreter::fromWsdl(__DIR__ . '/../fixtures/soap12.wsdl', SOAP_1_2),
            $operation,
            $parameters,
            $reqUriSoap12,
            __DIR__ . '/../fixtures/wsdl-request12.xml',
            $reqSoapAction,
            SOAP_1_2,
        ];

        yield 'uri_location_soap_11' => [
            PhpInterpreter::fromNonWsdl($location, $reqUriSoap11),
            $operation,
            $parameters,
            $reqUriSoap11,
            __DIR__ . '/../fixtures/uri-request11.xml',
            $reqSoapAction,
            SOAP_1_1,
        ];

        yield 'uri_location_soap_12' => [
            PhpInterpreter::fromNonWsdl($location, $reqUriSoap12, SOAP_1_2),
            $operation,
            $parameters,
            $reqUriSoap12,
            __DIR__ . '/../fixtures/uri-request12.xml',
            $reqSoapAction,
            SOAP_1_2,
        ];
    }

    public function testRequestThrowValueErrorException(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Operation cannot be empty');

        PhpInterpreter::fromWsdl(__DIR__ . '/../fixtures/soap11.wsdl')->request('');
    }

    /**
     * @param array<string, string> $parameters
     *
     * @dataProvider provideRequest
     */
    public function testRequest(
        PhpInterpreter $interpreter,
        string $operation,
        array $parameters,
        string $reqUri,
        string $reqBodyFile,
        string $reqSoapAction,
        int $reqSoapVersion
    ): void {
        $request = $interpreter->request($operation, $parameters);

        $this->assertSame($reqUri, $request->getUri());
        $this->assertSame($this->loadXmlFile($reqBodyFile), $this->loadXmlString($request->getBody()));
        $this->assertSame($reqSoapAction, $request->getSoapAction());
        $this->assertSame($reqSoapVersion, $request->getSoapVersion());
    }

    /** @return iterable<string, array{PhpInterpreter, string, string, string}> */
    public function provideResponse(): iterable
    {
        $operation = 'sayHello';
        $result = 'Hello Venca';

        yield 'wsdl11' => [
            PhpInterpreter::fromWsdl(__DIR__ . '/../fixtures/soap11.wsdl'),
            $operation,
            file_get_contents(__DIR__ . '/../fixtures/wsdl-response11.xml'),
            $result,
        ];

        yield 'wsdl12' => [
            PhpInterpreter::fromWsdl(__DIR__ . '/../fixtures/soap12.wsdl', SOAP_1_2),
            $operation,
            file_get_contents(__DIR__ . '/../fixtures/wsdl-response12.xml'),
            $result,
        ];

        yield 'uri_location_soap_11' => [
            PhpInterpreter::fromNonWsdl('https://example.com/say-hello/', '/soap11/say-hello'),
            $operation,
            file_get_contents(__DIR__ . '/../fixtures/uri-response11.xml'),
            $result,
        ];

        yield 'uri_location_soap_12' => [
            PhpInterpreter::fromNonWsdl('https://example.com/say-hello/', '/soap12/say-hello', SOAP_1_2),
            $operation,
            file_get_contents(__DIR__ . '/../fixtures/uri-response12.xml'),
            $result,
        ];
    }

    /** @dataProvider provideResponse */
    public function testResponse(
        PhpInterpreter $interpreter,
        string $operation,
        string $response,
        string $result
    ): void {
        $response = $interpreter->response($operation, $response);

        $this->assertSame($result, $response->getResult());
        $this->assertSame([], $response->getHeaders());
    }

    /** @return iterable<string, array{string, string, string}> */
    public function provideInvalidResponse(): iterable
    {
        yield 'empty_operation' => [
            '',
            file_get_contents(__DIR__ . '/../fixtures/wsdl-response11.xml'),
            'Operation cannot be empty',
        ];

        yield 'empty_response' => [
            'sayHello',
            '',
            'Response cannot be empty',
        ];
    }

    /** @dataProvider provideInvalidResponse */
    public function testResponseThrowValueErrorException(
        string $operation,
        string $response,
        string $exceptionMessage
    ): void {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage($exceptionMessage);

        PhpInterpreter::fromWsdl(__DIR__ . '/../fixtures/soap11.wsdl')->response($operation, $response);
    }

    public function testResponseThrowsSoapFault(): void
    {
        $response = <<<XML
<SOAP-ENV:Envelope
  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
   <SOAP-ENV:Body>
       <SOAP-ENV:Fault>
           <faultcode>SOAP-ENV:Server</faultcode>
           <faultstring>Server Error</faultstring>
           <detail>
               <e:myfaultdetails xmlns:e="Some-URI">
                 <message>
                   My application didn't work
                 </message>
                 <errorcode>
                   1001
                 </errorcode>
               </e:myfaultdetails>
           </detail>
       </SOAP-ENV:Fault>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML;

        $interpreter = PhpInterpreter::fromNonWsdl('https://example.com/say-hello/', '/soap11/say-hello');
        $interpreter->request('sayHello');

        $this->expectException(SoapFault::class);
        $this->expectExceptionMessage('Server Error');

        $interpreter->response('sayHello', $response);
    }

    private function loadXmlFile(string $filename): string
    {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->load($filename);
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        return $doc->saveXML();
    }

    private function loadXmlString(string $xml): string
    {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($xml);
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        return $doc->saveXML();
    }
}
