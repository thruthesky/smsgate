<?php
use Drupal\Core\Http\Client;
$url_server = "http://sonub.org";
$url = "$url_server/smsgate/loadData";
$client = new Client();



for($i=0; $i<=150; $i++) {
    $response = $client->get($url, ['verify'=>false]);
    $code = $response->getStatusCode();
    $re = $response->json();
    $url_record = "$url_server/smsgate/record_send_result?id=$re[id]&result=Y&sender=drush-php-script";
    $response = $client->get($url_record, ['verify'=>false]);
    $code = $response->getStatusCode();
    echo "record response status code: $code\n";
    print_r($re);
}




