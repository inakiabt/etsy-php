<?php
namespace Etsy;

/**
*
*/
interface ClientInterface {

    public function request($path, $params = array(), $method = 'GET', $json = true);
    public function getRequestToken(array $extra = array());
    public function getAccessToken($access_token, $verifier);

}
