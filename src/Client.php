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

    public function __construct(string $id, string $secret, array $scope = [], string $redirectUri = 'https://localhost.me')
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

    public function requestPostAuth($path, $params, $headers = [])
    {
        $params = $this->addParams($params, $headers);
        $params['headers'] = array_merge($params['headers'], $this->getAuthHeaders());
        return $this->requestPost($path, $params);
    }

    public function requestPostClient($path, $params, $headers = [])
    {
        $params = $this->addParams($params, $headers);
        $params['headers'] = array_merge($params['headers'], $this->getClientHeaders());
        return $this->requestPost($path, $params);
    }

    public function requestGetAuth($path, $params, $headers = [])
    {
        $headers = array_merge($headers, $this->getAuthHeaders());
        return $this->requestGet($path, $params, $headers);
    }

    public function requestGetClient($path, $params, $headers = [])
    {
        $headers = array_merge($headers, $this->getClientHeaders());
        return $this->requestGet($path, $params, $headers);
    }

    /**
     * @param $path
     * @param $params
     * @return array
     */
    private function requestPost($path, $params)
    {
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
    private function requestGet($path, $query = [], $headers = [])
    {
        $url = self::baseUrl . $path . (empty($query) ? '' : '?');
        $url .= is_array($query) ? http_build_query($query) : $query;

        try {
            $content = $this->httpClient->request('GET', $url, [
                'headers' => $headers,
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

    private function getAuthHeaders(): array
    {
        return [
            'Authorization' => ucfirst($this->getToken()->getTokenType())
                . ' ' . $this->getToken()->getAccessToken(),
        ];
    }

    private function getClientHeaders(): array
    {
        return [
            'Client-ID' => $this->getClientID(),
        ];
    }

    private function addParams($params, $headers)
    {
        $params = [
            'verify' => false,
            'headers' => $headers,
            'body' => http_build_query($params),
        ];
        return $params;
    }
}