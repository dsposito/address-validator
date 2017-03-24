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
     * @return Address
     */
    public function validate(Address $address): Address
    {
        // Name is a required field - just not in USPS's API.
        if (empty($address->name)) {
            throw new InvalidAddress();
        }

        $request = $this->buildXMLRequest($address);
        $response = $this->sendRequest($request);

        if (!$response || isset($response['Error'])) {
            throw new InvalidAddress();
        }

        $response['Name'] = strtoupper($address->name);

        return $this->buildAddress($response);
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
        $body->addChild('Address1', $address->street1);
        $body->addChild('Address2', $address->street2);
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
     * Builds an Address object based on key/value data.
     *
     * @param array $data Key/value data to populate object properties.
     *
     * @return Address
     */
    protected function buildAddress(array $data): Address
    {
        return new Address([
            'name' => $data['Name'],
            'street1' => $data['Address2'],
            'street2' => $data['Address1'],
            'city' => $data['City'],
            'state' => $data['State'],
            'zip' => $data['Zip5'],
            'country' => 'US',
        ]);
    }
}
