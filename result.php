<?php

include 'function.php';

$fileName = $_GET['request'];

$dir = 'images/'.$fileName;

$db_ = json_decode(file_get_contents('db.json'),1);
$dbi = json_decode(file_get_contents('dbi.json'),1);
$db = array_merge($db_, $dbi);

$items = [];
foreach ($db AS $item){
    if ($item['toxico'] == 'EVITAR') $color = 'danger';
    else if ($item['toxico'] == 'PRECAUCIÓN') $color = 'warning';
    else if ($item['toxico'] == 'INOFENSIVO') $color = 'success';
    else if ($item['toxico'] == 'INTOLERANCIA') $color = 'info';

    $items[normaliza($item['numero'])] = $color;
    $items[normaliza($item['nombre'])] = $color;
}

if (!file_exists($dir.'/'.basename($dir).'.txt')) {
    $nane = glob($dir.'/*')[0];
    $nane = explode('.',$nane)[0];
}else{
    $nane = $dir;
}

$image = '';
$datos = [];
$resut = '';
$ip = '';

if (file_exists($dir.'/'.basename($nane).'.json')) $datos = json_decode(file_get_contents($dir.'/'.basename($nane).'.json'),1);
if (file_exists($dir.'/'.basename($nane).'.jpg')) $image = $dir.'/'.basename($nane).'.jpg';
if (file_exists($dir.'/'.basename($nane).'.txt')) $resut = file_get_contents($dir.'/'.basename($nane).'.txt');
if (file_exists($dir.'/'.basename($nane).'.ip')) $ip = file_get_contents($dir.'/'.basename($nane).'.ip');

$searchs = [
    'datos' => $datos,
    'image' => $image,
    'result' => $resut,
    'ip' => $ip,
];

$aReplace = [];
foreach ($datos as $dato) {
    $aReplace[] = '<span class="label label-'.$items[$dato].'">'.$dato.'</span>';
    //$aReplace[] = "<strong>".$dato."</strong>";
}

$find = str_replace($datos,$aReplace,$searchs['result']);

$aditivos = implode(',',$datos);

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
      <style type="text/css">
          .label {
              text-decoration: underline;
              text-transform: uppercase;
          }
          img {
              width: 50%;
          }
      </style>
  </head>
  <body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
      	<div class="page-header">
      	  <h2>
              <a class="btn btn-default" href="<?php echo $_GET['search']?'searchs.php':'/';?>" role="button"><i class="glyphicon glyphicon-chevron-left"></i></a> ¿Sabes que comes? <small>Resultado</small>
          </h2>
      	</div>
        <a href="<?=$image?>">
          <div class="text-center" style="background: url('<?=$image?>') no-repeat scroll center top #000; background-size: cover;height: 250px;">
            <!--<img src="<?/*=$image*/?>" style="position: "/>-->
          </div>
        </a>
          <br/>
          <?php if ($fileName !== 'NOJPG') { ?>
              <?php if (!empty($find)) { ?>
                <?php if ($datos) { ?>
                    <h4>Hemos encontrado ...</h4>
                    <p class="lead"><?=$find;?></p>
                    <a href="show.php?items=<?=$aditivos?>" class="btn btn-md btn-primary btn-block">Ver significado</a>
                <?php }else{ ?>
                  <h4>Texto encontrado</h4>
                  <p class="lead"><?=$find;?></p>
                  <p class="lead text-success">No se encontraron aditivos alimentarios.</p>
                <?php } ?>
              <?php }else{ ?>
              <h4>Lo sentimos ...</h4>
              <p class="lead">No hemos encontrado texto en la imagen, intentalo de nuevo pero con una imagen más nítida.</p>
              <?php } ?>
          <?php }else{ ?>
              <p>Formato de imagen incorrecto!</p>
          <?php } ?>
          <br /><br /><br />
        </div>
      </div>
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  </body>
</html>
