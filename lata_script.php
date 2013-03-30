<?php 

define("ROOT_PATH", dirname(__FILE__) . '/');

for ($i=1; $i <= 99999; $i++) { 
    echo "try number $i\n";

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        //CURLOPT_HEADER => true, 
        CURLOPT_HTTPHEADER => array('Content-Type: application/xml'),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CAINFO => ROOT_PATH . "/certs/apitest.crt",
        CURLOPT_USERPWD => "2600hz:CallMeMaybe!",
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_URL => "https://api.test.inetwork.com/v1.0/accounts/2007366/availableNumbers?&lata=" . $i,
        CURLOPT_POSTFIELDS => null
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $xml = simplexml_load_string($response);

    if($xml->ResultCount == 0) {
        sleep(2);
        continue;
    } else {
        file_put_contents("lata_list.txt", $i . "\n", FILE_APPEND);
    }

    sleep(2);
}

?>