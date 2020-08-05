<?php

namespace fv\questrade;

class Token
{
    protected $time;

    protected $accessToken;

    protected $type;

    protected $expiresIn;

    protected $refreshToken;

    protected $apiServer;


    public function __construct(
        $access_token,
        $type,
        $expires_in,
        $refresh_token,
        $api_server
    ) {
        $this->time = time();
        $this->accessToken = $access_token;
        $this->type = $type;
        $this->expiresIn = $expires_in;
        $this->refreshToken = $refresh_token;
        $this->apiServer = rtrim($api_server, '/');
    }


    public function getExpireTimestamp()
    {
        return $this->time + $this->expiresIn;
    }


    public static function createFromOauth2Result(array $data)
    {
        return new self(
            $data['access_token'],
            $data['token_type'],
            intval($data['expires_in']),
            $data['refresh_token'],
            $data['api_server']
        );
    }


    public function getApiServerUrl()
    {
        return $this->apiServer;
    }


    public function getType()
    {
        return $this->type;
    }


    public function getAccessToken()
    {
        return $this->accessToken;
    }


    public function getRefreshToken()
    {
        return $this->refreshToken;
    }
}
