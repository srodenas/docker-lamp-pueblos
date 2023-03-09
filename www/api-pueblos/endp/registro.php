<?php
require_once '../respuestas/response.php';
require_once '../modelos/user.class.php';

/**
 * endpoint sólo para el REGISTRO DE  de las cuentas de usuario.
 * NO ES NECESARIO TOKEN
 * Los parámetros se pasan por body
 * 
 * De momento, un usuario que se registra, debe después loguearse.
 * 
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();

	//obtenemos los parámetros que son el email, password, nombre, imagen, etc.
    $params = json_decode(file_get_contents('php://input'), true);

	if(!isset($params)){
		$response = array(
				'result' => 'error',
				'details' => 'Error en la solicitud de creación usuario'
		);

		Response::result(400, $response);
        exit;
	}


	//se obtiene un nuevo id
	$insert_id = $user->insert($params);

	$response = array(
			'result' => 'ok',
			'insert_id' => $insert_id
	);

	Response::result(201, $response);

}
else{  //Intentamos registrarnos sin el post
    $response = array(
        'result' => 'error'
    );

    Response::result(404, $response);

}
?>