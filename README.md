# ExactApi
Exact Globe PHP API with Clickker

Install via composer:
````
composer require soerbv/exactapi
````

Usage:
````php
$client = new Client($host, $administration, $apikey);
$data = $client->getItemStock("BEK0003");
print_r(json_decode($data));
````