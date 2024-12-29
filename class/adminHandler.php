<?php
class Admin {
    private $nom;
    private $prenom;
    private $password;
    private $username;

    public function __construct($nom, $prenom, $password, $username) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->password = $password;
        $this->username = $username;
    }

    public static function loginA($conn, $username, $password) {
        $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ? AND password = ?");
        if ($stmt === false) {
            die("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $stmt->store_result();

        $id = null;
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id);
            $stmt->fetch();
        }

        $stmt->close();
        return $id ? $id : false;
    }


    public static function getNomPrenomByUsername($conn, $username) {
        $stmt = $conn->prepare("SELECT nom, prenom FROM admin WHERE username = ?");
        if ($stmt === false) {
            die("Failed to prepare statement: " . $conn->error);
        }
    
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
    
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($nom, $prenom);
            $stmt->fetch();
            return ['nom' => $nom, 'prenom' => $prenom];
        } else {
            return null;
        }
    
        $stmt->close();
    }
    
}
?>
