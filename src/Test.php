<?php

namespace Webmasterskaya\Unisender;

use PsrDiscovery\Discover;
use PsrDiscovery\Entities\CandidateEntity;
use PsrDiscovery\Implementations\Psr18\Clients;
use PsrDiscovery\Implementations\Psr3\Logs;

class Test
{
    public static function getClient()
    {
        // Add Joomla Log PSR-3 implementation
        Logs::add(CandidateEntity::create(
            'joomla/log',
            '~1',
            static function (string $class = '\Joomla\Log\Log') {
                return call_user_func([$class, 'createDelegatedLogger']);
            }
        ));

        Clients::add(CandidateEntity::create(
            'joomla/http',
            '~3',
            static function (string $class = '\Joomla\Http\Http') {
                return new $class;
            }
        ));

        /** @var \Psr\Http\Client\ClientInterface $client */
        $client   = Discover::httpClient(true);
        $requestFactory  = Discover::httpRequestFactory();
        $request = $requestFactory->createRequest('GET', 'https://webmasterskaya.xyz');
        $response = $client->sendRequest($request);
        var_dump($response->getBody()->__toString());
    }
}