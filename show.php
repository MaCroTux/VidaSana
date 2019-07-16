<?php

include 'function.php';

$arrayResult = explode(',',$_GET['items']);

$db_ = json_decode(file_get_contents('db.json'),1);
$dbi = json_decode(file_get_contents('dbi.json'),1);
$db = array_merge($db_, $dbi);

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
      	  <h2><a class="btn btn-default" href="javascript:window.history.back();" role="button"><i class="glyphicon glyphicon-chevron-left"></i></a> ¿Sabes que comes? <small>Información</small></h2>
      	</div>
        <p class="lead">
          Consejo: Pulsa en el número para ver su ficha técnica.
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
                          if (normaliza($value) == normaliza($item['numero']) || normaliza($value) == normaliza($item['nombre'])) {
                              if ($item['toxico'] == 'EVITAR') $color = 'danger';
                              else if ($item['toxico'] == 'PRECAUCIÓN') $color = 'warning';
                              else if ($item['toxico'] == 'INOFENSIVO') $color = 'success';
                              else if ($item['toxico'] == 'INTOLERANCIA') $color = 'info';
                              echo "<tr class=".$color.">".
                                      "<td>".
                                        '<a href="#" data-toggle="modal" data-target="#ficha'.$item['numero'].'" style="text-transform:uppercase;">'.$item['numero']."</a>".
                                      "</td>".
                                      "<td>".$item['nombre']."</td>".
                                      "<td>".
                                        '<a href="#" data-toggle="modal" data-target="#ficha'.$item['numero'].'" style="text-transform:uppercase;">'.
                                          '<span class="label label-'.$color.'">'.$item['toxico'].'</span>'.
                                        "</a>".
                                      "</td>".
                                    "</tr>";

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
                    echo '<tr><td colspan="3">Hemos tenido problemas para leer la imagen, intentalo de nuevo con una imagen más nítida.</td></tr>';
                }?>
              </tbody>
          </table>
          <?php echo implode(' ',$modal); ?>
        </div>
      </div>
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  </body>
</html>
