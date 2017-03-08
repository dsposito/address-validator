<?php

namespace Dsposito\Validator\Tests\Integration;

use Dsposito\Validator\Address;
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
}
