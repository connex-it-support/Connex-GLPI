<?php
class android_login_connect {
    private $conn;
 
    // Connecting to database
    public function connect() {
        require_once 'android_login_config.php';
 
        // Connecting to mysql database
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        // return database object
        return $this->conn;
    }
}
?>