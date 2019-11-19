<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use WiziYousignClient\WiziSignClient;


$testkey = 'Your api key';

$client = new WiziSignClient($testkey);

// var_dump($client->getUsers());
var_dump($client->proc2('testPDFPourYS.pdf'));
