<?php

include 'function.php';

$fileDebug = 'images/debug.log';

// Nombre del fichero temporal
$file = $_FILES['fileToUpload']['tmp_name'];

$finfo = finfo_open(FILEINFO_MIME_TYPE); // devuelve el tipo mime de su extensión
$type = finfo_file($finfo, $file);
finfo_close($finfo);

file_put_contents($fileDebug,"\n".'NEW SEARCH ---------------------------------------- '.date('d/m/Y H:i:s')."\n",FILE_APPEND);

if ($type == 'image/jpeg') {
    file_put_contents($fileDebug,'Fichero temporal: '.$file."\n",FILE_APPEND);

    $fileName = md5(file_get_contents($file));
    file_put_contents($fileDebug,'MD5: '.$fileName."\n",FILE_APPEND);

    // Generamos escalador de imagen
    $procesTime = microtime(true);
    procesImage($file, $fileName);
    file_put_contents($fileDebug,'Imagen: <a href="'.'images/'.$fileName.'/'.$fileName.'.jpg'.'">'.$fileName.'</a>'."\n",FILE_APPEND);
    file_put_contents($fileDebug,'Tiempo de proceso de imagen: '.(microtime(true)-$procesTime)."\n",FILE_APPEND);


    $db_ = json_decode(file_get_contents('db.json'),1);
    $dbi = json_decode(file_get_contents('dbi.json'),1);
    $db = array_merge($db_, $dbi);

    $dic = [];

    foreach ($db AS $items){
        $dic[] = normaliza($items['numero']);
        //$dic[] = normaliza(str_replace('E','E-',$items['numero']));
        $dic2[] = normaliza($items['nombre']);
    }

    $url = "https://api.ocr.space/parse/image";
    $url2 = 'https://westus.api.cognitive.microsoft.com/vision/v1.0/ocr';

    // Procesamos imagen y devolvemos el texto
    file_put_contents($fileDebug,'URL2: '.$url2."\n",FILE_APPEND);
    $ocrTime2 = microtime(true);
    $text = peticionOCR2($url2, $fileName);
    file_put_contents($fileDebug,'Tiempo de petición de OCR2: '.(microtime(true)-$ocrTime2)."\n",FILE_APPEND);

    if (empty($text)){
        // Procesamos imagen y devolvemos el texto
        file_put_contents($fileDebug,'URL: '.$url."\n",FILE_APPEND);
        $ocrTime = microtime(true);
        $text = peticionOCR($url, $fileName);
        file_put_contents($fileDebug,'Tiempo de petición de OCR: '.(microtime(true)-$ocrTime)."\n",FILE_APPEND);
    }

    // Puede que la operación Falle
    $op = true;
    if (empty($text)) {
        $op = false;
        file_put_contents($fileDebug,'OPERACION FALLIDA [KO]'."\n",FILE_APPEND);
    }

    // ----------- PERSER ------------------
    file_put_contents('images/'.$fileName.'/'.$fileName.'.txt',normaliza($text));
    $text = str_replace(["\n","\r"],['',''],$text);
    $text = mb_ereg_replace('([a-z])([ ])([0-9])','\\1\\3',$text);
    $text = normaliza($text);

    $arrayResult2 = [];
    $searchstring = $text;

    foreach($dic2 as $string) {
        if(strpos($searchstring, ' '.$string) !== false) {
            $arrayResult2[] = $string;
        }
    }

    $array1 = explode(' ',$text);
    $array2 = $dic;

    $arrayResult1 = array_intersect($array1,$array2);
    $arrayResult = array_unique(array_merge($arrayResult1,$arrayResult2));
    // ----------- PERSER ------------------

    file_put_contents('images/'.$fileName.'/'.$fileName.'.json',json_encode($arrayResult));
    file_put_contents('images/'.$fileName.'/'.$fileName.'.ip',getRealIP());

    file_put_contents($fileDebug,'Texto: '.$text."\n",FILE_APPEND);
    file_put_contents($fileDebug,'Encontrado: '.json_encode($arrayResult)."\n",FILE_APPEND);
    file_put_contents($fileDebug,'IP: '.getRealIP()."\n",FILE_APPEND);

    file_put_contents($fileDebug,'Página de resultado: <a href="result.php?request='.$fileName.'">'.$fileName.'</a>'."\n",FILE_APPEND);
    header('Location: result.php?request='.$fileName);
}else {
    file_put_contents($fileDebug,'Imagen no JPG'."\n",FILE_APPEND);
    header('Location: result.php?request=NOJPG');
}
file_put_contents($fileDebug,'FIN SEARCH ----------------------------------------'."\n",FILE_APPEND);

