<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'BBDD.php';

class User extends BBDD
{
    public function __construct()
    {
        parent::connect();
    }

    public function getUsuarios()
    {
        $query = "SELECT * FROM usuarios";
        $stmt = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        return $stmt;
    }

    public function getUsuario($id)
    {
        $query = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    }

    public function register($name, $apellido, $email, $passwd)
    {
        // $hashedPassword = password_hash($passwd, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, apellido, email, passwd) 
                VALUES (:nombre, :apellido, :email, :passwd)");
        $stmt->bindParam(':nombre', $name);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':passwd', $passwd);
        $stmt->execute();
    }

    public function delete()
    {
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $_SESSION['id']]);
    }
}
