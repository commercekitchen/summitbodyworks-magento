<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://tlstest.paypal.com/");
curl_setopt($ch, CURLOPT_SSLVERSION, 6);
var_dump(curl_exec($ch));

