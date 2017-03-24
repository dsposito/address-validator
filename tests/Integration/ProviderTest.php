<?php

namespace Dsposito\Validator\Tests\Integration;

use Dsposito\Validator\Address;
use Dsposito\Validator\Exception\InvalidAddress;
use Dsposito\Validator\Provider;

class ProviderTest extends \Dsposito\Validator\Tests\TestCase
{
    public function testUSAddressIsValid()
    {
        $provider = Provider::instance(
            'usps',
            [
                'endpoint' => 'http://production.shippingapis.com/ShippingAPI.dll',
                'user_id' => $this->config['usps_user_id'],
            ]
        );

        $validation = (bool) $provider->validate(new Address([
            'name' => 'Elon Musk',
            'street1' => '3500 Deer Creek Road',
            'city' => 'Palo Alto',
            'state' => 'CA',
            'zip' => '94304',
            'country' => 'US',
        ]));

        $this->assertTrue($validation);
    }

    public function testUSAddressIsInvalid()
    {
        $this->expectException(InvalidAddress::class);

        $provider = Provider::instance(
            'usps',
            [
                'endpoint' => 'http://production.shippingapis.com/ShippingAPI.dll',
                'user_id' => $this->config['usps_user_id'],
            ]
        );

        $validation = (bool) $provider->validate(new Address([
            'name' => 'Elon Musk',
            'street1' => '3555 Deer Creek Lane',
            'city' => 'Palo Alto',
            'state' => 'NV',
            'zip' => '93333',
            'country' => 'US',
        ]));
    }

    public function testCAAddressIsValid()
    {
        $provider = Provider::instance(
            'easypost',
            [
                'api_key' => $this->config['easypost_api_key'],
            ]
        );

        $validation = (bool) $provider->validate(new Address([
            'name' => 'Apple Store, Market Mall',
            'street1' => '3625 Shaganappi Trail NW',
            'city' => 'Calgary',
            'state' => 'Alberta',
            'zip' => 'T3A 0E2',
            'country' => 'CA',
        ]));

        $this->assertTrue($validation);
    }

    public function testCAAddressIsInvalid()
    {
        $this->expectException(InvalidAddress::class);

        $provider = Provider::instance(
            'easypost',
            [
                'api_key' => $this->config['easypost_api_key'],
            ]
        );

        $validation = (bool) $provider->validate(new Address([
            'name' => 'Apple Store, Market Mall',
            'street1' => '1 Shaganappi Trail Way',
            'city' => 'Edmondton',
            'state' => 'Ontario',
            'zip' => 'T3A',
            'country' => 'CA',
        ]));
    }

}
