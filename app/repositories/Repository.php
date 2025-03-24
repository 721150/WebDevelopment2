<?php
namespace App\Repositories;

use PDO;
use PDOException;

class Repository {
    protected PDO $connection;

    function __construct() {
        require __DIR__ . '/../config/dbconfig.php';

        try {
            $this->connection = new PDO("$type:host=$servername;dbname=$database", $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            throw $exception;
        }
    }
}
?>