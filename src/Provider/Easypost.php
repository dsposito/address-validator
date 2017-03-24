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
     * @return array|bool
     */
    public function validate(Address $address)
    {
        $response = $this->sendRequest($address);

        if (!$response || !$response->verifications->delivery->success) {
            throw new InvalidAddress();
        }

        return $this->formatAddress($response);
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

    /**
     * Standardizes the format of a validated, cleaned address.
     *
     * @param EasyPostAddress $address Response address to be formatted.
     *
     * @return array
     */
    protected function formatAddress(EasyPostAddress $address): array
    {
        return [
            'street1' => self::formatValue($address->street1),
            'street2' => self::formatValue($address->street2),
            'city' => self::formatValue($address->city),
            'state' => $address->state,
            'zip' => $address->zip,
            'country' => $address->country,
        ];
    }

    /**
     * Formats capitalization for cleaned values.
     *
     * @param string $value Address value to format.
     *
     * @return string
     */
    protected function formatValue($value): string
    {
        return ucwords(strtolower($value));
    }
}
