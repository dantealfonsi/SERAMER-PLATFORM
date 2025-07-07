<?php
/*
(Procesos en Segundo Plano / Automatización
Implementa estos procesos como "cron jobs" o tareas programadas en tu servidor.
*/

require_once 'connect.php';

function pagos_atrasados() {
    $consulta = "SELECT * FROM pagos WHERE estado = 'atrasado'";
    $resultados = pg_array_sqlconector($consulta);
    
    if (empty($resultados)) {
        echo "No hay pagos atrasados.";
        return;
    }
    
    foreach ($resultados as $pago) {
        // Aquí podrías enviar un correo o notificación al usuario
        echo "Pago atrasado: " . $pago['id'] . " - Monto: " . $pago['monto'] . "<br>";
    }
}

function actualizar_estado_pagos() {
    $consulta = "UPDATE pagos SET estado = 'pagado' WHERE fecha_pago <= NOW() AND estado = 'pendiente'";
    $resultado = pg_sqlconector($consulta);
    
    if ($resultado) {
        echo "Estado de pagos actualizado correctamente.";
    } else {
        echo "Error al actualizar el estado de los pagos.";
    }
}

// Ejecutar las funciones
pagos_atrasados();

?>