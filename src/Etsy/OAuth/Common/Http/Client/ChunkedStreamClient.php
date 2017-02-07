<?php

namespace Etsy\OAuth\Common\Http\Client;

use OAuth\Common\Http\Client\StreamClient;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\UriInterface;

/**
 * Client implementation for streams/file_get_contents using HTTP 1.0
 * because StreamClient (HTTP 1.1) does not handle chunked streams
 */
class ChunkedStreamClient extends StreamClient
{
    private function generateStreamContext($body, $headers, $method)
    {
        return stream_context_create(
            array(
                'http' => array(
                    'method'           => $method,
                    'header'           => implode("\r\n", array_values($headers)),
                    'content'          => $body,
                    'protocol_version' => '1.0',
                    'user_agent'       => $this->userAgent,
                    'max_redirects'    => $this->maxRedirects,
                    'timeout'          => $this->timeout
                ),
            )
        );
    }
}