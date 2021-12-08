# Soap Interpreter

This package provides interpreting of SOAP 1.1 and SOAP 1.2 messages.
It can be used in WSDL or non-WSDL mode.
The implementation is built on the top of PHP's [SoapClient](http://php.net/manual/en/class.soapclient.php).

## Install

You can install this package via composer. 

``` bash
composer require vaclavvanik/soap-interpreter
```

## Usage

An [Interpreter](src/Interpreter.php) is responsible for generating SOAP [request](src/Request.php) messages and translating SOAP [response](src/Response.php) messages.

### Create interpreter in WSDL mode:

```php
<?php

declare(strict_types=1);

use VaclavVanik\Soap\Interpreter\Interpreter;

$interpreter = Interpreter::fromWsdl('http://www.dneonline.com/calculator.asmx?wsdl');
```

### Create interpreter in non-WSDL mode:

```php
<?php

declare(strict_types=1);

use VaclavVanik\Soap\Interpreter\Interpreter;

$interpreter = Interpreter::fromNonWsdl('http://tempuri.org/', 'http://www.dneonline.com/calculator.asmx');
```

### Generate SOAP request message in WSDL mode

```php
<?php

declare(strict_types=1);

use VaclavVanik\Soap\Interpreter\Interpreter;

$request = (Interpreter::fromWsdl('http://www.dneonline.com/calculator.asmx?wsdl'))->request('Add', ['Add' => ['intA' => 1, 'intB' => 3]]);
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

use VaclavVanik\Soap\Interpreter\Interpreter;

$response = <<<XML
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
   <SOAP-ENV:Body>
        <ns1:AddResponse xmlns:ns1="http://tempuri.org/">
          <ns1:AddResult>3</ns1:AddResult>
        </ns1:AddResponse>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML;

$response = (Interpreter::fromWsdl('http://www.dneonline.com/calculator.asmx?wsdl'))->response('Add', $response);
print_r($response->getResult());
```

Output:

```php
stdClass Object
(
    [AddResult] => 3
)
```

## Exceptions

- [Exception\SoapFault](src/Exception/SoapFault.php) if soap fault thrown.
- [Exception\ValueError](src/Exception/ValueError.php) if required argument is incorrect.

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
