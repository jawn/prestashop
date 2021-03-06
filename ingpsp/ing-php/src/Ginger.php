<?php

namespace GingerPayments\Payment;

use GuzzleHttp\Client as HttpClient;
use Assert\Assertion as Guard;

final class Ginger
{
    /**
     * The library version.
     */
    const CLIENT_VERSION = '1.4.0';

    /**
     * The API version.
     */
    const API_VERSION = 'v1';

    /**
     * Create a new API client.
     *
     * @param string $apiKey Your API key.
     * @param string $product
     * @return Client
     */
    public static function createClient($apiKey, $product = null)
    {
        Guard::uuid(
            static::apiKeyToUuid($apiKey),
            'ING API key is invalid: '.$apiKey
        );

        return new Client(
            new HttpClient(
                [
                    'base_url' => [
                        static::getEndpoint($product),
                        ['version' => self::API_VERSION]
                    ],
                    'defaults' => [
                        'headers' => [
                            'User-Agent' => 'ing-php/'.self::CLIENT_VERSION,
                            'X-PHP-Version' => PHP_VERSION
                        ],
                        'auth' => [$apiKey, '']
                    ]
                ]
            )
        );
    }

    /**
     * Get API endpoint based on product
     *
     * @param string $product
     * @return string
     */
    public static function getEndpoint($product)
    {
        switch ($product) {
            case 'kassacompleet':
                return (new Client\EndpointResolver())->getEndpointKassa();
            case 'ingcheckout':
                return (new Client\EndpointResolver())->getEndpointIng();
            case 'epay':
                return (new Client\EndpointResolver())->getEndpointEpay();
            default:
                return (new Client\EndpointResolver())->getEndpointGinger();
        }
    }

    /**
     * Method restores dashes in Ginger API key in order to validate UUID.
     *
     * @param string $apiKey
     * @return string UUID
     */
    public static function apiKeyToUuid($apiKey)
    {
        return preg_replace('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/', '$1-$2-$3-$4-$5', $apiKey);
    }
}
