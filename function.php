<?php

require_once 'HTTP/Request2.php';
require_once 'Net/URL2.php';

function peticionOCR($url, $fileName){
    $ch = curl_init();
    // Establecer URL y otras opciones apropiadas
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query([
        'apikey' => 'eb737d56a988957',
        'base64image' => 'data:image/jpeg;base64,'.base64_encode(file_get_contents('images/'.$fileName.'/'.$fileName.'_bw.jpg'))
    ]));
    $res = curl_exec($ch);
    $res = curl_exec($ch);
    $parse = json_decode($res,1)['ParsedResults'];
    $parse = array_shift($parse);
    $text = $parse['ParsedText'];
    curl_close($ch);
    return $text;
}

function peticionOCR2($url, $fileName){
    $request = new Http_Request2($url);
    $url = $request->getUrl();

    // Request headers
    $headers = array(
        'Content-Type' => 'application/json',
        'Ocp-Apim-Subscription-Key' => '1f99ae2aebdc4e96a62211b83c77279f',
    );

    $request->setHeader($headers);

    // Request parameters
    $parameters = array(
        'language' => 'es',
        'detectOrientation ' => 'true'
    );

    $url->setQueryVariables($parameters);

    $request->setMethod(HTTP_Request2::METHOD_POST);

    // Request body
    $DAS = '/';
    $request->setBody('{"url":"http://feriacloud.com:9999/images/'.$fileName.$DAS.$fileName.'_bw.jpg"}');
    error_log('{"url":"http://feriacloud.com:9999/images/'.$fileName.$DAS.$fileName.'_bw.jpg"}');
    try
    {
        $response = $request->send();
        error_log($response->getBody());
        $datas = json_decode($response->getBody(),1)['regions'];
        $texto = '';
        foreach($datas AS $data){
            foreach($data['lines'] AS $lines){
                foreach($lines['words'] AS $words){
                    $texto .= $words['text'].' ';
                }
            }
        }
        return $texto;
    }
    catch (HttpException $ex)
    {
        return '';
    }
}

function normaliza ($cadena){
    $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
    $modificadas = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
    $cadena = utf8_decode($cadena);
    $cadena = strtr($cadena, utf8_decode($originales), $modificadas);
    $cadena = strtolower($cadena);
    return preg_replace('/[^a-zA-Z0-9 ]/i','',utf8_encode($cadena));
}




function getRealIP()
{

    if (isset($_SERVER["HTTP_CLIENT_IP"]))
    {
        return $_SERVER["HTTP_CLIENT_IP"];
    }
    elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
    {
        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    elseif (isset($_SERVER["HTTP_X_FORWARDED"]))
    {
        return $_SERVER["HTTP_X_FORWARDED"];
    }
    elseif (isset($_SERVER["HTTP_FORWARDED_FOR"]))
    {
        return $_SERVER["HTTP_FORWARDED_FOR"];
    }
    elseif (isset($_SERVER["HTTP_FORWARDED"]))
    {
        return $_SERVER["HTTP_FORWARDED"];
    }
    else
    {
        return $_SERVER["REMOTE_ADDR"];
    }

}

function procesImage($file, $srcFile){
    $fileName = $srcFile;
    mkdir('images/'.$fileName);
    $image = resize_image($file,2000,2000);
    imagejpeg($image, '/tmp/'.$fileName.'.jpg', 95);
    copy('/tmp/'.$fileName.'.jpg','images/'.$fileName.'/'.$fileName.'.jpg');
    imagedestroy($image);

    $image = resize_image($file,2400,2400);
    imagefilter($image, IMG_FILTER_GRAYSCALE);
    imagefilter($image, IMG_FILTER_CONTRAST, -10);
    imagefilter($image, IMG_FILTER_BRIGHTNESS, -50);
    imagefilter($image, IMG_FILTER_SMOOTH, -255);
    imagejpeg($image, '/tmp/'.$fileName.'.jpg', 80);
    copy('/tmp/'.$fileName.'.jpg','images/'.$fileName.'/'.$fileName.'_bw.jpg');
    imagedestroy($image);

    $image_th = resize_image('images/'.$fileName.'/'.$fileName.'.jpg',200,200);
    imagejpeg($image_th, 'images/'.$fileName.'/'.$fileName.'_th.jpg', 80);
    imagedestroy($image_th);

    return 'images/'.$fileName.'/'.$fileName.'_bw.jpg';
}

function resize_image($file, $w, $h, $crop=FALSE) {
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    $src = imagecreatefromjpeg($file);
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}