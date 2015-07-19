<?php
use Drupal\Core\Http\Client;

$url_server = "http://sonub.org";
$username = 'thruth3esky';
$password = 'asdf99';

$client = new Client();

for ( $i = 0; $i < 3 ; $i ++) {
    $number = "0917-467-8603";
    $message = "Message No. $i";
    $url = "$url_server/smsgate/send?username=$username&password=$password&number=$number&message=$message";
    $response = $client->post($url, [], ['verify'=>false]);
    $code = $response->getStatusCode();
    $re = $response->json();
    //$re = json_decode($re,true);
    print_r($re);



    if ( isset($re['error']) && $re['error'] < 0 ) {
        echo "ERROR - $re[error] - $re[message]\n";
    }
    else {
        echo "SUCCESS - $number : $message\n";
    }


}


