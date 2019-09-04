<?php

namespace TwitchAPI;

use AuthManager\OAuthClientInterface;
use AuthManager\OAuthTokenInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;

class Client implements OAuthClientInterface
{
    const baseUrl = 'https://api.twitch.tv/helix';
    const tokenUrl = 'https://id.twitch.tv/oauth2/token';
    const authorizeUrl = 'https://id.twitch.tv/oauth2/authorize';

    private $id;
    private $secret;
    /** @var OAuthTokenInterface */
    private $token;
    private $redirectUri;
    private $scope;
    /** @var HttpClient */
    private $httpClient;

    public function __construct($id, $secret, $scope = [], $redirectUri = 'https://localhost.me')
    {
        $this->id = $id;
        $this->secret = $secret;
        $this->redirectUri = $redirectUri;
        $this->scope = $scope;
        $this->httpClient = new HttpClient();
    }

    public function setToken(OAuthTokenInterface $token)
    {
        $this->token = $token;
        return $this;
    }

    public function getScope(): array
    {
        return $this->scope;
    }

    public function getAuthorizeURL(): string
    {
        return self::authorizeUrl;
    }

    public function getTokenUrl(): string
    {
        return self::tokenUrl;
    }

    public function getClientID(): string
    {
        return $this->id;
    }

    public function getSecretKey(): string
    {
        return $this->secret;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function getToken(): OAuthTokenInterface
    {
        return $this->token;
    }

    public function getAuthHeaders(): array
    {
        $a = ucfirst($this->getToken()->getTokenType()) . ' ' . $this->getToken()->getAccessToken();
        return [
            'Authorization' => $a,
            'Client-ID' => $this->getClientID(),
        ];
    }

    /**
     * @param $path
     * @param $params
     * @param array $headers
     * @return array
     */
    public function requestPost($path, $params, $headers = [])
    {
        $params = [
            'verify' => false,
            'headers' => $headers,
            'body' => http_build_query($params),
        ];
        $params['headers'] = array_merge($params['headers'], $this->getAuthHeaders());

        try {
            $content = $this->httpClient->request(
                'POST',
                self::baseUrl . $path,
                $params
            )->getBody()->getContents();
            $resp = json_decode($content, true);

        } catch (RequestException $e) {
            $resp = json_decode($e->getResponse()->getBody()->getContents(), true);
            if (!$resp) {
                return [
                    'error' => $e->getResponse()->getReasonPhrase(),
                    'status' => $e->getResponse()->getStatusCode(),
                    'message' => $e->getMessage(),
                ];
            }

        } catch (GuzzleException $e) {
            return [
                'error' => 'Internal Server Error',
                'status' => 500,
                'message' => $e->getMessage(),
            ];
        }

        return $resp;
    }

    /**
     * @param $path
     * @param array|string $query
     * @param array $headers
     * @return array
     */
    public function requestGet($path, $query = [], $headers = [])
    {
        $url = self::baseUrl . $path . (empty($query) ? '' : '?');
        $url .= is_array($query) ? http_build_query($query) : $query;

        try {
            $content = $this->httpClient->request('GET', $url, [
                'headers' => array_merge($headers, $this->getAuthHeaders()),
            ])->getBody()->getContents();
            $resp = json_decode($content, true);

        } catch (RequestException $e) {
            $resp = json_decode($e->getResponse()->getBody()->getContents(), true);
            if (!$resp) {
                return [
                    'error' => $e->getResponse()->getReasonPhrase(),
                    'status' => $e->getResponse()->getStatusCode(),
                    'message' => $e->getMessage(),
                ];
            }

        } catch (GuzzleException $e) {
            return [
                'error' => 'Internal Server Error',
                'status' => 500,
                'message' => $e->getMessage(),
            ];
        }

        return $resp;
    }
}