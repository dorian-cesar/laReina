<?php


$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/user/auth',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => '{"login":"monitoreomreina@masgps.cl","password":"Monitoreo2024.","dealer_id":10004282,"locale":"es","hash":null}',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json, text/plain, */*',
    'Accept-Language: es-419,es;q=0.9,en;q=0.8',
    'Connection: keep-alive',
    'Content-Type: application/json',
    'Cookie: _ga=GA1.2.728367267.1665672802; locale=es; _gid=GA1.2.967319985.1673009696; _gat=1',
    'Origin: http://www.trackermasgps.com',
    'Referer: http://www.trackermasgps.com/',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36'
  ),
));



$response = curl_exec($curl);

$json=json_decode($response);
$cap= $json->hash;
echo $cap;

 curl_close($curl);
