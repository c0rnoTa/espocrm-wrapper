<?php
require_once 'vendor/autoload.php';

use Drei\EspoCRM\Client\EspoClient;

$client = EspoClient::factory([
    'url'     => 'http://plus.dev/',     // required
    'username' => 'admin', // required
    'token' => 'admin' // required
]);


$command = $client->getCommand('list', [
    'entityType' => 'Account',
    'maxSize'  => 10
]);

$results = (array) $client->execute($command); // returns an array of results