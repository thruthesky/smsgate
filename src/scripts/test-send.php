<?php
use Drupal\Core\Http\Client;

$url_server = "http://sonub.org";
$username = 'canary';
$password = 'canary';

$client = new Client();

for ( $i = 0; $i < 3 ; $i ++) {
    $number = "0917-467-8603";
    $message = "Message No. $i";
    $url = "$url_server/smsgate/send?username=$username&password=$password&number=$number&message=$message";
    $response = $client->post($url, [], ['verify'=>false]);
    $code = $response->getStatusCode();
    $re = $response->json();
    if ( isset($re['error']) && $re['error'] ) {
        echo "ERROR - $re[error]\n";
    }
    else {
        echo "SUCCESS - $number : $message\n";
    }
}


