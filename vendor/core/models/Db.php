<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use PDOException;
use Dcs\Vendor\Core\Models\DcsException;
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING) . "/app/dcs_const.php");

class Db
{
    private static $_instance = null;
    protected $pdo;
    private function __construct () {
        $connection_string = "pgsql:host=".DCS_DBHOST.";port=5432;dbname=" . DCS_DBNAME . ";";
        try {
            $this->pdo = new PDO($connection_string, DCS_DBUSER, DCS_DBPASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $ex) {
            throw new DcsException($ex->getMessage(),DCS_ERROR_DB,$ex);
        }
    }

    private function __clone () {}
    private function __wakeup () {}

    public static function getInstance()
    {
        if (self::$_instance === null) { // если экземпляр данного класса  не создан
            self::$_instance = new self;  // создаем экземпляр данного класса
        }
        return self::$_instance; // возвращаем экземпляр данного класса
    }
  // a proxy to native PDO methods
    public function __call($method, $args = [])
    {
        try {
            return call_user_func_array(array($this->pdo, $method), $args);
        } catch (PDOException $ex) {
            throw new DcsException($ex->getMessage().' method: '.$method.' args:'.print_r($args,TRUE),DCS_ERROR_DB,$ex);
        }
    }

    // a helper function to run prepared statements smoothly
    public function run($sql, $args = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($args);
        } catch (PDOException $ex) {
            throw new DcsException($ex->getMessage().' sql: '.$sql.' args:'.print_r($args,TRUE),DCS_ERROR_DB,$ex);
        }
        return $stmt;
    }
}