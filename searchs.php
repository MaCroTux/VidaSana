<?php

include 'function.php';

$dirs = array();
if ($handle = opendir('images/')) {
  while (false !== ($file = readdir($handle))) {
    if ($file != "." && $file != ".." && $file != "debug.log") {
      $dirs[filemtime('images/'.$file).'_'.$file] = 'images/'.$file;
    }
  }
  closedir($handle);
}

// sort
ksort($dirs);

$searchs = [];
foreach ($dirs as $key => $dir){
  $nane = glob($dir.'/*')[0];
  $nane = explode('.',$nane)[0];

  if (!file_exists($dir.'/'.basename($nane).'_th.jpg') && file_exists($dir.'/'.basename($nane).'.jpg')) {
    $image_th = resize_image($dir.'/'.basename($nane).'.jpg',200,200);
    imagejpeg($image_th, $dir.'/'.basename($nane).'_th.jpg', 80);
    imagedestroy($image_th);
  }

  //echo basename($nane)." [".$dir."]\n<br/>";
  //echo $dir.'/'.basename($nane).'.json'."\n<br/";
  //echo $dir.'.json'."\n";
  if (file_exists($dir.'/'.basename($nane).'.json')) $datos = file_get_contents($dir.'/'.basename($nane).'.json');
  if (file_exists($dir.'/'.basename($nane).'_th.jpg')) $image = $dir.'/'.basename($nane).'_th.jpg';
  if (file_exists($dir.'/'.basename($nane).'.txt')) $resut = file_get_contents($dir.'/'.basename($nane).'.txt');
  if (file_exists($dir.'/'.basename($nane).'.ip')) $ip = file_get_contents($dir.'/'.basename($nane).'.ip');

  $searchs[] = [
    'date' => explode('_',$key)[0],
    'name' => basename($dir),
    'datos' => json_decode($datos,1),
    'image' => $image,
    'resut' => $resut,
    'ip' => $ip,
  ];
}

$searchs = array_reverse($searchs);

?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>¿Sabes que comes?</title>

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
        	  <h2><a class="btn btn-default" href="/" role="button"><i class="glyphicon glyphicon-chevron-left"></i></a> ¿Sabes que comes? <small>Búsquedas</small></h2>
        	</div>
          <?php foreach ($searchs AS $search) { ?>
          <div class="media">
            <div class="media-left">
              <a href="#">
                <img class="media-object" src="<?=$search['image']?>" alt="..." style="width:100px">
              </a>
            </div>
            <div class="media-body">
              <?php if ($search['datos']) { ?>
                <p><strong>Encontrado:</strong> <br /><?=implode(', ',$search['datos'])?></p>
              <?php }else{ ?>
                <p>No se encontrarón resultados.</p>
              <?php } ?>
              <p><?=date('d/m/Y H:i:s',$search['date'])?></p>
              <a class="btn btn-primary btn-xs" href="result.php?request=<?=$search['name']?>&search=1">Más info</a>
            </div>
          </div>
            <hr />
          <?php } ?>
          <br />
        </div>
      </div>
    </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  </body>
</html>
