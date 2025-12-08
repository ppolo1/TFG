<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

class BBDD
{
    private $cad_conect = 'mysql:dbname=tfg;host=localhost';
    private $user = 'root';
    private $pass = '';
    public $db;

    public function connect()
    {
        try {
            $this->db = new PDO($this->cad_conect, $this->user, $this->pass);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
            exit;
        }
    }
}
