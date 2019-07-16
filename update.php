<?php

if (!file_exists('db.json')) {

	$urlListado = "http://www.aditivos-alimentarios.com/p/listado-de-aditivos.html";
	echo "Consultando [$urlListado] \n";
	$res = realizarPeticionGet($urlListado);

	$doc = new DOMDocument('1.0', 'utf8');
	@$doc->loadHTML($res);
	$res = $doc->getElementsByTagName('table');

	foreach ($res as $key => $value) {
		$res2 = $value->getElementsByTagName('tr');
		foreach ($res2 as $key2 => $value2) {
			$res3 = $value2->getElementsByTagName('td');
			$col = [];
			$a3 = $res3[0]->getElementsByTagName('a'); // Objeto a dentro del elemento
			$col['url'] = $a3[0]->getAttribute('href'); // URL de la ficha
			$col['numero'] = $res3[0]->nodeValue;
			$col['nombre'] = $res3[1]->nodeValue;
			$col['toxico'] = $res3[2]->nodeValue;
			$cols[] = $col;
		}
	}

	echo "Listado procesado consultado ficha de aditivos \n";
	foreach ($cols as $key => $value) {
		$resFicha = realizarPeticionGet($value['url']);
		$doc = new DOMDocument('1.0', 'utf8');
		@$doc->loadHTML($resFicha);
		$blockquotes = $doc->getElementsByTagName('blockquote');
		$cols[$key][]['info'] = $blockquotes[0]->nodeValue;
		$cols[$key][]['usos'] = $blockquotes[1]->nodeValue;
		$cols[$key][]['efectos_secundarios'] = $blockquotes[2]->nodeValue;
		echo "[".$key.'/'.count($cols)."]".' Creando ficha de '.$value['numero'].': '.$value['nombre']."\n";
	}

	echo ' -> 100% '."\n";

	file_put_contents('db.json', json_encode($cols));

	echo "Creado fichero: db.json\n";
	echo "DB lista !\n";

}else{
	echo "DB ya existe lista !\n";
}


function realizarPeticionGet($url){
	// Crear un nuevo recurso cURL
	$ch = curl_init();
	// Establecer URL y otras opciones apropiadas
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	// Capturar la URL y devuelve el código HTML
	$res = curl_exec($ch);

	// Cerrar el recurso cURL y liberar recursos del sistema
	curl_close($ch);

	return $res;
}
