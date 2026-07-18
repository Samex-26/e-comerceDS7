<?php
// Clase base abstracta para todos los modelos.
// Centraliza la conexión PDO para que las clases hijas accedan a $this->db.

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}
