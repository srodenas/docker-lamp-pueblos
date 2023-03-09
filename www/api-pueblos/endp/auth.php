<?php
require_once '../modelos/user.class.php';
require_once '../modelos/auth.class.php';
require_once '../respuestas/response.php';


/**
 ***********SÓLO PARA EL LOGEO************
 * 
 * EndPoint que tiene definido el método POST y lo que 
 * recibe es un username y un password. Tienen que coincidir
 * en la bbdd y si coindice, genera el token para devolverlo al 
 * usuario que deberá agregarlo a su encabezado para posteriormente
 * usarlo en los demás endpoint con normalidad.
 * 
 * Para probarlo por primera vez, voy a crear un usuario y utilizaré la página
 * https://emn178.github.io/online-tools/sha256  para que a partir de una password
 * me genere una contraseña encriptada. Utilizo los datos username = santi y password=santi
 * 
 * Generaremos una llamada a nuestro endpoint auth, donde con el método post le pasaremos
 * nuestro usuario y contraseña que mandamos desde un login. Probamos con username = "santi" y
 * password="santi". Lo que nos deberá generar es un token y lo devolerá para que lo siguamos utilizando
 * en el resto de endpoint. Hay que tener en cuenta, que nuestro endpoint, a partir del password, utilizará
 * un encoder basado en sha256 para encriptar la password y comprobar que efectivamente es la que tiene
 * en la tabla.
 * 
 * Con la password codificada, tenemos que calcular su token, para ello llamamos al método signInt de la clase
 * auth.class. ¿Cómo se genera el token y cual es su flujo?
 */

 /*
 PODRÍA HACER QUE EL TOKEN DURARA POR UN TIEMPO Y QUE ÉSTE SE REFRESCARA HACIENDO UN NUEVO ENDPOINT.
 TENDRÍA QUE AÑADIR EL TIEMPO DE FINALIZACIÓN, JUNTO CON EL TIEMPO DE CREACIÓN DE TOKEN QUE YA UTILIZO.
 NO LO VOY A HACER, PORQUE NO ME DA LA GANA....
 */
$auth = new Authentication();  //crea un objeto con la tabla, la key privada.

//dependiendo del método request, tiene que ser un POST.
switch ($_SERVER['REQUEST_METHOD']) {
	case 'POST':
		$user = json_decode(file_get_contents('php://input'), true);

		//PARANOIA PARA DEPURAR CUANDO LA COSA VA MALLLL...
//		$auth->insertarLog("Entra en el POST de autenticacion"); exit;

		$token = $auth->signIn($user);  //ya tenemos el token.
/*
Falta devolver todos los datos de ese usuario.
*/
		$id_user = $auth->getIdUser();
		//echo "su id es " . $id_user; exit;
		$user = $auth->getUser($id_user);
		$nombre = $user['nombre'];
		$imagen = $user['imagen'];
		$email = $user['email'];
		$url_raiz_img="http://".$_SERVER['HTTP_HOST']."/api-pueblos/public/img";

		$imagen = $url_raiz_img."/".$imagen;
		

		//echo $id_user." ".$nombre." ".$imagen;
		//exit;

		$response = array(
			'result' => 'ok',
			'token' => $token,
			'id' => $id_user,
			'nombre' => $nombre,
			'email' => $email,
			'imagen' => $imagen
		);

		// Se devuelve el token correctamente.
		Response::result(201, $response);

		break;
}