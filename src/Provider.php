<?php

namespace Dsposito\Validator;

/**
 * Abstract class for address validation provider classes.
 */
abstract class Provider
{
    const PROVIDER_EASYPOST = 'easypost';
    const PROVIDER_USPS = 'usps';

    protected $config;

    /**
     * Returns a singleton object of the Provider class.
     *
     * @param string $provider The name of the validator provider to instantiate.
     * @param array $options Optional key/value pair meta data.
     *
     * @return Provider
     */
    public static function instance(string $provider, array $options = array())
    {
        $class = __NAMESPACE__ . '\\Provider\\' . ucfirst($provider);
        if (!class_exists($class)) {
            return false;
        }

        $provider = new $class();
        $provider->config = $options;

        return $provider;
    }

    /**
     * Validate and format address information.
     *
     * @param Address $address Object with data to be validated.
     *
     * return Object|bool
     */
    abstract function validate(Address $address);
}
