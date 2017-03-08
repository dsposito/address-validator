<?php

namespace Dsposito\Validator\Tests\Unit;

use Dsposito\Validator\Provider;

class ProviderTest extends \Dsposito\Validator\Tests\TestCase
{
    public function testProviderIsUsps()
    {
        $provider = Provider::instance('usps');

        $this->assertInstanceOf('Dsposito\Validator\Provider\Usps', $provider);
    }

    public function testProviderIsEasypost()
    {
        $provider = Provider::instance('easypost');

        $this->assertInstanceOf('Dsposito\Validator\Provider\Easypost', $provider);
    }
}
