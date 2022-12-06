# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0 - 2022-12-06

- Interpreter implementations could throw [Exception\WsdlParsingError](src/Exception/WsdlParsingError.php) if wsdl cannot be parsed.
- Interpreter implementations could throw `VaclavVanik\Soap\Wsdl\Exception\Exception` if other wsdl error occurs.

## 0.5.0 - 2022-12-05

Interpreter implementations could throw [Exception\Exception](src/Exception/Exception.php) if any other error occurs.

## 0.4.0 - 2022-12-01

Revert - Exception\SoapFault remove `final`.

## 0.3.0 - 2022-12-01

Exception\SoapFault remove `final`.

## 0.2.0 - 2021-12-08

**BC break**: promote Interpreter as interface. Former Interpreter renamed to PhpInterpreter and marked as final.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2021-12-08

Initial release.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
