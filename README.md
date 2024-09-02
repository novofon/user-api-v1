# Novofon API - User class
An official PHP class for work with Novofon API.

Allows to work with all API methods (including VoIP, PBX, CallBack etc).

## Requirements:
- PHP >= 5.5.0
- cURL
- TLS v1.2

## How to use?
An official documentation on Novofon API is [here](https://novofon.com/instructions/api/).

Keys for authorization are in [personal account](https://my.novofon.ru/vpbx/employee).

## Installation
### Via Сomposer
```sh
composer require "novofon/user-api-v1"
```
or just add this line to your `composer.json` file:
```json
"novofon/user-api-v1"
```

### Via Git
```sh
git clone git@github.com:novofon/user-api-v1.git
```

###  \Novofon_API\Api call code example
```php
<?php
include_once '/PATH/TO/vendor/autoload.php'; 
$api = new \Novofon_API\Api(KEY, SECRET);
try{
    $result = $api->getSipStatus('YOURSIP');
    echo $result->sip.' status: '.($result->is_online ? 'online' : 'offline');
} catch (\Novofon_API\ApiException $e) {
    echo 'Error: '.$e->getMessage();
}

```
All other examples you can see in the "[example file](https://github.com/novofon/user-api-v1/tree/master/examples/index.php)".

###  \Novofon_API\Client call code example
```php
<?php

include_once '/PATH/TO/lib/Client.php';

$params = array(
    'id' => 'YOURSIP',
    'status' => 'on'
);

$api = new \Novofon_API\Client(YOUR_KEY, YOUR_SECRET);
/*
$api->call('METHOD', 'PARAMS_ARRAY', 'REQUEST_TYPE', 'FORMAT', 'IS_AUTH');
where:
- METHOD - a method API, started from /v1/ and ended by '/';
- PARAMS_ARRAY - an array of parameters to a method;
- REQUEST_TYPE: GET (default), POST, PUT, DELETE;
- FORMAT: json (default), xml;
- IS_AUTH: true (default), false - is method under authentication or not.
*/
$answer = $api->call('/v1/sip/', $params);
$answerObject = json_decode($answer);

```

All other examples you can see in the "[examples](https://github.com/novofon/user-api-v1/tree/master/examples)" folder.
