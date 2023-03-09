<?php

/**
 * Modelo para la authenticación.
 */
class AuthModel
{
	private $connection;
	
	public function __construct(){
			//recordar que db es como he llamado al link para conectar ambos contenedores.

		$this->connection = new mysqli('db', 'root', 'santi', 'pueblosDb', '3306');

		if($this->connection->connect_errno){
			echo 'Error de conexión a la base de datos';
			exit;
		}
	}

	/**
	 * Este método, recibe el email y el password ya codificado.
	 * Realiza una query, devolviendo el id, nombres a partir del username y de la password codificada.
	 */

	public function login($email, $password)
	{
		$query = "SELECT id, nombre, email FROM usuarios WHERE email = '$email' AND password = '$password'";

		$results = $this->connection->query($query);

		$resultArray = array();

		if($results != false){
			foreach ($results as $value) {
				$resultArray[] = $value;
			}
		}

		//devuelve un array con el id, nombres y username.
		return $resultArray;
	}

	/**
	 * Setea el token a partir del id. Cada logeo, tenemos que actualizar el registro.
	 */

	public function update($id, $token)
	{
		$query = "UPDATE usuarios SET token = '$token' WHERE id = $id";

		$this->connection->query($query);
		
		if(!$this->connection->affected_rows){
			return 0;
		}

		return $this->connection->affected_rows;
	}

	/**
	 * Retorna el token dado un id de usuario.
	 */
	public function getById($id)
	{
		$query = "SELECT token FROM usuarios WHERE id = $id";

		$results = $this->connection->query($query);

		$resultArray = array();

		if($results != false){
			foreach ($results as $value) {
				$resultArray[] = $value;
			}
		}

		return $resultArray;
	}



	public function insertarLog($milog){
		$query = "INSERT INTO log (log) VALUES('$milog')";
		//echo $query;exit;
		$this->connection->query($query);
	}

	public function devUserModel($id)
	{
		$query = "SELECT id, nombre, email, imagen FROM usuarios WHERE id = $id";

		$results = $this->connection->query($query);

		$resultArray = array();

		if($results != false){
			foreach ($results as $value) {
				$resultArray[] = $value;
			}
		}

		//devuelve un array con el id, nombres y username.
		return $resultArray;
	}
}
