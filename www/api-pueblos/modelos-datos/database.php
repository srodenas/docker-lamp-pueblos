<?php

class Database
{
	private $connection;  //guardará la conexión
	private $results_page = 50; //número de resultados por página.

	//recordar que db es como he llamado al link para conectar ambos contenedores.
	public function __construct(){
		$this->connection = new mysqli('db', 'root', 'santi', 'pueblosDb', '3306');
		if($this->connection->connect_errno){
			echo 'Error de conexión a la base de datos';
			exit;
		}
	}


/*
		Arma la consulta sql con los parámetros pasados y ya
		validados. Los parámetros están en extra.

		Si dentro de nuestros parámetros es page, por ello está
		dentro de isset, lo eliminamos de la consulta porque
		hay un where y no tiene sentido poner el parámetro page.

		Si extra es distinto de null, hay parámetros por tanto hay que
		anexar el where.

		Para cada elemento con clave->valor, se añade después del where
		el parámetro y su valor. Si no es el último elemento del array asociativo,
		hay que anexarle el AND.

		Si hemos pasado también como parámetro page, lo que debemos hacer
		es poner un límite porque no queremos todos los registros.
		Si la page es mayor que 0, hay que armar con el query un LIMIT
		desde page -1 * número de registros por página (50) ejemplo. 
		Si page = 1, lo que hace es page -1 que es 0 multiplicado por 50 registros
		por página, por tanto empezará desde el registro 0 hasta los 50.
		Si page =2, 2-1 es igual a 1, por tanto 1*50 es desde los primeros 50 hasta los
		50 consecutivos. 
		Si page = 3, 3-1 es igual a 2, por tanto 2*50 es 100 que es desde el registro 100, hasta 50 más.
	
		Sino existe page, el límite está desde el registro 0 hasta 50.
		Con la query, hace la consulta y devuelve un array con los resultados en forma de array.
		*/

	public function getDB($table, $extra = null)
	{
		$page = 0;
		$query = "SELECT * FROM $table";

		/*
		Eliminamos page de los parámetros, pero nos quedamos con su valor en $page
		*/
		if(isset($extra['page'])){
			$page = $extra['page'];
			unset($extra['page']);
		}

		/*
		Si existen parámetros que deban seguir al where, tenemos que ir armando la Query
		Si el parámetro en Key es distinto a la última Key, hay que ir poniendo el AND
		Si por el contrario, es el último parámetro, no ponemos el AND
		*/
		if($extra != null){
			$query .= ' WHERE';

			foreach ($extra as $key => $condition) {
				$query .= ' '.$key.' = "'.$condition.'"';
				if($extra[$key] != end($extra)){
					$query .= " AND ";
				}
			}
		}

		//echo $query;
		//exit;
		/*
		Si queremos paginar, es porque hemos puesto page > 0. Formamos el límite.
		Está arriba explicado
		*/
		if($page > 0){
			$since = (($page-1) * $this->results_page);
			$query .= " LIMIT $since, $this->results_page";
		}
		else{
			//sólo queremos los primeros 50 registros o menos.
			$query .= " LIMIT 0, $this->results_page";
		}

		//echo $query;exit;
		$results = $this->connection->query($query);
		$resultArray = array();

		//pasamos todos los registros a resultArray.
		foreach ($results as $value) {
			$resultArray[] = $value;
			
		}

	//	echo $resultArray['id'];exit;
		return $resultArray;  //retornamos el array con los registros.
	}


/**
 * Arma un String con cada uno de los key separados por comas. Por cada parámetro
 * los separa por comas, pero los valores en el mismo orden, si que van encerrados
 * entre comillas simples.
 * Los campos los separo por comas con implode y los valores los separo por ''
 * Creamos la Query y realizamos la consulta. Retornamos el id insertado.
 */



	public function insertDB($table, $data)
	{
		/*
		array_keys, son los parámetros compuestos por 'key'=>'valor'
		Tengo que formar un String, con todos los key's separados por una (coma). Lo guardo
		dentro de $fields
		Tengo que formar un Sring, con todos los value's separados por una (coma). Lo guardo
		dentro de $values.

		*/ 
		$fields = implode(',', array_keys($data));
		$values = '"';
		$values .= implode('","', array_values($data));
		$values .= '"';

		//aquí hacemos la inserción de la query en la tabla.
		$query = "INSERT INTO $table (".$fields.') VALUES ('.$values.')';
		//echo $query;exit;
		$this->connection->query($query);

		return $this->connection->insert_id;
	}


/**
	 * Arma la query. Por cada parámetro, le ponemos el parámetro = al 
	 * valor encerrado por una coma simple y si hay más valores, hay que
	 * ponerle una coma para ir contatenando. 
	 * 
	 * Cuando ya tenemos la query, hay que concatenarle el where con el id
	 * que viene por parámetro. Ejecutamos la consulta. Si no hubo cambios, ninguna
	 * fila afectada, retorna 0 y si hay varias filas afectadas, retorna el número
	 * de registros afectados.
	 */


	public function updateDB($table, $id, $data)
	{	
		$query = "UPDATE $table SET ";
		foreach ($data as $key => $value) {
			$query .= "$key = '$value'";
			/*
			si ese dato no es el último, hay que añadir una ,
			*/
			if(sizeof($data) > 1 && $key != array_key_last($data)){
				$query .= " , ";
			}
		}

		$query .= ' WHERE id = '.$id;

		//echo $query; exit;
		$this->connection->query($query);

		if(!$this->connection->affected_rows){
			return 0;
		}

		return $this->connection->affected_rows;
	}




	public function deleteDB($table, $id)
	{
		$query = "DELETE FROM $table WHERE id = $id";
		$this->connection->query($query);

		if(!$this->connection->affected_rows){
			return 0;
		}

		return $this->connection->affected_rows;
	}
}


?>