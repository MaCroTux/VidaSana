<?php

$fileDebug = 'images/debug.log';

// Nombre del fichero temporal
$file = $_FILES['fileToUpload']['tmp_name'];

file_put_contents($fileDebug,"\n".'NEW SEARCH ---------------------------------------- '.date('d/m/Y H:i:s')."\n",FILE_APPEND);
file_put_contents($fileDebug,'Fichero temporal: '.$file."\n",FILE_APPEND);

$fileName = md5(file_get_contents($file));
file_put_contents($fileDebug,'MD5: '.$fileName."\n",FILE_APPEND);

// Generamos escalador de imagen
$procesTime = microtime(true);
procesImage($file, $fileName);
file_put_contents($fileDebug,'Imagen: <a href="'.'images/'.$fileName.'/'.$fileName.'.jpg'.'">'.$fileName.'</a>'."\n",FILE_APPEND);
file_put_contents($fileDebug,'Tiempo de proceso de imagen: '.(microtime(true)-$procesTime)."\n",FILE_APPEND);


$db = json_decode(file_get_contents('db.json'),1);
$dic = [];

foreach ($db AS $items){
	$dic[] = normaliza($items['numero']);
	//$dic[] = normaliza(str_replace('E','E-',$items['numero']));
	$dic[] = normaliza($items['nombre']);
}

$url = "https://api.ocr.space/parse/image";
file_put_contents($fileDebug,'URL: '.$url."\n",FILE_APPEND);

// Procesamos imagen y devolvemos el texto
$ocrTime = microtime(true);
$text = peticionOCR($url, $fileName);
file_put_contents($fileDebug,'Tiempo de petición de OCR: '.(microtime(true)-$ocrTime)."\n",FILE_APPEND);

// Puede que la operación Falle
$op = true;
if (empty($text)) {
    $op = false;
    file_put_contents($fileDebug,'OPERACION FALLIDA [KO]'."\n",FILE_APPEND);
}

// ----------- PERSER ------------------
//echo "MODO PRUEBAS, no comprueba etiquetas, DATOS INVENTADOS";
//$text = 'E-950 e951 e-950 e_950   E950  B*150';
file_put_contents('images/'.$fileName.'/'.$fileName.'.txt',normaliza($text));
$text = str_replace(["\n","\r"],['',''],$text);
$text = mb_ereg_replace('([a-z])([ ])([0-9])','\\1\\3',$text);
$text = normaliza($text);

$arrayResult2 = [];
$searchstring = $text;

foreach($dic as $string) {
    if(strpos($searchstring, $string) !== false) {
        $arrayResult2[] = $string;
    }
}

$array1 = explode(' ',$text);
$array2 = $dic;

//print_r($array1);
//print_r($array2);

$arrayResult1 = array_intersect($array1,$array2);
$arrayResult = array_unique(array_merge($arrayResult1,$arrayResult2));
// ----------- PERSER ------------------

file_put_contents('images/'.$fileName.'/'.$fileName.'.json',json_encode($arrayResult));
file_put_contents('images/'.$fileName.'/'.$fileName.'.ip',getRealIP());

file_put_contents($fileDebug,'Texto: '.$text."\n",FILE_APPEND);
file_put_contents($fileDebug,'Encontrado: '.json_encode($arrayResult)."\n",FILE_APPEND);
file_put_contents($fileDebug,'IP: '.getRealIP()."\n",FILE_APPEND);

/*
echo "<pre>";
print_r($array1);
print_r($array2);
print_r($arrayResult);
echo "</pre>";
*/

file_put_contents($fileDebug,'FIN SEARCH ----------------------------------------'."\n",FILE_APPEND);

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
        'base64image' => 'data:image/jpeg;base64,'.base64_encode(file_get_contents('/tmp/'.$fileName.'.jpg'))
    ]));
    $res = curl_exec($ch);
    $res = curl_exec($ch);
    $parse = json_decode($res,1)['ParsedResults'];
    $parse = array_shift($parse);
    $text = $parse['ParsedText'];
    curl_close($ch);
    return $text;
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
    copy($file,'images/'.$fileName.'/'.$fileName.'.jpg');
    $image = resize_image($file,2000,2000);
    imagejpeg($image, '/tmp/'.$fileName.'.jpg', 95);
    imagedestroy($image);
    return $fileName;
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

?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Vida Sana</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
      	<div class="page-header">
      	  <h2><a class="btn btn-default" href="/" role="button"><i class="glyphicon glyphicon-chevron-left"></i></a> Vida Sana <small>Resultado</small></h2>
      	</div>        
        <p class="lead">
          Consejo: Pulsa en el número para ver su ficha técnica. <a href="#" data-toggle="modal" data-target="#debug"><i class="glyphicon glyphicon-alert"></i></a>
        </p>
          <table class="table table-striped">
              <thead>
                  <tr>
                      <th>Número</th><th>Nombre</th><th>Toxicidad</th>
                  </tr>
              </thead>
              <tbody>
                <?php
                $color = 'success';
                $modal = [];
                if ($arrayResult){
                  foreach ($arrayResult as $value) {
                      foreach($db AS $item){
                          if (normaliza($value) == normaliza($item['numero'])) {
                              if ($item['toxico'] == 'EVITAR') $color = 'danger';
                              else if ($item['toxico'] == 'PRECAUCIÓN') $color = 'warning';
                              else if ($item['toxico'] == 'INOFENSIVO') $color = 'success';
                              echo "<tr class=".$color."><td>".'<a href="#" data-toggle="modal" data-target="#ficha'.$item['numero'].'">'.$item['numero']."</a></td><td>".$item['nombre']."</td><td>".'<span class="label label-'.$color.'">'.$item['toxico'].'</span>'."</td></tr>";

                              $modal[] = '<!-- Modal -->
                                <div class="modal fade" id="ficha'.$item['numero'].'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                  <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title" id="myModalLabel">Ficha técnica: '.$item['numero'].'</h4>
                                      </div>
                                      <div class="modal-body">
                                          <h3>'.$item['nombre'].'</h3>
                                          <br />
                                          <h4>Información</h4>
                                          <p>
                                            '.$item[0]['info'].'
                                          </p>
                                          <h4>Usos</h4>
                                          <p>
                                            '.$item[1]['usos'].'
                                          </p>
                                          <h4>Efectos secundarios</h4>
                                          <p>
                                            '.$item[2]['efectos_secundarios'].'
                                          </p>
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                      </div>
                                    </div>
                                  </div>
                                </div>';
                          }
                      }
                  }
                }else{
                    if ($op) {echo '<tr class='.$color.'><td colspan="3">No se encontraron resultados.</td></tr>';}
                    else {echo '<tr><td colspan="3">Hemos tenido problemas para leer la imagen, intentalo de nuevo con una imagen más nítida.</td></tr>';}
                }?>
              </tbody>
          </table>
          <?php echo implode(' ',$modal); ?>
        </div>
      </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="debug" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">Depuración</h4>
          </div>
          <div class="modal-body">
            <?php echo $text; ?>
            <hr/>
            <?php echo print_r($arrayResult, true); ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  </body>
</html>
