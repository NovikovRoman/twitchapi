<?php

namespace TwitchAPI;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

class Users
{
    private $client;
    private $httpClient;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->httpClient = new HttpClient();
    }

    /**
     * @param $after
     * @param $first
     * @param $fromID
     * @param $toID
     * @return array
     * @throws GuzzleException
     */
    public function follows($after, $first, $fromID, $toID)
    {
        $query = [];
        if ($after) {
            $query['after'] = $after;
        }
        if ($first) {
            $query['first'] = $first;
        }
        if ($fromID) {
            $query['from_id'] = $fromID;
        }
        if ($toID) {
            $query['to_id'] = $toID;
        }
        return $this->client->requestGet('/users/follows', $query);
    }

    /**
     * @param array $ids
     * @return array
     * @throws GuzzleException
     */
    public function getID(array $ids = [])
    {
        $query = '';
        if (!empty($ids)) {
            $query = 'id=' . implode('&id=', $ids);
        }
        return $this->client->requestGet('/users', $query);
    }

    /**
     * @param array $logins
     * @return array
     * @throws GuzzleException
     */
    public function getLogin(array $logins = [])
    {
        $query = '';
        if (!empty($logins)) {
            $query = 'login=' . implode('&login=', $logins);
        }
        return $this->client->requestGet('/users', $query);
    }
}