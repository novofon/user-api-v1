<?php

use Novofon_API\Api;

require_once __DIR__.DIRECTORY_SEPARATOR.'include.php';

$api = new Api(KEY, SECRET);

// TODO: enter your values
$sourceNumber = ''; // in international format
$destinationNumber = '';
$checkNumber = '';
$sip = ''; // sip number
$pbx = ''; // internal number
$callId = '';
$directNumber = '';
$directNumberType = '';

// info methods
$result = $api->getBalance();
$result = $api->getTimezone();
$result = $api->getCurrencies();
$result = $api->getLanguages();

// sip methods
$result = $api->getSip();
$result = $api->getSipStatus($sip);

// pbx methods
$result = $api->getPbxInternal();
$result = $api->getPbxStatus($pbx);

// statistic methods
$result = $api->getStatistics();
$result = $api->getPbxStatistics();
$result = $api->getIncomingCallStatistics();
$result = $api->getCallbackWidgetStatistics();

// direct numbers methods
$result = $api->getDirectNumberCountries();
$result = $api->getDirectNumbers();
$result = $api->getDirectNumber($directNumber, $directNumberType);

// other methods
$result = $api->getPbxRecord($callId, null);
$result = $api->requestCallback($pbx, $destinationNumber);
$result = $api->requestCheckNumber($sourceNumber, $destinationNumber, '12345');

