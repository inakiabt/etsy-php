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

    public function requestPermissionUrl(array $extra = array())
    {
        $this->request_token = $this->client->getRequestToken($extra);

        return $this->request_token['login_url'];
    }

    public function getAccessToken($verifier, $oauth_token = false, $oauth_token_secret = false)
    {
        if (!$oauth_token) {
            $oauth_token = $this->request_token['oauth_token'];
        } else {
            $this->request_token['oauth_token'] = $oauth_token;
        }
        if (!$oauth_token_secret) {
            $oauth_token_secret = $this->request_token['oauth_token_secret'];
        } else {
            $this->request_token['oauth_token_secret'] = $oauth_token_secret;
        }
        $this->client->authorize($oauth_token, $oauth_token_secret);
        $this->access_token = $this->client->getAccessToken($verifier);
        return $this->getAuth();
    }

    public function getAuth()
    {
        $auth = array();
        $auth['consumer_key'] = $this->client->getConsumerKey();
        $auth['consumer_secret'] = $this->client->getConsumerSecret();
        $auth['token_secret'] = $this->request_token['oauth_token'];
        $auth['token'] = $this->request_token['oauth_token_secret'];
        $auth['access_token'] = $this->access_token['oauth_token'];
        $auth['access_token_secret'] = $this->access_token['oauth_token_secret'];

        return $auth;
    }
}