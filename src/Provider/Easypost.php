<?php

namespace Dsposito\Validator\Provider;

use Dsposito\Validator\Address;
use Dsposito\Validator\Exception\InvalidAddress;
use Dsposito\Validator\Provider;
use EasyPost\Address as EasyPostAddress;
use EasyPost\EasyPost as EasyPostClient;
use Exception;

/**
 * Handles address validation with this EasyPost API.
 */
class Easypost extends Provider
{
    /**
     * Returns validated and cleaned address information.
     *
     * @param Address $address The address data to validate.
     *
     * @return Address
     */
    public function validate(Address $address): Address
    {
        $response = $this->sendRequest($address);

        if (!$response || !$response->verifications->delivery->success) {
            throw new InvalidAddress();
        }

        return new Address($response->__toArray());
    }

    /**
     * Sends a request to the API.
     *
     * @param Address $address The address data to valid via the request.
     *
     * @return array|bool
     */
    protected function sendRequest(Address $address)
    {
        try {
            EasyPostClient::setApiKey($this->config['api_key']);

            return EasyPostAddress::create_and_verify([
                'name' => $address->name,
                'street1' => $address->street1,
                'street2' => $address->street2,
                'city' => $address->city,
                'state' => $address->state,
                'zip' => $address->zip,
                'country' => $address->country,
                'verify' => ['delivery'],
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
