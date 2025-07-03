<?php
// Arquivo de configuração e conexão com o banco de dados

class Database {
    // Credenciais do banco de dados
    private $host = "localhost";
    private $db_name = "bank"; // Nome do banco de dados
    private $username = "root"; // Usuário do banco
    private $password = ""; // Senha do banco
    public $conn;

    // Método para obter a conexão
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
