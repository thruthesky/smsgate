<?php
use Drupal\Core\Http\Client;

$url_server = "http://dev.withcenter.com";
$username = 'thruthesky';
$password = 'asdf99';

$url_server = "http://sonub.org";
$username = 'test1126';
$password = 'test1126';


$client = new Client();
$priority = 11;
$number = '09174678603';
$message = "Priority: $priority stamp:" . time();
$url = "$url_server/smsgate/send?username=$username&password=$password&number=$number&message=$message&priority=$priority";
$response = $client->post($url, [], ['verify'=>false]);
$code = $response->getStatusCode();
$re = $response->json();
if ( isset($re['error']) && $re['error'] < 0 ) {
    echo "ERROR ($re[error]) : $re[message]\n";
}
else {
    echo "SUCCESS - $number : $message\n";
}
