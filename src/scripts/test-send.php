<?php
use Drupal\Core\Http\Client;


$url_server = "http://sonub.org";
$username = 'canary';
$password = 'canary';

$url_server = "http://dev.withcenter.com";
$username = 'thruthesky';
$password = 'asdf99';



$numbers = [
    '09174678603',
    '09067104270',
    '09057210344',
    '0935-1123-877', // galaxy ace
    '0918-393-2954', // king kom
];

$client = new Client();

for ( $i = 0; $i < 10 ; $i ++) {
    foreach( $numbers as $number) {
        $message = "L Message No. $i";
        $url = "$url_server/smsgate/send?username=$username&password=$password&number=$number&message=$message";
        $response = $client->post($url, [], ['verify'=>false]);
        $code = $response->getStatusCode();
        $re = $response->json();
        if ( isset($re['error']) && $re['error'] < 0 ) {
            echo "ERROR ($re[error]) : $re[message]\n";
        }
        else {
            echo "SUCCESS - $number : $message\n";
        }
    }
}


