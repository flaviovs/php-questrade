<?php

namespace fv\questrade;

class Client
{
    const URL_LIVE = 'https://login.questrade.com';

    const URL_PRACTICE = 'https://practicelogin.questrade.com';

    const INTERVAL_M1 = 'OneMinute';
    const INTERVAL_M2 = 'TwoMinutes';
    const INTERVAL_M3 = 'ThreeMinutes';
    const INTERVAL_M4 = 'FourMinutes';
    const INTERVAL_M5 = 'FiveMinutes';
    const INTERVAL_M10 = 'TenMinutes';
    const INTERVAL_M15 = 'FifteenMinutes';
    const INTERVAL_M20 = 'TwentyMinutes';
    const INTERVAL_M30 = 'HalfHour';
    const INTERVAL_H1 = 'OneHour';
    const INTERVAL_H2 = 'TwoHours';
    const INTERVAL_H4 = 'FourHours';
    const INTERVAL_D1 = 'OneDay';
    const INTERVAL_WK1 = 'OneWeek';
    const INTERVAL_MO1 = 'OneMonth';
    const INTERVAL_Y1 = 'OneYear';

    protected $url;


    public function __construct($url = self::URL_PRACTICE)
    {
        $this->url = $url;
    }


    public function getAccessToken($refreshToken)
    {
        $res = $this->call(null, '/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        return Token::createFromOauth2Result($res);
    }


    public function symbolsSearch(Token $token, $prefix, $offset = null)
    {
        $options = ['prefix' => $prefix];
        if ($offset !== null) {
            $options['offset'] = $offset;
        }
        $res = $this->call($token, 'v1/symbols/search', $options);

        if (!isset($res['symbols'])) {
            throw Error('Unexpected result set');
        }

        return $res['symbols'];
    }

    public function marketsCandles(
        Token $token,
        $symbolId,
        \DateTimeInterface $startTime = null,
        \DateTimeInterface $endTime = null,
        $interval = self::INTERVAL_D1
    ) {
        if (!$endTime) {
            $endTime = new \DateTimeImmutable();
        }

        if (!$startTime) {
            $startTime = $endTime->sub(new \DateInterval('P7D'));
        }

        $res = $this->call($token, "v1/markets/candles/$symbolId", [
            'startTime' => $startTime->format('c'),
            'endTime' => $endTime->format('c'),
            'interval' => $interval,
        ]);

        if (!isset($res['candles'])) {
            throw Error('Unexpected result set');
        }

        return $res['candles'];
    }


    public function call($token, $path, array $query = null, array $data = null)
    {
        $url = $token ? $token->getApiServerUrl() : $this->url;

        $url .= '/' . $path;
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
        ]);

        if ($token) {
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                ['Authorization: ' . $token->getType() . ' ' . $token->getAccessToken()]
            );
        }

        if ($data !== null) {
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
            ]);
        }

        $res = curl_exec($ch);
        if (!$res) {
            throw new Error('Call failed: ' . curl_error($ch));
        }

        $http_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($http_code !== 200) {
            throw new Error(
                "Unexpected HTTP response $http_code",
                $http_code
            );
        }

        $arr = json_decode($res, true);
        if (!$arr) {
            throw new Error("Empty response");
        }

        return $arr;
    }
}
