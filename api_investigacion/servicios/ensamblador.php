<?php
declare(strict_types=1);

// Área de conocimiento
require_once __DIR__ . '/../repositorios/RepositorioAreaConocimientoMariaDB.php';
require_once __DIR__ . '/ServicioAreaConocimiento.php';
// Universidad
require_once __DIR__ . '/../repositorios/RepositorioUniversidadMariaDB.php';
require_once __DIR__ . '/ServicioUniversidad.php';
// ODS
require_once __DIR__ . '/../repositorios/RepositorioObjetivoDesarrolloSostenibleMariaDB.php';
require_once __DIR__ . '/ServicioObjetivoDesarrolloSostenible.php';
// Área de Aplicación
require_once __DIR__ . '/../repositorios/RepositorioAreaAplicacionMariaDB.php';
require_once __DIR__ . '/ServicioAreaAplicacion.php';
// Término Clave
require_once __DIR__ . '/../repositorios/RepositorioTerminoClaveMariaDB.php';
require_once __DIR__ . '/ServicioTerminoClave.php';
// Línea de Investigación
require_once __DIR__ . '/../repositorios/RepositorioLineaInvestigacionMariaDB.php';
require_once __DIR__ . '/ServicioLineaInvestigacion.php';

class Ensamblador 
{
    private static ?PDO $pdo = null;

    private static function obtenerConexion(): PDO 
    {
        if (self::$pdo === null) {
            $host = getenv('DB_HOST') ?: 'mariadb';
$db   = getenv('DB_DATABASE') ?: 'investigacion';
$user = getenv('DB_USERNAME') ?: 'root';  // Cambiamos a root
$pass = getenv('DB_PASSWORD') ?: 'root';  // La clave de root según el docker-compose
            
            self::$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_FOUND_ROWS => true
            ]);
        }
        return self::$pdo;
    }

    // EXPLICACIÓN CLASE: El ensamblador es una "Fábrica". Nos entrega el servicio ya armado con su conexión.
    public static function crearServicioAreaConocimiento(): IServicioAreaConocimiento {
        return new ServicioAreaConocimiento(new RepositorioAreaConocimientoMariaDB(self::obtenerConexion()));
    }
    public static function crearServicioUniversidad(): IServicioUniversidad {
        return new ServicioUniversidad(new RepositorioUniversidadMariaDB(self::obtenerConexion()));
    }
    public static function crearServicioObjetivoDesarrolloSostenible(): IServicioObjetivoDesarrolloSostenible {
        return new ServicioObjetivoDesarrolloSostenible(new RepositorioObjetivoDesarrolloSostenibleMariaDB(self::obtenerConexion()));
    }
    public static function crearServicioAreaAplicacion(): IServicioAreaAplicacion {
        return new ServicioAreaAplicacion(new RepositorioAreaAplicacionMariaDB(self::obtenerConexion()));
    }
    public static function crearServicioTerminoClave(): IServicioTerminoClave {
        return new ServicioTerminoClave(new RepositorioTerminoClaveMariaDB(self::obtenerConexion()));
    }
    public static function crearServicioLineaInvestigacion(): IServicioLineaInvestigacion {
        return new ServicioLineaInvestigacion(new RepositorioLineaInvestigacionMariaDB(self::obtenerConexion()));
    }
}