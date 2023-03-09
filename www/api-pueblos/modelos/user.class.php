<?php
require_once '../respuestas/response.php';
require_once '../modelos-datos/database.php';



class User extends Database
{
	private $table = 'usuarios';  //nombre de la tabla

	//parámetros permitidos para hacer consultas selección.
	//sólo permito hacer consultas get siempre que esten estos parámetros aqui
	private $allowedConditions_get = array(
		'id',
		'nombre',
		'disponible',
		'imagen',
		'page'
	);


	//parámetros permitidos para la inserción. Al hacer el POST
	private $allowedConditions_insert = array(
		'email',
		'password',
		'nombre',
		'imagen',
		'disponible'
	);

//parámetros permitidos para la actualización.
private $allowedConditions_update = array(
	'email',
	'password',
	'nombre',
	'imagen',
	'disponible'
	
);


	/**
 * Valida que el campo nombre sea obligatorio y su valor distinto a vacío.
 * Llamamos este método desde el insert.
 * También debemos de validar que el disponible, tenga un valor 1 o 0. No puede aceptar
 * otro valor. Debe ser booleano.
 */
	private function validateInsert($data){
		
		if(!isset($data['email']) || empty($data['email'])){
			$response = array(
				'result' => 'error',
				'details' => 'El campo email es obligatorio'
			);

			Response::result(400, $response);
			exit;
		}
		if(!isset($data['nombre']) || empty($data['nombre'])){
			$response = array(
				'result' => 'error',
				'details' => 'El campo nombre es obligatorio'
			);

			Response::result(400, $response);
			exit;
		}
		/*
		Si viene el campo disponible, debe ser booleano.
		*/
		if(isset($data['disponible']) && !($data['disponible'] == "1" || $data['disponible'] == "0")){
			$response = array(
				'result' => 'error',
				'details' => 'El campo disponible debe ser del tipo boolean'
			);

			Response::result(400, $response);
			exit;
		}

		if (!isset($data['password'])  ||  empty($data['password'])) {
			$response = array(
				'result' => 'error',
				'details' => 'El password es obligatoria'
			);

			Response::result(400, $response);
			exit;
		}
		
		
		if (isset($data['imagen']) && !empty($data['imagen'])) {
			
			/*
			separo por la secuencia ;base64, el lado derecho, 
			es el archivo y el lado izquierdo el tipo de fichero(formato codificación).
			*/
			$img_array = explode(';base64,', $data['imagen']);
			//hago un explode para separar por / y la parte derecha es la extensión y la izquierda 'data_image'
			//lo paso a mayúsculas, por tanto tengo JPEG ó PNG ó JPG
			$extension = strtoupper(explode('/', $img_array[0])[1]); //me quedo con jpeg
			if ($extension!='PNG' && $extension!='JPG'  && $extension!='JPEG') {
				$response = array('result'  => 'error', 'details' => 'Formato de la imagen no permitida, sólo PNG/JPE/JPEG');
				Response::result(400, $response);
				exit;
			}//fin extensión
			/*echo "La imagen es: ".$img_array[1]."<br>";
			echo "La extensión es: ".$extension;
			exit;*/
		} //fin isset 

		

		return true;
	}



	private function validateUpdate($data){
		
		if(!isset($data['email']) || empty($data['email'])){
			$response = array(
				'result' => 'error',
				'details' => 'El campo email es obligatorio'
			);

			Response::result(400, $response);
			exit;
		}
		
		if(isset($data['disponible']) && !($data['disponible'] == "1" || $data['disponible'] == "0")){
			$response = array(
				'result' => 'error',
				'details' => 'El campo disponible debe ser del tipo boolean'
			);

			Response::result(400, $response);
			exit;
		}

		if (!isset($data['password'])  ||  empty($data['password'])){
			$response = array(
				'result' => 'error',
				'details' => 'El password es obligatoria'
			);

			Response::result(400, $response);
			exit;
		}

		if (isset($data['imagen']) && !empty($data['imagen'])) {
			$img_array = explode(';base64,', $data['imagen']);
			$extension = strtoupper(explode('/', $img_array[0])[1]); //me quedo con jpeg
			if ($extension!='PNG' && $extension!='JPG'  && $extension!='JPEG') {
				$response = array('result'  => 'error', 'details' => 'Formato de la imagen no permitida, sólo PNG/JPE/JPEG');
				Response::result(400, $response);
				exit;
			}//fin extensión
		} //fin isset 
		
		return true;
	}


	/*
Recorre los parámetros y si no existe alguno de ellos, crea un
error 400. Si no hubo error, ejecutará el método getDB de la clase
Database que es el padre. 

Al método que le pasa es el nombre de la tabla y los parámetros. Devuelve
los objetos de tipo clase.
	*/
	public function get($params){
		foreach ($params as $key => $param) {
			if(!in_array($key, $this->allowedConditions_get)){
				unset($params[$key]);
				$response = array(
					'result' => 'error',
					'details' => 'Error en la solicitud'
				);
	
				Response::result(400, $response);
				exit;
			}
		}

		//ejecuta el método getDB de Database. Contendrá todos los usuarios.
		$usuarios = parent::getDB($this->table, $params);

		return $usuarios;
	}



	/*
Recorremos todos los parámetros comprobando si están permitidos.
En el momento que encuentre a un parámetro que no está dentro de los permitidos,
arma un error 400 y se sale.

Si no se sale, los parámetros son los correctos, por tanto hay que ejecutar
una función que valida, porque consideramos que el campo nombre debe estar ya que es
obligatorio y de que no venga su nombre vacío.

Si la validación es correcta, llamamos al método insertDB de database. Nos arma la consulta
y la ejecutará.
*/
	public function insert($params)
	{
		//recordamos que params, es un array asociaivo del tipo 'id'=>'1', 'nombre'=>'santi'
		foreach ($params as $key => $param) {
			//echo $key." = ".$params[$key];
			if(!in_array($key, $this->allowedConditions_insert)){
				unset($params[$key]);
				$response = array(
					'result' => 'error',
					'details' => 'Error en la solicitud. Parametro no permitido'
				);
	
				Response::result(400, $response);
				exit;
			}
		}
		//ejecutará la función que valida los parámetros pasados.
		
		if($this->validateInsert($params)){
			
			if (isset($params['imagen'])){
				/*echo "Tiene imagen";
				exit;*/
				$img_array = explode(';base64,', $params['imagen']);  //datos de la imagen
				$extension = strtoupper(explode('/', $img_array[0])[1]); //formato de la imagen
				$datos_imagen = $img_array[1]; //aqui me quedo con la imagen
				$nombre_imagen = uniqid(); //creo un único id.
				//del directorio actual de user.class, subo un nivel (1) y estando en el directorio api-pueblos, concateno public\img
				$path = dirname(__DIR__, 1)."/public/img/".$nombre_imagen.".".$extension;
				/*echo "La imagen es ".$nombre_imagen.".".$extension;
				echo "El path es ".$path;
				exit;*/
				file_put_contents($path, base64_decode($datos_imagen));  //subimos la imagen al servidor.
				$params['imagen'] = $nombre_imagen.'.'.$extension;  //pasamos como parametro en foto, con el nombre y extensión completo.
				//exit;  //hay que quitarlo una vez verificado que se sube la imagen
			}//fin isset

			//ahora debemos encriptar la password
			$password_encriptada = hash('sha256' , $params['password']);
			$params['password'] = $password_encriptada;
			//se llama al padre con el método inserDB.
			return parent::insertDB($this->table, $params);
		}

		
	}



/**
 * Recibimos el id y los parámetros a modificar.
 * Al igual que antes, comprobamos que todos los parámetros estén dentro de 
 * las condiciones permitidas como en el caso del insert. Si hay no está alguno de
 * los parámetros en los permitidos, hay un error.
 * 
 * Volvemos a validar los parámetros, ya que debe comprobar que esté el nombre y disponible
 * sea booleano. Si da true, llama al método update de la clase database. Le pasamos el nombre
 * de la tabla, el id y los parámetros. La actualización de database, devuelve el número
 * de registros afectados. Comprobamos si ha sido 0, por tanto podemos considerarlo como queramos
 * que en nuestro caso es un error ya que no hubo cambios. En el caso de que hay devuelto más de 0
 * registros, no devuelve nada porque la respuesta la hará desde la clase user.php
 * 
 * 
 */	
	public function update($id, $params)
	{
		foreach ($params as $key => $parm) {
			//debe comprobar que los parámetros son los permitidos.
			//si hubiera otro parámetro como 'codigo', no estaría permitida.
			if(!in_array($key, $this->allowedConditions_update)){
				unset($params[$key]);
				echo $params[$key];
				$response = array(
					'result' => 'error',
					'details' => 'Error en la solicitud dentro del modelo datos'
				);
	
				Response::result(400, $response);
				exit;
			}
		}

		/*
		Este método, valida que los datos a actualizar son los correctos y
		obligatorios, como el email, password y si está el parámetro disponible, que sea booleano
		*/
		if($this->validateUpdate($params)){
			//ahora debemos encriptar la password
			$password_encriptada = hash('sha256' , $params['password']);
			$params['password'] = $password_encriptada;
			//Si mandamos imagen.
			if (isset($params['imagen'])){

				//necesito saber el nombre del fichero antiguo a partir del id y eliminarlo del servidor.
				$usuarios = parent::getDB($this->table, $_GET);
				$usuario = $usuarios[0];
				$imagen_antigua = $usuario['imagen'];
				//echo $imagen_antigua;
				$path = dirname(__DIR__, 1)."/public/img/".$imagen_antigua;
				//si no puedo eliminar la imagen antigua, lo indico.
				if (!unlink($path)){
					$response = array(
						'result' => 'warning',
						'details' => 'No se ha podido eliminar el fichero antiguo'
					);	
					Response::result(200, $response);
					exit;
					
				}
				
				/*foreach ($usu as $item => $value) 
					echo $item.": ".$value;
				*/
				//exit;

				//ahora tengo que crear la nueva imagen y actualizar registro.
				
				$img_array = explode(';base64,', $params['imagen']);  //datos de la imagen
				$extension = strtoupper(explode('/', $img_array[0])[1]); //formato de la imagen
				$datos_imagen = $img_array[1]; //aqui me quedo con la imagen
				$nombre_imagen = uniqid(); //creo un único id.
				$path = dirname(__DIR__, 1)."/public/img/".$nombre_imagen.".".$extension;
				file_put_contents($path, base64_decode($datos_imagen));  //subimos la imagen al servidor.
				$params['imagen'] = $nombre_imagen.'.'.$extension;  //pasamos como parametro en foto, con el nombre y extensión completo.
			}//fin isset




			//actualizamos el registro a partir de una query que habrá que armar en updateDB
			$affected_rows = parent::updateDB($this->table, $id, $params);

			if($affected_rows==0){
				$response = array(
					'result' => 'error',
					'details' => 'No hubo cambios'
				);
				
				Response::result(200, $response);
				exit;
			}
		}

			
	}


	/**
 * Este método, elimina el registro llamando al database. Si
 * el número de registros afectados es 0, no se ha encontrado ese registro
 * y por tanto arma una respuesta de error.
 * 
 * Si todo ha ido bien, retorna de la función a user y éste acaba.
 */
	public function delete($id)
	{

		//Necesito eliminar su imagen, en el supuesto de que exista.	
		$usuarios = parent::getDB($this->table, $_GET);
		$usuario = $usuarios[0];
		$imagen_antigua = $usuario['imagen'];
		if(!empty($imagen_antigua)){
			$path = dirname(__DIR__, 1)."/public/img/".$imagen_antigua;
			if (!unlink($path)){
				$response = array(
					'result' => 'warning',
					'details' => 'No se ha podido eliminar la imagen del usuario'
				);	
				Response::result(200, $response);
				exit;
					
			}

		}
		
		$affected_rows = parent::deleteDB($this->table, $id);

		if($affected_rows==0){
			$response = array(
				'result' => 'error',
				'details' => 'No hubo cambios'
			);

			Response::result(200, $response);
			exit;
		}
	}
}

?>