<?php

namespace TwitchAPI;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

class Videos
{
    /** @var Client */
    private $client;
    private $httpClient;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->httpClient = new HttpClient();
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
        return $this->client->requestGet('/videos', $query);
    }

    /**
     * @param $id
     * @param array $filter
     * @return array
     * @throws GuzzleException
     */
    public function getUserID($id, $filter = [])
    {
        $query = 'user_id=' . $id;
        if (!empty($filter)) {
            $query .= http_build_query($filter);
        }
        return $this->client->requestGet('/videos', $query);
    }

    /**
     * @param $id
     * @param array $filter
     * @return array
     * @throws GuzzleException
     */
    public function getGameID($id, $filter = [])
    {
        $query = 'game_id=' . $id;
        if (!empty($filter)) {
            $query .= http_build_query($filter);
        }
        return $this->client->requestGet('/videos', $query);
    }
}