# Soap Interpreter

This package provides interpreting of SOAP 1.1 and SOAP 1.2 messages.
It can be used in WSDL or non-WSDL mode.
The implementation is built on the top of PHP's [SoapClient](http://php.net/manual/en/class.soapclient.php).

`It is not intended to use this package directly.`

You can use:

- [soap-binding](https://github.com/vaclavvanik/soap-binding) package
for creating PSR-7 compliant SOAP requests and processing PSR-7 responses to SOAP.

- [soap-client](https://github.com/vaclavvanik/soap-client) package for processing requests and responses with PSR HTTP client.

## Install

You can install this package via composer. 

``` bash
composer require vaclavvanik/soap-interpreter
```

## Usage

An [PhpInterpreter](src/PhpInterpreter.php) is responsible for generating SOAP [request](src/Request.php) messages and translating SOAP [response](src/Response.php) messages.

### Create PhpInterpreter in WSDL mode

```php
<?php

declare(strict_types=1);

use VaclavVanik\Soap\Interpreter\PhpInterpreter;

$interpreter = PhpInterpreter::fromWsdl('http://www.dneonline.com/calculator.asmx?wsdl');
```

### Create PhpInterpreter in non-WSDL mode

```php
<?php

declare(strict_types=1);

use VaclavVanik\Soap\Interpreter\PhpInterpreter;

$interpreter = PhpInterpreter::fromNonWsdl('http://tempuri.org/', 'http://www.dneonline.com/calculator.asmx');
```

### Generate SOAP request message in WSDL mode

```php
<?php

declare(strict_types=1);

use VaclavVanik\Soap\Interpreter\PhpInterpreter;

$request = (PhpInterpreter::fromWsdl('http://www.dneonline.com/calculator.asmx?wsdl'))->request('Add', ['Add' => ['intA' => 1, 'intB' => 3]]);
print_r($request->getBody());
```

Output:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://tempuri.org/">
    <SOAP-ENV:Body>
        <ns1:Add>
            <ns1:intA>1</ns1:intA>
            <ns1:intB>3</ns1:intB>
        </ns1:Add>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
```

### Translate SOAP response message

```php
<?php

declare(strict_types=1);

use VaclavVanik\Soap\Interpreter\PhpInterpreter;

$response = <<<XML
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
   <SOAP-ENV:Body>
        <ns1:AddResponse xmlns:ns1="http://tempuri.org/">
          <ns1:AddResult>4</ns1:AddResult>
        </ns1:AddResponse>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML;

$response = (PhpInterpreter::fromWsdl('http://www.dneonline.com/calculator.asmx?wsdl'))->response('Add', $response);
print_r($response->getResult());
```

Output:

```php
stdClass Object
(
    [AddResult] => 4
)
```

## Advanced WSDL usage

Package [soap-interpreter-wsdl](https://github.com/vaclavvanik/soap-interpreter-wsdl) offers advanced WSDL usage.

## Exceptions

- [Exception\SoapFault](src/Exception/SoapFault.php) if soap fault thrown.
- [Exception\ValueError](src/Exception/ValueError.php) if required argument is incorrect.
- [Exception\WsdlParsingError](src/Exception/WsdlParsingError.php) if wsdl cannot be parsed..
- [Exception\Exception](src/Exception/Exception.php) if any other error occurs.

or `VaclavVanik\Soap\Wsdl\Exception\Exception` if other wsdl error occurs.

## Run check - coding standards and php-unit

Install dependencies:

```bash
make install
```

Run check:

```bash
make check
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
