<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Allow: GET, POST, OPTIONS, PUT, DELETE');
header('Content-Type: application/json');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://apis.google.com");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

$method = $_SERVER['REQUEST_METHOD'];

require_once 'connect.php';

// Métodos de comunicación con el front

if ($method == "OPTIONS") {
    die();
}

if ($method == "POST") {
    try {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if(isset($data['addTipoAlerta'])){
            $obj = array('message' => null);
            $nombre = $data['data']['nombre'];
            $descripcion = $data['data']['descripcion'];
            $auto = $data['data']['auto'];
            $dias = $data['data']['dias'];

            //INSERT INTO products (product_id, name, price) VALUES (101, 'Laptop', 1200.00)
            //ON CONFLICT (product_id) DO UPDATE SET name = EXCLUDED.name, price = EXCLUDED.price;

            $query = "INSERT INTO cat_tipos_alerta(nombre_tipo_alerta, descripcion, escalamiento_automatico, dias_para_escalar)
            values('$nombre', '$descripcion', '$auto', $dias) ON CONFLICT (nombre_tipo_alerta) DO UPDATE SET descripcion = EXCLUDED.descripcion, escalamiento_automatico= EXCLUDED.escalamiento_automatico, dias_para_escalar= EXCLUDED.dias_para_escalar";

            //error_log($query, 3,"C:/xampp/htdocs/SERAMER-PLATFORM/var/log/error.log"); 
            $result = pg_sqlconector($query);

            if($result){
                $obj['message'] = "Tipo de Alerta Creada con Exito...";
            }
            else{
                $obj['message'] = "error!";
            }

            echo json_encode($obj);
        }

        if(isset($data['addAdjudicatarios'])){
            $obj = array('message' => null);

            $tipo_documento = $data['data']['tipoDocumento'];
            $numero_documento = $data['data']['numeroDocumento'];
            $razon_social_nombre= $data['data']['nombreRazonSocial'];
            $apellido= $data['data']['apellido'];
            $telefono= $data['data']['telefono'];
            $correo_electronico= $data['data']['correo'];
            $direccion_fiscal= $data['data']['direccion'];
            $solvencia_financiera = $data['data']['solvenciaFinanciera'];
            $es_persona_juridica = $data['data']['esPersonaJuridica'];
            $nombre_representante_legal= $data['data']['nombreRepresentanteLegal']; 
            $fecha_registro = $data['data']['fechaRegistro'];
            $estado_activo = $data['data']['activo'];

            $query = "INSERT INTO adjudicatarios(
                tipo_documento,
                numero_documento,
                razon_social_nombre,
                apellido,
                telefono,
                correo_electronico,
                direccion_fiscal,
                solvencia_financiera,
                es_persona_juridica,
                nombre_representante_legal,
                fecha_registro,
                estado_activo
                )
                values(
                '$tipo_documento',
                '$numero_documento',
                '$razon_social_nombre',
                '$apellido',
                '$telefono',
                '$correo_electronico',
                '$direccion_fiscal',
                '$solvencia_financiera',
                '$es_persona_juridica',
                '$nombre_representante_legal',
                '$fecha_registro',
                '$estado_activo'                
                ) ON CONFLICT (numero_documento) DO UPDATE SET 
                razon_social_nombre = EXCLUDED.razon_social_nombre,
                apellido =EXCLUDED.apellido,
                telefono =EXCLUDED.telefono,
                correo_electronico= EXCLUDED.correo_electronico,
                direccion_fiscal=EXCLUDED.direccion_fiscal,
                solvencia_financiera=EXCLUDED.solvencia_financiera,
                es_persona_juridica=EXCLUDED.es_persona_juridica,
                nombre_representante_legal=EXCLUDED.nombre_representante_legal,
                fecha_registro=EXCLUDED.fecha_registro,
                estado_activo = EXCLUDED.estado_activo
                ";

            //error_log($query, 3,"C:/xampp/htdocs/SERAMER-PLATFORM/var/log/error.log"); 
            $result = pg_sqlconector($query);

            if($result){
                $obj['message'] = "Adjudicatario Creado con Exito...";
            }
            else{
                $obj['message'] = "error!";
            }

            echo json_encode($obj);
        }        

        if (isset($data['alertas_cumplimiento'])) {
            $obj = array('message' => null, 'alerta' => array());

            $id_tipo_alerta = $data['id_tipo_alerta'];
            $id_adjudicatario = $data['id_adjudicatario'];
            $id_puesto = $data['id_puesto'];
            $descripcion_alerta = $data['descripcion_alerta'];
            $generada_por = $data['generada_por'];
            
            if (isset($id_tipo_alerta) && isset($id_adjudicatario)) {
                $sql = "INSERT INTO alertas_cumplimiento (id_tipo_alerta, id_adjudicatorio, id_puesto, descripcion_alerta, generada_por) 
                        VALUES ($id_tipo_alerta, $id_adjudicatario, $id_puesto, '$descripcion_alerta', '$generada_por')";
                $result = pg_sqlconector($sql);
                if ($result) {
                    $obj['message'] = 'Alerta registrada exitosamente';
                    $obj['alerta'] = array(
                        'id_alerta' => getPgPDOConnection()->lastInsertId(), // Obtiene el ID de la última inserción
                        'id_tipo_alerta' => $id_tipo_alerta,
                        'id_adjudicatorio' => $id_adjudicatario,
                        'id_puesto' => $id_puesto,
                        'descripcion_alerta' => $descripcion_alerta,
                        'fecha_generacion' => date('Y-m-d H:i:s'),
                        'estado_alerta' => 'pendiente',
                    );
                } else {
                    $obj['message'] = 'Error al insertar la alerta de cumplimiento.';
                }
            }
            echo json_encode($obj);
        }

        if (isset($data['seguimiento_alertas'])) {
            $obj = array('message' => null, 'alerta' => array());

            $id_alerta = $data['id_alerta'];
            $tipo_accion = $data['tipo_accion'];
            $descripcion_accion = $data['descripcion_accion'];
            $resultado_accion = $data['resultado_accion'];
            $realizado_por = $data['realizado_por'];
            
            if (isset($id_alerta) && isset($tipo_accion)) {
                $sql = "INSERT INTO seguimiento_alertas (id_alerta, tipo_accion, descripcion_accion, resultado_accion, realizado_por) 
                        VALUES ($id_alerta, '$tipo_accion', '$descripcion_accion', '$resultado_accion', '$realizado_por')";
                $result = pg_sqlconector($sql);
                if ($result) {
                    $obj['message'] = 'Acción de seguimiento registrada exitosamente';
                    $obj['seguimiento'] = array(
                        'id_seguimiento' => getPgPDOConnection()->lastInsertId(), // Obtiene el ID de la última inserción
                        'id_alerta' => $id_alerta,
                        'tipo_accion' => $tipo_accion,
                        'descripcion_accion' => $descripcion_accion,
                        'resultado_accion' => $resultado_accion,
                        'fecha_accion' => date('Y-m-d H:i:s'),
                        'realizado_por' => $realizado_por,
                    );
                } else {
                    $obj['message'] = 'Error al insertar el seguimiento_alertas.';
                }
            }
            echo json_encode($obj);
        }

        if (isset($data['update_alertas_cumplimiento'])) {
            $obj = array('message' => null, 'alerta' => array());

            $id_alerta = $data['id_alerta'];
            $estado_alerta = $data['estado_alerta'];
            
            if (isset($id_alerta)) {
                $accion = "";
                if($estado_alerta == 'Resuelta' || $estado_alerta == 'Escalada a Infraccion'){
                    $accion = ", fecha_resolucion_escalada = '".date('Y-m-d H:i:s')."'";
                }

                $sql = "UPDATE alertas_cumplimiento 
                        SET estado_alerta = '$estado_alerta' {$accion} 
                        WHERE id_alerta = $id_alerta";

                $result = pg_sqlconector($sql);
                if ($result) {
                    $obj['message'] = 'Estado de alerta actualizado exitosamente';
                    $obj['alerta'] = array(
                        'id_alerta' => $id_alerta,
                        'estado_alerta' => $estado_alerta,
                        'fecha_resolucion_escalada' => date('Y-m-d H:i:s'),
                    );
                } else {
                    $obj['message'] = 'Error al actualizar la alerta de cumplimiento.';
                }
            }
            echo json_encode($obj);
        }

        if (isset($data['escalar_a_infraccion'])) {
            $obj = array('message' => null, 'alerta' => array());

            $id_alerta = $data['id_alerta'];
            $id_adjudicatario = pg_row_sqlconector("SELECT id_adjudicatorio FROM alertas_cumplimiento WHERE id_alerta = $id_alerta")['id_adjudicatorio'];
            $articulo_violado = $data['articulo_violado'];
            $descripcion_infraccion = $data['descripcion_infraccion'];
            $id_tipo_infraccion = $data['id_tipo_infraccion'];
            $evidencia_url = $data['evidencia_url'];
            $observaciones_fiscal = $data['observaciones_fiscal'];
            $registrada_por_usuario = $data['registrada_por_usuario'];

            if (isset($id_alerta)) {
                $sql = "INSERT INTO infracciones (id_adjudicatorio, articulo_violado, descripcion_infraccion, id_tipo_infraccion, evidencia_url, observaciones_fiscal, registrada_por_usuario) 
                        VALUES ($id_adjudicatario, '$articulo_violado', '$descripcion_infraccion', $id_tipo_infraccion, '$evidencia_url', '$observaciones_fiscal', '$registrada_por_usuario')";
                $result = pg_sqlconector($sql);
                if ($result) {
                    $obj['message'] = 'Estado de alerta actualizado exitosamente';
                    $obj['infraccion_creada'] = array(
                        'id_infraccion' => getPgPDOConnection()->lastInsertId(), // Obtiene el ID de la última inserción
                        'id_alerta' => $id_alerta,
                        'id_adjudicatorio' => $id_adjudicatario,
                        'articulo_violado' => $articulo_violado,
                        'descripcion_infraccion' => $descripcion_infraccion
                    );
                } else {
                    $obj['message'] = 'Error al actualizar la alerta de cumplimiento.';
                }
            }
            echo json_encode($obj);
        }        

    } catch (Exception $e) {
        $response = array('Error' => $e->getMessage());
        echo json_encode($response);
    }
}

//**********METODO DE LLAMADA GET */
if ($method == "GET") {
    $petitions = "";
    if(isset($_GET['petitions'])){
      $petitions = $_GET['petitions'];
    }
    
    if($petitions == 'cerrarSesion'){
        // Destruir todas las variables de sesión
        $_SESSION = array();
        
        // Si se desea destruir la sesión completamente, también se debe destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Finalmente, destruir la sesión
        session_destroy();
        echo json_encode(array('result' => true, 'order' => null, 'message' => 'Cambios realizados con exito.!'));
    }

    if($petitions == 'getquejas'){
        $type = $_GET['type'];
        $status = $_GET['status'];
        $sql = "select * from quejas";
        if($type == "xxx"){
            $sql = "select * from quejas";
        }
        echo json_encode(pg_array_sqlconector($sql));
    }
    
    if($petitions == 'tipos'){
        $sql = "select tipo_queja from quejas";
        echo json_encode(pg_array_sqlconector($sql));
    }

    if($petitions == 'tipoAlertas'){
        $sql = "select * from cat_tipos_alerta order by id_tipo_alerta";
        echo json_encode(pg_array_sqlconector($sql));
    }    

    if($petitions == 'listAdjudicatarios'){
        $sql = "select * from adjudicatarios order by id_adjudicatario";
        echo json_encode(pg_array_sqlconector($sql));
    }    

    if($petitions == 'alertas_cumplimiento'){
        $obj = array();
        $sql = "select * from alertas_cumplimiento";
        $alertas_cumplimiento = pg_array_sqlconector($sql);

        foreach ($alertas_cumplimiento as $alerta) {
            $obj[] = array(
                'id' => $alerta['id_alerta'],
                'tipo_alerta' => array(
                    'id_tipo_alerta' => $alerta['id_tipo_alerta'],
                    'nombre' => pg_row_sqlconector("select nombre from cat_tipos_alerta where id_tipo_alerta = ".$alerta['id_tipo_alerta'])['nombre']
                ),
                'adjudicatorio' => array(
                    'id_adjudicatorio' => $alerta['id_adjudicatorio'], 
                    'nombre' => pg_row_sqlconector("select razon_social_nombre as nombre from adjudicatorios where id_adjudicatorio = ".$alerta['id_adjudicatorio'])['nombre'],
                    'numero_documento'=> pg_row_sqlconector("select numero_documento from adjudicatorios where id_adjudicatorio = ".$alerta['id_adjudicatorio'])['numero_documento']
                ),
                'puesto'=> array(
                    'id_puesto' => $alerta['id_puesto'],
                    'nombre' => pg_row_sqlconector("select codigo_puesto from puestos_locales where id_puesto = ".$alerta['id_puesto'])['codigo_puesto']
                ),                
                'fecha_generacion' => $alerta['fecha_generacion'],
                'descripcion_alerta' => $alerta['descripcion_alerta'],
                'estado_alerta' => $alerta['estado_alerta'],
            );
        }
        echo json_encode($obj);
    }

    if($petitions == 'alertas_cumplimiento' && isset($_GET['id_alerta'])){
        $id_alerta = $_GET['id_alerta'];
        $sql = "select * from alertas_cumplimiento where id_alerta = $id_alerta";
        $obj = array();
        $alerta = pg_row_sqlconector($sql);
        if ($alerta) {
            $obj[] = array(
                'id' => $alerta['id_alerta'],
                'tipo_alerta' => array(
                    'id_tipo_alerta' => $alerta['id_tipo_alerta'],
                    'nombre' => pg_row_sqlconector("select nombre from cat_tipos_alerta where id_tipo_alerta = ".$alerta['id_tipo_alerta'])['nombre'],
                    'descripcion' => pg_row_sqlconector("select descripcion from cat_tipos_alerta where id_tipo_alerta = ".$alerta['id_tipo_alerta'])['descripcion']
                ),
                'pagos' => array(
                    'escalamiento_automatico' => true,
                    'dias_para_escalar' => 15
                ),
                'adjudicatorio' => array(
                    'id_adjudicatorio' => $alerta['id_adjudicatorio'], 
                    'nombre' => pg_row_sqlconector("select razon_social_nombre as nombre from adjudicatorios where id_adjudicatorio = ".$alerta['id_adjudicatorio'])['nombre'],
                    'numero_documento'=> pg_row_sqlconector("select numero_documento from adjudicatorios where id_adjudicatorio = ".$alerta['id_adjudicatorio'])['numero_documento'],
                    'telefono' => pg_row_sqlconector("select telefono from adjudicatorios where id_adjudicatorio = ".$alerta['id_adjudicatorio'])['telefono'],
                    'email' => pg_row_sqlconector("select correo_electronico from adjudicatorios where id_adjudicatorio = ".$alerta['id_adjudicatorio'])['correo_electronico']
                ),
                'puesto'=> array(
                    'id_puesto' => $alerta['id_puesto'],
                    'nombre' => pg_row_sqlconector("select codigo_puesto from puestos_locales where id_puesto = ".$alerta['id_puesto'])['codigo_puesto'],
                    'ubicacion_detalle' => pg_row_sqlconector("select ubicacion_detalle from puestos_locales where id_puesto = ".$alerta['id_puesto'])['ubicacion_detalle']                    
                ),                
                'fecha_generacion' => $alerta['fecha_generacion'],
                'descripcion_alerta' => $alerta['descripcion_alerta'],
                'estado_alerta' => $alerta['estado_alerta'],
                'historial_seguimiento' => array(
                    'seguimiento' => pg_array_sqlconector("select * from seguimiento_alertas where id_alerta = $id_alerta")
                )
            );
        } else {
            $obj = array('result' => false, 'error' => '404 Not Found');
        }

        echo json_encode($obj);
    }    
    
}

?>
