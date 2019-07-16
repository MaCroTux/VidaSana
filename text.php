<?php
// This sample uses the Apache HTTP client from HTTP Components (http://hc.apache.org/httpcomponents-client-ga/)
require_once 'HTTP/Request2.php';
require_once 'Net/URL2.php';

$request = new Http_Request2('https://westus.api.cognitive.microsoft.com/vision/v1.0/ocr');
$url = $request->getUrl();

$headers = array(
    // Request headers
    'Content-Type' => 'application/json',
    'Ocp-Apim-Subscription-Key' => '1f99ae2aebdc4e96a62211b83c77279f',
);

$request->setHeader($headers);

$parameters = array(
    // Request parameters
    'language' => 'es',
    'detectOrientation ' => 'true'
);

$url->setQueryVariables($parameters);

$request->setMethod(HTTP_Request2::METHOD_POST);

// Request body
$request->setBody('{"url":"http:\/\/feriacloud.com:9999/images/a6002d709cdfe1a97154f86df9a35305/a6002d709cdfe1a97154f86df9a35305.jpg"}');

try
{
    $response = $request->send();
    $datas = json_decode($response->getBody(),1)['regions'];
    $texto = '';
    foreach($datas AS $data){
//	print_r($data['lines']);
	foreach($data['lines'] AS $lines){
//		print_r($lines['words']);
		foreach($lines['words'] AS $words){
			$texto .= $words['text'].' ';
		}
	}
    }
    echo $texto;
}
catch (HttpException $ex)
{
    echo $ex;
}

?>
