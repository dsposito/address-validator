<?php

namespace Dsposito\Validator\Provider;

use Dsposito\Validator\Address;
use Dsposito\Validator\Provider;
use EasyPost\Address as EasyPostAddress;
use EasyPost\EasyPost as EasyPostClient;
use Exception;

/**
 * Handles address validation with EasyPost API.
 */
class Easypost extends Provider
{
    /**
     * Returns validated and cleaned address information.
     *
     * @param Address $address Address object with uncleaned, unvalidated information.
     *
     * @return array|bool The cleaned address or a false value on failure.
     */
    public function validate(Address $address)
    {
        try {
            EasyPostClient::setApiKey($this->config['api_key']);

            $verified_address = EasyPostAddress::create_and_verify([
                'name' => $address->name,
                'street1' => $address->street1,
                'street2' => $address->street2,
                'city' => $address->city,
                'state' => $address->state,
                'zip' => $address->zip,
                'country' => $address->country,
                'verify' => ['delivery']
            ]);
        } catch (Exception $e) {
            return false;
        }

        if ($verified_address->verifications->delivery->success) {
            $address->setValidated();

            return $this->formatAddress($verified_address);
        }

        return false;
    }

    /**
     * Standardizes the format of a validated, cleaned address.
     *
     * @param EasyPostAddress $address Response address to be formatted.
     *
     * @return array
     */
    protected function formatAddress(EasyPostAddress $address)
    {
        return [
            'street1' => self::formatValue($address->street1),
            'street2' => self::formatValue($address->street2),
            'city' => self::formatValue($address->city),
            'state' => $address->state,
            'zip' => $address->zip,
            'country' => $address->country
        ];
    }

    /**
     * Formats capitalization for cleaned values.
     *
     * @param string $value Address value to format.
     *
     * @return string
     */
    protected function formatValue($value)
    {
        return (ucwords(strtolower($value)));
    }
}
