<?php

require_once 'vendor/autoload.php';

$username = 'username';
$password = 'password';
$espocrmUrl = 'https://espo.example.com';
$checkSpamUrl = 'https://www.neberitrubku.ru/nomer-telefona';

$options = [
    'auth' => [
        $username,
        $password
    ],
    'timeout' => 1
];

$phoneNumber = format($_GET['phone']);

if ( empty($phoneNumber) ) {
    exit(0);
}

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

if (CheckSpam($client)) {
    echo "SPAM!";
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

function format($phone) {

    $phone = preg_replace('/[^0-9]/','',$phone);

    if ( strlen($phone) == 11 && substr($phone,0,1) == 8) {
        return '+7'.substr($phone,-10);
    }

    if ( strlen($phone)==10 ) {
        return '+7'.$phone;
    }

    if (substr($phone,0,1) != '+') {
        return '+'.$phone;
    }

    return $phone;
}

function CheckSpam($client) {

    global $checkSpamUrl,$phoneNumber;

    $response = $client->get($checkSpamUrl.'/'.$phoneNumber);

    if ($response->getStatusCode() != 200) {
        return false;
    }

    $result = (string) $response->getBody();

    if (strpos($result, '<div class="score negative">') !== false) {
        return true;
    }

    return false;
}