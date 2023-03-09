<?php
require_once '../respuestas/response.php';
require_once '../modelos/user.class.php';
require_once '../modelos/auth.class.php';

/*
$url_completa="http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
//echo $url_completa;

$url_raiz="http://".$_SERVER['HTTP_HOST']."/api-pueblos/public/img";

echo $url_raiz;
exit;
*/

/*
ESTE ENDPOINT, SERÁ LLAMADO SIEMPRE QUE QUERAMOS HACER UN
****LISTADO (GET)
****MODIFICAR-USUARIO (PUT)
*****ELIMINAR-USUARIO(DELETE)

Para comprobar si tiene permisos y está autorizado.

 * endpoint para cualquier petición de usuarios.
 * Tendremos dos endpoint.
 * 1.- El de la autenticación
 * 2.- Para la petición de datos de usuarios.
 */

 /**
  * SE SUPONE QUE AL ACCEDER A ESTE ENDPOINT, YA NOS HEMOS LOGEADO.
  */
$auth = new Authentication();  //***** CAPA DE AUTHENTICATION  *****/
//Compara que el token sea el correcto y que la decodificación con clave privada
//sea la correcta.
$auth->verify();  /* VERIFICAMOS LA AUTENTICACIÓN.  */
//hasta aquí, el token está perfectamente verificada.
$user = new User();  //creamos un objeto de la clase User.

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$params = $_GET; //leemos los parámetros por URL
		/*
		Dentro de la clase user, están todas las validaciones. El método get recibe
		los parámetros y devuelve un array con los datos en forma de array.
		*/

		$usuarios = $user->get($params); 	//Recuperamos todos los usuarios.
		//Arma la respuesta con resultado ok y los usuarios en el array.  Luego le pasamos
		//a resul nuestro response.
		//ME FALTA, MANDAR COMO IMAGEN, LA URL.
		$url_raiz_img="http://".$_SERVER['HTTP_HOST']."/api-pueblos/public/img";

		for($i=0; $i< count($usuarios); $i++){
			if (!empty($usuarios[$i]['imagen']))
				$usuarios[$i]['imagen'] = $url_raiz_img ."/". $usuarios[$i]['imagen'];
		}
		/*foreach ($usuarios as $usuario) {
			if (!empty($usuario['imagen'])){
				//$imagen = $usuario['imagen'];
				$usuario['imagen'] = $url_raiz_img."/".$usuario['imagen'];
				//echo $usuario['imagen'];
			}
			
			
		}
		*/
		//exit;
		
		$response = array(
			'result' => 'ok',
			'usuarios' => $usuarios
		);

		Response::result(200, $response);

		break;


		/*
		Los parámetros en caso de inserción, no se define en la URL, sino en el body
		con los datos de un JSON. Por ejemplo, 
		{
			"nombres"= "Juan",
			"disponible"=1
		}

		Al momento de enviarlo, vemos que es un POST y recuperamos los parámetro a través de
		la función json_decode(file_get_contents()). Transforma en un array asociativo
		Si hizo un post pero no recibe ningún dato en JSON, devuelve un error 400. En caso de que
		si llegaran bien los parámetros, pasamos a ejecutar nuestro insert con los parámetros.

		Nos devuelve el id del registro insertado. Después armamos la respuesta. 201 de create.
		*/
	case 'POST':
		/*
		EN PRINCIPIO, NO LO USAREMOS..... SÓLO LO HARÍA EL ADMINISTRADOR.

		Los parámetros del body, los recupera a partir de la función file_get_contents('php://input')
		Decodificamos ese json a partir de json_decode y lo transforma a un array asociativo dentro de params.
		*/
		$params = json_decode(file_get_contents('php://input'), true);  //supongo que se envía por @body

		/*
		Comprueba si existen parámetros. Si no existe, devuelve la respuesta de error 400.
		*/
		if(!isset($params)){
			$response = array(
				'result' => 'error',
				'details' => 'Error en la solicitud'
			);

			Response::result(400, $response);
			exit;
		}


		//aquí insertamos el nuevo usuario a partir de nuestro objeto user.
		$insert_id = $user->insert($params);

		$response = array(
			'result' => 'ok',
			'insert_id' => $insert_id
		);

		Response::result(201, $response);


		break;

/**
 * Los campos que queremos editar, también van el el body, pero el id va en la URL
 * Decodifica el json recibido como en el post y comprobamos si tenemos un id. En caso
 * contrario, hay un error porque no sabemos qué registro hay que actualizar.
 * 
 * Llamamos al método update con el id y los parametros a modificar.
 * Al finalizar, se arma la respuesta ok.
 */

	case 'PUT':
		//volvemos a pasar nuestro json a un arry asociativo
		$params = json_decode(file_get_contents('php://input'), true);

		/*
		Es obligatorio que al editar un usuario, exista el parámetro id y valor.
		*/
		if(!isset($params) || !isset($_GET['id']) || empty($_GET['id'])){
			$response = array(
				'result' => 'error',
				'details' => 'Error en la solicitud de actualización'
			);

			Response::result(400, $response);
			exit;
		}

		//actualizamos por id.
		$user->update($_GET['id'], $params);
		/**
		 * toca actualizar el token del usuario, ya que modificó obligatoriamente
		 * el campo email.
		 */
		$auth->modifyToken($_GET['id'], $params["email"]);
		$response = array(
			'result' => 'ok'
		);

		Response::result(200, $response);
		
		break;

/**
 * Comprueba que le hemos pasado por url el id y que no esté vacío. En cuyo caso, armamos la 
 * respuesta de error y nos salimos. Llamamos al método delete pasándole el id y éste
 * eliminará dicho registro. Por último arma la respuesta ok.
 */

	case 'DELETE':

		/*
		Es obligatorio el id por GET
		*/
		if(!isset($_GET['id']) || empty($_GET['id'])){
			$response = array(
				'result' => 'error',
				'details' => 'Error en la solicitud'
			);

			Response::result(400, $response);
			exit;
		}
		//eliminamos al usuario, cuya id pasamos.
		$user->delete($_GET['id']);

		$response = array(
			'result' => 'ok'
		);

		Response::result(200, $response);
		break;
	default:
		$response = array(
			'result' => 'error'
		);

		Response::result(404, $response);

		break;
}
?>