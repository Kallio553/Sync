<?php

//////////////////////////////////////////////////
/// curl init
$curl = curl_init();
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

///////////////////////////////////////////////////////////////////
/// login
$request = '<?xml version="1.0" encoding="UTF-8" ?>
			<Params>
				<ApiKey></ApiKey>
			</Params>';
			
curl_setopt($curl, CURLOPT_URL, "https://api.unas.eu/shop/login");
curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
$response = curl_exec($curl);

$xml = simplexml_load_string($response);
$token = (string)$xml->Token;

///////////////////////////////////////////////////////////////////
/// getProduct
$headers = array();
$headers[] = "Authorization: Bearer ".$token;

$request = '<?xml version="1.0" encoding="UTF-8" ?>
            <Params>
                <ContentType>minimal</ContentType>
            </Params>';
			
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_URL, "https://api.unas.eu/shop/getProduct");
curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
$unasProducts = curl_exec($curl);
curl_close($curl);
//echo $unasProducts;

$unasXml = simplexml_load_string($unasProducts);
//$unasXml = new SimpleXMLElement($unasProducts);

$unasProducts = array();

$unasProductsCount = count($unasXml->Product);
for ($i = 0; $i < $unasProductsCount; $i++) {

    $unasProducts["UnasCikkszam" . $i] = (String)$unasXml->Product[$i]->Sku;
}

///////////////////////////////////////////////////////////////////
/// Debra
$url = 'https://debranet.com/xml/stock.php?codekey='; // &from=0&to=5

$ch = curl_init();
$timeout = 5;
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
$debraProducts = curl_exec($ch);
curl_close($ch);

$debraXml = simplexml_load_string($debraProducts);
//$debraXml = new SimpleXMLElement($debraProducts);

$xmlBody = '<?xml version="1.0" encoding="UTF-8" ?>
';

$xmlBody .= "<Products>";

$debraProductsCount = count($debraXml->product_stock);
for ($i = 0; $i < $debraProductsCount; $i++) {

    $debraProducts = array();

    $debraProducts["Cikkszam"] = (String)$debraXml->product_stock[$i]->product_code;
    $debraProducts["Keszlet"] = (String)$debraXml->product_stock[$i]->stock;

    if (in_array($debraProducts["Cikkszam"], $unasProducts)) {

        $xmlBody .= '
    <Product>
        <Action>modify</Action>
        <Sku>' . $debraProducts["Cikkszam"] . '</Sku>
        <Stocks>
            <Stock>
                <WarehouseId>5497825</WarehouseId>
                <Qty>' . $debraProducts["Keszlet"] . '</Qty>
            </Stock>
        </Stocks>
    </Product>';
    }

    //print_r($debraProducts);
}

$xmlBody .= "
</Products>
";

//echo $xmlBody;

///////////////////////////////////////////////////////////////////
/// setStock
$headers = array();
$headers[] = "Authorization: Bearer ".$token;

$request = $xmlBody;

$curl = curl_init();
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_URL, "https://api.unas.eu/shop/setStock");
curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
$response = curl_exec($curl);
curl_close($curl);
//echo $response;

///////////////////////////////////////////////////////////////////
/// getStock
/*
$headers = array();
$headers[] = "Authorization: Bearer ".$token;

$request = '<?xml version="1.0" encoding="UTF-8" ?>
			<Params>
    			<Sku>teszt001</Sku>
			</Params>';

$curl = curl_init();
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_URL, "https://api.unas.eu/shop/getStock");
curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
$response = curl_exec($curl);
curl_close($curl);
echo $response;
*/

?>
