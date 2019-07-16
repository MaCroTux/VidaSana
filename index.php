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
        	  <h2>¿Sabes que comes? <small>Haz una foto de la etiqueta de los ingredientes</small></h2>
        	</div>
          Número de busquedas totales: <strong><?php echo count(glob('images/*')) ?></strong>
          <br /><br />
          <form action="process.php" method="post" enctype="multipart/form-data">
            <p id="load" class="text-center hidden">
              <img src="https://www.gdlplazaexpo.com/images/ajax-loader.gif" />
            </p>
            <span class="btn btn-default btn-file" style="width:100%;">
              <h1><i class="glyphicon glyphicon-picture"></i></h1>
              Haz una foto de la etiqueta
             </span>
             <input class="hide" id="upload" type="file" name="fileToUpload" id="fileToUpload">
             <input class="hide" id="submit" type="submit" value="Upload Image" name="submit">
          </form>
          <br />
          <br />
          <a href="dicionario.php" type="button" class="btn btn-primary btn-block">Ir al listado</a>
          <a href="searchs.php" type="button" class="btn btn-info btn-block">Ver últimas busquedas</a>
        </div>
      </div>
    </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script>
    $('#content').html('<div><img src="images/loading.gif"/></div>');
      $('#upload').change(function(){
        $('#submit').click();
        $('.btn-file').hide();
        $('#load').removeClass('hidden');
      });

      $('.btn-file').click(function(){
        $('#upload').click();
        $('#content').fadeIn(1000).html(data);
        //console.log('click');
      });
    </script>
  </body>
</html>
