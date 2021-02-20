<?php

require_once 'vendor/autoload.php';

$username = 'username';
$password = 'password';
$espocrmUrl = 'https://espo.example.com';


$phoneNumber = $_GET['phone'];

if ( empty($phoneNumber) ) {
    exit(0);
}

$options = [
    'auth' => [
        $username,
        $password
    ],
    'timeout' => 1
];

$params = [
    'query' => [
        'maxSize' => 1,
        'offset' => 0,
        'sortBy' => 'createdAt',
        'asc' => 'false',
        'where' => [
            [
                'type' => 'equals',
                'attribute' => 'phoneNumber',
                'value' => $phoneNumber
            ]
        ]
    ]
];

$client = new GuzzleHttp\Client();

$name = get($client,'Lead','leadname');
if ( !is_null($name) ) {
    echo $name;
    exit(0);
}

$name = get($client,'Contact','accountName');
if ( !is_null($name) ) {
    echo $name;
    exit(0);
}

$name = get($client,'Account','billingAddressCountry');
if ( !is_null($name) ) {
    echo $name;
    exit(0);
}

exit(0);

function get($client,$entity,$second = null) {

    global $espocrmUrl, $options, $params;

    $response = $client->get($espocrmUrl.'/api/v1/'.$entity,array_merge($options,$params));

    if ($response->getStatusCode() != 200) {
        exit(0);
    }

    $result = json_decode($response->getBody(),true);

    if ($result['total'] == 0) {
        return null;

    }
    $format = "%s (%s)";
    if (empty($result['list'][0][$second])) {
        $format = "%s";
    }
    return sprintf($format, $result['list'][0]['name'],$result['list'][0][$second] );
}