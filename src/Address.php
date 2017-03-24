<?php

namespace Dsposito\Validator;

/**
 * Handles address interactions.
 */
class Address
{
    public $name;
    public $street1;
    public $street2;
    public $city;
    public $state;
    public $zip;
    public $country;

    /**
     * Initializes the class.
     *
     * @param array $data Key/value data to populate object properties.
     *
     * @return void
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->loadData($data);
        }
    }

    /**
     * Attempts to map array data to object properties.
     *
     * @param array $data Key/value data to populate object properties.
     *
     * @return void
     */
    protected function loadData(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
