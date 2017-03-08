# Address Validator
[![Build Status](https://travis-ci.org/dsposito/address-validator.svg?branch=master)](https://travis-ci.org/dsposito/address-validator)

## Overview

An address validator adapter that supports a variety of third-party validators.

## Installation
Run the following [composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) command to add the package to your project:

```
composer require dsposito/address-validator
```

Alternatively, add `"dsposito/address-validator": "^0.1"` to your composer.json file.

##Usage
```php
$provider = Provider::instance(
    'usps',
    [
        'endpoint' => 'http://production.shippingapis.com/ShippingAPI.dll',
        'user_id' => 'SK297O2B7BF221',
    ]
);

$validated = $provider->validate(new Address([
    'name' => 'Elon Musk',
    'street1' => '3500 Deer Creek Road',
    'city' => 'Palo Alto',
    'state' => 'CA',
    'zip' => '94304',
    'country' => 'US',
]));
```

## Tests
To run the test suite, run the following commands from the root directory:

```
composer install
vendor/bin/phpunit -d usps_user_id=YOUR_USPS_ID -d easypost_api_key=YOUR_EASYPOST_KEY
```

> **Note:** Valid API keys are required when running the integration tests.
