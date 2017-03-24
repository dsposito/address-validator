<?php

namespace Dsposito\Validator\Provider;

use Dsposito\Validator\Address;
use Dsposito\Validator\Exception\InvalidAddress;
use Dsposito\Validator\Provider;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use SimpleXMLElement;

/**
 * Handles address validation with the USPS API.
 */
class Usps extends Provider
{
    /**
     * Returns validated and cleaned address information.
     *
     * @param Address $address The address data to validate.
     *
     * @return array
     */
    public function validate(Address $address)
    {
        // Name is a required field - just not in USPS's API.
        if (empty($address->name)) {
            throw new InvalidAddress();
        }

        // Streets should not be duplicated and avoid empty street1 from formatCleanedAddress().
        if ($address->street1 === $address->street2) {
            $address->street2 = '';
        }

        $request = $this->buildXMLRequest($address);
        $response = $this->sendRequest($request);

        if (!$response || isset($response['Error'])) {
            throw new InvalidAddress();
        }

        return $this->formatCleanedAddress($response, $address);
    }

    /**
     * Builds an XML object with address information.
     *
     * @param Address $address Unvalidated address object.
     *
     * @return SimpleXMLElement
     */
    protected function buildXMLRequest(Address $address): SimpleXMLElement
    {
        $element = new SimpleXMLElement(
            "<AddressValidateRequest USERID='" . $this->config['user_id'] . "'></AddressValidateRequest>"
        );

        $body = $element->addChild('Address');
        $body->addAttribute('ID', "1");
        // USPS requires that Address1 and Address2 be reversed from norm.
        $body->addChild('Address1', $address->street2);
        $body->addChild('Address2', $address->street1);
        $body->addChild('City', $address->city);
        $body->addChild('State', $address->state);
        $body->addChild('Zip5', $address->zip);
        $body->addChild('Zip4');

        return $element;
    }

    /**
     * Sends a request to the API.
     *
     * @param SimpleXMLElement $request XML address data to validate via the request.
     *
     * @return array|bool
     */
    protected function sendRequest(SimpleXMLElement $request)
    {
        try {
            $client = new GuzzleClient();
            $response = $client->post(
                $this->config['endpoint'],
                [
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'body' => [
                        'API' => 'Verify',
                        'XML' => $request->asXML(),
                    ],
                    'connect_timeout' => 2,
                    'timeout' => 4,
                ]
            );
        } catch (Exception $e) {
            return false;
        }

        if ($response->getStatusCode() != 200) {
            return false;
        }

        try {
            $response = json_decode(json_encode($response->xml()), true);

            return $response['Address'];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Standardizes the format of a validated, cleaned address.
     *
     * @param array $cleaned_address Validated, cleaned address data returned from the API.
     * @param Address $address Unvalidated address information object.
     *
     * @return array
     */
    protected function formatCleanedAddress(array $cleaned_address, Address $address): array
    {
        // USPS requires that Address1 and Address2 be reversed from norm.
        $formatted_address = [
            'street1' => self::formatValue($cleaned_address, 'Address2', $address->street1),
            'street2' => self::formatValue($cleaned_address, 'Address1', $address->street2),
            'city' => self::formatValue($cleaned_address, 'City', $address->city),
            'state' => self::formatValue($cleaned_address, 'State', $address->state),
            'zip' => self::formatValue($cleaned_address, 'Zip5', $address->zip),
            'country' => $address->country,
        ];

        $street_1 = str_replace($formatted_address['street2'], '', $formatted_address['street1']);
        $formatted_address['street1'] = trim($street_1);

        return $formatted_address;
    }

    /**
     * Formats capitalization for cleaned or default values.
     *
     * @param array $address Cleaned address information.
     * @param string $property Address property to search for in cleaned address.
     * @param string|null $default Value to return if property not found in address.
     *
     * @return string
     */
    protected function formatValue(array $address, string $property, $default = null): string
    {
        $value = $address[$property] ?? $default;

        return ucwords(strtolower($value));
    }
}
