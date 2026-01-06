<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'BBDD.php';

class User extends BBDD
{
    private $usersFile = __DIR__ . '/../data/users.json';

    public function __construct()
    {
        parent::connect();
    }

    public function getUsuarios()
    {
        if (file_exists($this->usersFile)) {
            $json = file_get_contents($this->usersFile);
            return json_decode($json, true);
        }
        return [];
    }

    public function getUsuario($id)
    {
        $users = $this->getUsuarios();
        foreach ($users as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }

    public function register($name, $apellido, $email, $passwd)
    {
        // First, insert into DB
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, apellido, email, passwd) 
                VALUES (:nombre, :apellido, :email, :passwd)");
        $stmt->bindParam(':nombre', $name);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':passwd', $passwd);
        $stmt->execute();
        $newId = $this->db->lastInsertId();

        // Then, update JSON
        $users = $this->getUsuarios();
        $users[] = [
            'id' => intval($newId),
            'nombre' => $name,
            'apellido' => $apellido,
            'email' => $email,
            'passwd' => $passwd,
            'is_admin' => 0
        ];
        file_put_contents($this->usersFile, json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public function delete()
    {
        $id = $_SESSION['id'];
        // Delete from DB
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);

        // Update JSON
        $users = $this->getUsuarios();
        $users = array_filter($users, function($user) use ($id) {
            return $user['id'] != $id;
        });
        $users = array_values($users); // reindex
        file_put_contents($this->usersFile, json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
