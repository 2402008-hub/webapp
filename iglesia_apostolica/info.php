<?php
/**
 * Proyecto: Demostración de Rutinas Almacenadas en MySQL (Procedimientos y Funciones)
 * Alumno: Luis Angel Barahona Jimenez
 * Asignatura: Programación en Bases de Datos / Proyecto Integrador
 * Universidad: Universidad Tecnológica de la Riviera Maya (UTRM)
 */

// 1. Configuración de la conexión a la base de datos (Laragon Localhost)
$host = 'localhost';
$user = 'root';
$password = ''; // Por defecto en Laragon está vacío
$dbname = 'test';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    // Configurar el modo de error de PDO para que lance excepciones
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion_exitosa = true;
} catch (PDOException $e) {
    $conexion_exitosa = false;
    $error_mensaje = $e->getMessage();
}

// Inicialización de variables para los resultados
$resultado_hola_mundo = "";
$resultado_version = "";
$resultado_funcion_estado = [];

if ($conexion_exitosa) {
    try {
        // Ejecución del Ejemplo 5.1: Procedimiento hola_mundo()
        $stmt1 = $pdo->query("CALL hola_mundo()");
        $res1 = $stmt1->fetch(PDO::FETCH_ASSOC);
        $resultado_hola_mundo = $res1 ? $res1['mensaje'] : "No se retornaron datos";
        $stmt1->closeCursor(); // Cerrar el cursor para permitir la siguiente consulta

        // Ejecución del Ejemplo 5.2: Procedimiento ver_version()
        $stmt2 = $pdo->query("CALL ver_version()");
        $res2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        $resultado_version = $res2 ? $res2['Versión de MySQL'] : "No se retornaron datos";
        $stmt2->closeCursor();

        // Ejecución del Ejemplo 5.4: Función estado() pasándole diferentes parámetros
        $estados_a_probar = ['P', 'O', 'N', 'X'];
        foreach ($estados_a_probar as $est) {
            $stmt3 = $pdo->prepare("SELECT estado(?) AS resultado");
            $stmt3->execute([$est]);
            $res3 = $stmt3->fetch(PDO::FETCH_ASSOC);
            $resultado_funcion_estado[$est] = $res3 ? $res3['resultado'] : "Error";
            $stmt3->closeCursor();
        }

    } catch (PDOException $e) {
        $error_consultas = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demostración de Rutinas Almacenadas - MySQL & PHP</title>
    <!-- Bootstrap 5 CDN para un diseño limpio y profesional -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/style.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .card-header { background-color: #0d6efd; color: white; font-weight: bold; }
        .card-header.procedure { background-color: #198754; }
        .card-header.function { background-color: #0dcaf0; color: #333; }
        pre { background-color: #212529; color: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 0.9rem; }
        .badge-status { font-size: 1rem; padding: 8px 12px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold text-primary">Evidencia de Programación en Base de Datos</h1>
        <p class="lead">Integración de Procedimientos Almacenados y Funciones de MySQL en PHP mediante PDO</p>
        <span class="badge bg-secondary p-2">Entorno: Laragon Localhost</span>
    </div>

    <!-- Estado de la Conexión -->
    <div class="alert <?php echo $conexion_exitosa ? 'alert-success' : 'alert-danger'; ?> d-flex align-items-center" role="alert">
        <div>
            <strong>Estado del Servidor:</strong> 
            <?php 
            if ($conexion_exitosa) {
                echo "Conexión exitosa a la base de datos <code>test</code> en Laragon.";
            } else {
                echo "Error de conexión: " . $error_mensaje;
            }
            ?>
        </div>
    </div>

    <?php if (isset($error_consultas)): ?>
        <div class="alert alert-warning" role="alert">
            <strong>Nota técnica para revisión:</strong> <?php echo $error_consultas; ?> <br>
            <small>Asegúrate de haber creado primero los procedimientos en DBeaver ejecutando el script de la tarea.</small>
        </div>
    <?php endif; ?>

    <div class="row mt-4">
        <!-- EJEMPLO 5.1: HOLA MUNDO -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header procedure">
                    Ejemplo 5.1: Procedimiento <code>hola_mundo()</code>
                </div>
                <div class="card-header bg-white text-dark small">
                    <strong>Código SQL en la Base de Datos:</strong>
<pre><code>CREATE PROCEDURE hola_mundo ()
BEGIN
    SELECT 'hola mundo' AS mensaje;
END;</code></pre>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Resultado de la ejecución en PHP:</h5>
                    <p class="card-text fs-4 text-center text-success fw-bold p-3 bg-light border rounded">
                        "<?php echo htmlspecialchars($resultado_hola_mundo); ?>"
                    </p>
                    <p class="text-muted small">Invocado mediante la sentencia PHP: <code>$pdo->query("CALL hola_mundo()")</code></p>
                </div>
            </div>
        </div>

        <!-- EJEMPLO 5.2: VER VERSIÓN -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header procedure">
                    Ejemplo 5.2: Procedimiento <code>ver_version()</code>
                </div>
                <div class="card-header bg-white text-dark small">
                    <strong>Código SQL en la Base de Datos:</strong>
<pre><code>CREATE PROCEDURE ver_version ()
BEGIN
    SELECT version() AS 'Versión de MySQL';
END;</code></pre>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Resultado de la ejecución en PHP:</h5>
                    <p class="card-text fs-4 text-center text-dark fw-bold p-3 bg-light border rounded">
                        📊 <?php echo htmlspecialchars($resultado_version); ?>
                    </p>
                    <p class="text-muted small">Invocado mediante la sentencia PHP: <code>$pdo->query("CALL ver_version()")</code></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- EJEMPLO 5.4: FUNCIÓN ESTADO -->
        <div class="col-12">
            <div class="card">
                <div class="card-header function">
                    Ejemplo 5.4: Función Almacenada <code>estado(in_estado CHAR(1))</code>
                </div>
                <div class="card-header bg-white text-dark small">
                    <strong>Código SQL en la Base de Datos (Estructura Condicional IF/ELSEIF):</strong>
<pre><code>CREATE FUNCTION estado (in_estado CHAR(1)) RETURNS VARCHAR(20)
BEGIN
    DECLARE estado_texto VARCHAR(20);
    IF in_estado = 'P' THEN SET estado_texto = 'caducado';
    ELSEIF in_estado = 'O' THEN SET estado_texto = 'activo';
    ELSEIF in_estado = 'N' THEN SET estado_texto = 'nuevo';
    ELSE SET estado_texto = 'desconocido';
    END IF;
    RETURN (estado_texto);
END;</code></pre>
                </div>
                <div class="card-body">
                    <h5 class="card-title mb-3">Prueba Dinámica de Retorno de Datos:</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Parámetro Enviado (Letra)</th>
                                    <th>Significado Esperado en el Libro</th>
                                    <th>Resultado devuelto por MySQL Funcion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>'P'</code></td>
                                    <td>Caducado</td>
                                    <td><span class="badge bg-danger badge-status"><?php echo htmlspecialchars($resultado_funcion_estado['P'] ?? 'N/A'); ?></span></td>
                                </tr>
                                <tr>
                                    <td><code>'O'</code></td>
                                    <td>Activo</td>
                                    <td><span class="badge bg-success badge-status"><?php echo htmlspecialchars($resultado_funcion_estado['O'] ?? 'N/A'); ?></span></td>
                                </tr>
                                <tr>
                                    <td><code>'N'</code></td>
                                    <td>Nuevo</td>
                                    <td><span class="badge bg-primary badge-status"><?php echo htmlspecialchars($resultado_funcion_estado['N'] ?? 'N/A'); ?></span></td>
                                </tr>
                                <tr>
                                    <td><code>'X'</code> (Cualquier otra)</td>
                                    <td>Desconocido / Filtro de Control</td>
                                    <td><span class="badge bg-warning text-dark badge-status"><?php echo htmlspecialchars($resultado_funcion_estado['X'] ?? 'N/A'); ?></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mt-2">Invocado de forma segura en ciclo mediante PHP usando Prepared Statements: <code>SELECT estado(?)</code></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página con información del alumno -->
    <div class="text-center text-muted border-top pt-4 mt-5">
        <p class="mb-1"><strong>Demostración para Revisión Académica</strong></p>
        <p class="small">Desarrollado con PHP 8, PDO, Bootstrap 5 y MySQL Server en entorno Laragon.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>