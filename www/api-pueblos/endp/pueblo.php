<?php
require_once '../respuestas/response.php';
require_once '../modelos/pueblo.class.php';
require_once '../modelos/auth.class.php';

/**
 * endpoint para la gestión de datos con los pueblos.
 * Get (para objeter todos los pueblos)
 *  - token (para la autenticación y obtención del id usuario)
 * 
 * Post (para la creación de pueblo)
 *  - token (para la autenticación y obtención del id usuario)
 *  - datos del pueblo por body
 * 
 * Put (para la actualización del pueblo)
 *  *  - token (para la autenticación y obtención del id usuario)
 *  - id del pueblo por parámetro
 *  - datos nuevos del pueblo por body
 * 
 * Delete (para la eliminación del pueblo)
 *  *  - token (para la autenticación y obtención del id usuario)
 *  - id del pueblo por parámetro
 * 
 */


$auth = new Authentication();
//Compara que el token sea el correcto 
$auth->verify();



//hasta aquí, el token está perfectamente verificada. Creamos modelo para que pueda gestionar las peticiones
$pueblo = new Pueblo();

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$params = $_GET;  //aquí están todos los parámetros por url

       // $auth->insertarLog(); exit;
        //si pasamos un id del usuario, comprobamos que sea el mismo que el del token
        if (isset($_GET['id_usuario']) && !empty($_GET['id_usuario'])){
            //echo "Pasamos id_usuario es ".$_GET['id_usuario']." y el id del token es ".$auth->getIdUser();
            if ($_GET['id_usuario'] != $auth->getIdUser()){
                $response = array(
                    'result' => 'error',
                    'details' => 'El id no corresponde con el del usuario autenticado. '
                ); 
                Response::result(400, $response);
			    exit;
            }
        }else{
            //hay que añadir a $params el id del usuario.
            $params['id_usuario'] = $auth->getIdUser();
        }


        //necesitamos que esté obligatoriamente el id_usuario
        /*
        if(!isset($_GET['id_usuario']) || empty($_GET['id_usuario'])){
			$response = array(
				'result' => 'error',
				'details' => 'Error en la solicitud. id usuario desconocido '
			);

			Response::result(400, $response);
			exit;
		}
        
        if (!($auth->igualesIdUser($params["id_usuario"])))
        {
                $response = array(
                            'result' => 'error',
                            'details' => 'No tiene permisos para esa consulta'
                );
            
                Response::result(400, $response);
                exit;
        }
        */
        //Recuperamos todos los pueblos
        $pueblos = $pueblo->get($params);
        //$auth->insertarLog('lleva a solicitud de pueblos');
        $url_raiz_img = "http://".$_SERVER['HTTP_HOST']."/api-pueblos/public/img";
		for($i=0; $i< count($pueblos); $i++){
			if (!empty($pueblos[$i]['imagen']))
				$pueblos[$i]['imagen'] = $url_raiz_img ."/". $pueblos[$i]['imagen'];
		}


/*
        $response = array(
            'result'=> 'ok',
            'details'=>"Hay pueblos"
        );
        Response::result(200, $response);
        break;
*/
        $response = array(
            'result'=> 'ok',
            'pueblos'=> $pueblos
        );
       // $auth->insertarLog('devuelve pueblos'); 
        Response::result(200, $response);
        break;
    
    case 'POST':
       // $auth->insertaLog("Recibe petición de creacion de pueblo");

        /**
         * Recibimos el json con los datos a insertar, pero necesitamos
         * ogligatoriamente el id del usuario. Si no está, habrá un error.
         * El id del usuario verificado, deberá ser igual al id_usuario que
         * es la clave secundaria.
         * PUEDO SACAR TAMBIÉN LA id DEL USUARIO A PARTIR DE LA KEY.
         * ESTO LO HARÉ EN OTRA MODIFICACIÓN.
         */
        $params = json_decode(file_get_contents('php://input'), true);
     
       /*if (!isset($params) || !isset($params["id_usuario"]) || empty($params["id_usuario"])  || 
             !($auth->igualesIdUser($params["id_usuario"]))
            ){
                        $response = array(
                            'result' => 'error',
                            'details' => 'Error en la solicitud. Debes autenticarte o faltan parametros.'
                        );
            
                        Response::result(400, $response);
                        exit;
            }
        */
            //si pasamos un id del usuario, comprobamos que sea el mismo que el del token
        if (isset($params['id_usuario']) && !empty($params['id_usuario'])){
            //echo "Pasamos id_usuario es ".$_GET['id_usuario']." y el id del token es ".$auth->getIdUser();
            if ($params['id_usuario'] != $auth->getIdUser()){
                $response = array(
                    'result' => 'error',
                    'details' => 'El id pasado por body no corresponde con el del usuario autenticado. '
                ); 
                Response::result(400, $response);
			    exit;
            }
        }else{
            //hay que añadir a $params el id del usuario.
            $params['id_usuario'] = $auth->getIdUser();
        }




        $insert_id_pueblo = $pueblo->insert($params);
        //Debo hacer una consulta, para devolver tambien el nombre de la imagen.
        $id_param['id'] = $insert_id_pueblo;
        $pueblo = $pueblo->get($id_param);
        if($pueblo[0]['imagen'] !='')
            $name_file =  "http://".$_SERVER['HTTP_HOST']."/api-pueblos/public/img/".$pueblo[0]['imagen'];
        else
            $name_file = '';

        $response = array(
			'result' => 'ok insercion',
			'insert_id' => $insert_id_pueblo,
            'file_img'=> $name_file
		);

		Response::result(201, $response);
        break;


    case 'PUT':
        /*
        Es totalmente necesario tener los parámetros del id del pueblo a modificar
        y también el id del usuario, aunque esto lo puedo sacar del token.
        */
		$params = json_decode(file_get_contents('php://input'), true);
       /* if (!isset($params) ||  !isset($_GET['id']) || empty($_GET['id']) || !isset($params['id_usuario']) || empty($params['id_usuario'])){
            $response = array(
				'result' => 'error',
				'details' => 'Error en la solicitud de actualización del pueblo'
			);

			Response::result(400, $response);
			exit;
        }
        */

        if (!isset($params) || !isset($_GET['id']) || empty($_GET['id'])  ){
            $response = array(
				'result' => 'error',
				'details' => 'Error en la solicitud de actualización del pueblo. No has pasado el id del pueblo'
			);

			Response::result(400, $response);
			exit;
        }

         //si pasamos un id del usuario, comprobamos que sea el mismo que el del token
         if (isset($params['id_usuario']) && !empty($params['id_usuario'])){
            //echo "Pasamos id_usuario es ".$_GET['id_usuario']." y el id del token es ".$auth->getIdUser();
            if ($params['id_usuario'] != $auth->getIdUser()){
                $response = array(
                    'result' => 'error',
                    'details' => 'El id del body no corresponde con el del usuario autenticado. '
                ); 
                Response::result(400, $response);
			    exit;
            }
        }else{
            //hay que añadir a $params el id del usuario.
            $params['id_usuario'] = $auth->getIdUser();
        }


        $pueblo->update($_GET['id'], $params);  //actualizo ese pueblo.
        $id_param['id'] = $_GET['id'];
        $pueblo = $pueblo->get($id_param);
       

        if($pueblo[0]['imagen'] !='')
            $name_file =  "http://".$_SERVER['HTTP_HOST']."/api-pueblos/public/img/".$pueblo[0]['imagen'];
        else
            $name_file = '';
            
        $response = array(
			'result' => 'ok actualizacion',
            'file_img'=> $name_file
		);



		Response::result(200, $response);
        break;


    case 'DELETE':
        /*
        El id, también lo puedo sacar del token. Lo modificaré mas adelante.
        */
        if(!isset($_GET['id']) || empty($_GET['id'])){
			$response = array(
				'result' => 'error',
				'details' => 'Error en la solicitud'
			);

			Response::result(400, $response);
			exit;
		}

		$pueblo->delete($_GET['id']);

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