<?php
use Drupal\Core\Http\Client;

$url_server = "http://sonub.org";
$url_server = "http://dev.withcenter.com";
$username = 'thruthesky';
$password = 'asdf99';

$client = new Client();

for ( $i = 0; $i < 9 ; $i ++) {
    $number = "09054776789";
    $message = "D Message No. $i";
    $url = "$url_server/smsgate/send?username=$username&password=$password&number=$number&message=$message";
    $response = $client->post($url, [], ['verify'=>false]);
    $code = $response->getStatusCode();
    $re = $response->json();
    //$re = json_decode($re,true);
    // print_r($re);

    if ( isset($re['error']) && $re['error'] < 0 ) {
        echo "ERROR ($re[error]) : $re[message]\n";
    }
    else {
        echo "SUCCESS - $number : $message\n";
    }
}


