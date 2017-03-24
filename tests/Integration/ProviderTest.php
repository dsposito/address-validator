<?php

namespace Dsposito\Validator\Tests\Integration;

use Dsposito\Validator\Address;
use Dsposito\Validator\Exception\InvalidAddress;
use Dsposito\Validator\Provider;

class ProviderTest extends \Dsposito\Validator\Tests\TestCase
{
    public function testUSAddressIsValid()
    {
        $address = $this->getProviderUsps()->validate(new Address([
            'name' => 'Elon Musk',
            'street1' => '3500 Deer Creek Road',
            'street2' => 'STE 3',
            'city' => 'Palo Alto',
            'state' => 'CA',
            'zip' => '94304',
            'country' => 'US',
        ]));

        $this->assertEquals('ELON MUSK', $address->name);
        $this->assertEquals('3500 DEER CREEK RD', $address->street1);
        $this->assertEquals('STE 3', $address->street2);
        $this->assertEquals('PALO ALTO', $address->city);
        $this->assertEquals('CA', $address->state);
        $this->assertEquals('94304', $address->zip);
        $this->assertEquals('US', $address->country);
    }

    public function testUSAddressIsInvalid()
    {
        $this->expectException(InvalidAddress::class);

        $this->getProviderUsps()->validate(new Address([
            'name' => 'Elon Musk',
            'street1' => '3555 Deer Creek Lane',
            'street2' => 'STE 3',
            'city' => 'Palo Alto',
            'state' => 'NV',
            'zip' => '93333',
            'country' => 'US',
        ]));
    }

    public function testCAAddressIsValid()
    {
        $address = $this->getProviderEasyPost()->validate(new Address([
            'name' => 'Apple Store, Market Mall',
            'street1' => '3625 Shaganappi Trail NW',
            'street2' => 'STE 1',
            'city' => 'Calgary',
            'state' => 'Alberta',
            'zip' => 'T3A 0E2',
            'country' => 'CA',
        ]));

        $this->assertEquals('APPLE STORE, MARKET MALL', $address->name);
        $this->assertEquals('3625 SHAGANAPPI TRAIL NW', $address->street1);
        $this->assertEquals('STE 1', $address->street2);
        $this->assertEquals('CALGARY', $address->city);
        $this->assertEquals('AB', $address->state);
        $this->assertEquals('T3A 0E2', $address->zip);
        $this->assertEquals('CA', $address->country);
    }

    public function testCAAddressIsInvalid()
    {
        $this->expectException(InvalidAddress::class);

        $this->getProviderEasyPost()->validate(new Address([
            'name' => 'Apple Store, Market Mall',
            'street1' => '1 Shaganappi Trail Way',
            'street2' => 'STE 1',
            'city' => 'Edmondton',
            'state' => 'Ontario',
            'zip' => 'T3A',
            'country' => 'CA',
        ]));
    }

    protected function getProviderUsps(): Provider
    {
        return Provider::instance(
            'usps',
            [
                'endpoint' => 'http://production.shippingapis.com/ShippingAPI.dll',
                'user_id' => $this->config['usps_user_id'],
            ]
        );
    }

    protected function getProviderEasyPost(): Provider
    {
        return Provider::instance(
            'easypost',
            [
                'api_key' => $this->config['easypost_api_key'],
            ]
        );
    }
}
