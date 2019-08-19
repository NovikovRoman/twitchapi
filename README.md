```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use AuthManager\OAuthManager;
use GuzzleHttp\Exception\GuzzleException;
use TwitchAPI\Client;
use TwitchAPI\Users;

$clientID = 'our client id';
$secretKey = 'our secret key';
$client = new Client(
   $clientID,
   $secretKey,
    ['user:read:email', 'user:read:broadcast'],
    'https://our.domain'
);

$am = new OAuthManager($client);
$state = 123456;

if (!empty($_GET['code'])) {

    try{
        $token = $am->getToken($_SERVER['REQUEST_URI'], $state);
        $client->setToken($token);
        $users = new Users($client);

        $userID = 1234567890;
        $login = 'login some user';
        print_r($users->getLogin([$login]));
        print_r($users->follows('', '', '', $userID));
        print_r($users->follows('', '', $userID, ''));

    } catch (GuzzleException $e) {
        exit($e->getMessage());

    } catch (Exception $e) {
        exit($e->getMessage());
    }

} else {
    $am->signin($state,true);
}
```