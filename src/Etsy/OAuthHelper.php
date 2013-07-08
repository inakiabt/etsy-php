<?php
namespace Etsy;

/**
* 
*/
class OAuthHelper
{
    private $client;
    private $request_token = array();
    private $access_token = array();

    function __construct($client)
    {
        $this->client = $client;
    }

    public function requestPermissionUrl($permissions = 'oob')
    {
        $this->request_token = $this->client->getRequestToken($permissions);

        return $this->request_token['login_url'];
    }

    public function getAccessToken($verifier)
    {
        $this->client->authorize($this->request_token['oauth_token'], $this->request_token['oauth_token_secret']);

        $this->access_token = $this->client->getAccessToken($verifier);

        return $this->getAuth();
    }

    public function getAuth()
    {
        $auth = array();
        $auth['token_secret'] = $this->request_token['oauth_token'];
        $auth['token'] = $this->request_token['oauth_token_secret'];
        $auth['access_token'] = $this->access_token['oauth_token'];
        $auth['access_token_secret'] = $this->access_token['oauth_token_secret'];

        return $auth;
    }
}