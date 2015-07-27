<?php
use Drupal\Core\Http\Client;
$url_server = "http://sonub.org";
$url = "$url_server/smsgate/loadData";
$client = new Client();



for($i=0; $i<=50; $i++) {
    $response = $client->get($url, ['verify'=>false]);
    $code = $response->getStatusCode();
    $re = $response->json();
    print_r($re);
}

