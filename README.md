PHP Questrade
===================================================

This package provide a thin abstraction layer around [Questrade API](https://www.questrade.com/api/home)

**This package is not affiliated, sponsored, or endorsed by Questrade in any way. The name Questrade, as well as related names and images, are registered trademarks of their respective owners.**


Requirements
------------
* PHP >= 5.6
* CURL extension


Installation
------------

```
composer require flaviovs/php-questrade
```


Usage
-----

```php
use fv\questrade\Client;
use fv\questrade\Error;
use fv\questrade\Token;

// Manual authorization can be done in
// https://login.questrade.com/APIAccess/UserApps.aspx
const REFRESH_TOKEN = 'MANUAL-AUTH-API-KEY';

$client = new Client(Client::URL_LIVE);

try {
	$token = $client->getAccessToken(REFRESH_TOKEN);
} catch (Error $ex) {
	echo $ex->getMessage() . "\n";
	exit(1);
}

foreach ($client->symbolsSearch($token, 'DJI') as $result) {
	print_r($result);
}

// By default this will fetch daily quotes from 7 day ago until
// today. See the code for more information.
// Note: 16434 == DJI symbol ID.
foreach ($client->marketsCandles($token, 16434) as $result) {
	print_r($result);
}
```


Bugs? Suggestions?
------------------
Visit https://github.com/flaviovs/php-questrade/issues
