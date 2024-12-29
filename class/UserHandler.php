<?php
class Person {
    private $nom;
    private $prenom;
    private $password;
    private $specialite;
    private $matricule;
    private $groupe;
    private $email;
    private $typepersonne;

    public function __construct($nom, $prenom, $password, $specialite, $matricule, $groupe, $email, $typepersonne) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->password = $password;
        $this->specialite = $specialite; 
        $this->matricule = $matricule;
        $this->groupe = $groupe;
        $this->email = $email;
        $this->typepersonne = $typepersonne;
    }

    public function save($conn) {
        $stmt = $conn->prepare("INSERT INTO user (matricule, nom, prenom, email, `group`, specialite, password, id_typeperson) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt === false) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
    
        $stmt->bind_param("ssssssss", $this->matricule, $this->nom, $this->prenom, $this->email, $this->groupe, $this->specialite, $this->password, $this->typepersonne);
    
        if ($stmt->execute()) {
            return $stmt->insert_id; 
        } else {
            throw new Exception("Error inserting record: " . $stmt->error);
        }
    
        $stmt->close();
    }

    public static function login($conn, $matricule, $password) {
        $stmt = $conn->prepare("SELECT ID FROM user WHERE matricule = ? AND password = ?");
        if ($stmt === false) {
            die("Failed to prepare statement: " . $conn->error);
        }
    
        $stmt->bind_param("ss", $matricule, $password);
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

    public static function getNomPrenomByMatricule($conn, $matricule) {
        $nom=null;
        $prenom=null;
        $specialite=null;
        
        $stmt = $conn->prepare("SELECT nom, prenom, specialite FROM user WHERE matricule = ?");
        if ($stmt === false) {
            die("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("s", $matricule); 
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($nom, $prenom,$specialite);
            $stmt->fetch(); 

            return ['nom' => $nom, 'prenom' => $prenom , 'specialite' => $specialite]; 
        } else {
            return null;
        }

        $stmt->close();
    }

 ////////////////////////////////////////////////////////////////////////////
//          R E L A T E D - T O -  D O C U M E N T S                       //
////////////////////////////////////////////////////////////////////////////


    public static function savedocument($conn, $title, $created_at, $id_user, $document_status) {   
        try {
            
            $stmt = $conn->prepare("INSERT INTO document (title, created_at, id_user, document_status) VALUES (?, ?, ?, ?)");
    
            
            $stmt->bind_param("ssis", $title, $created_at, $id_user, $document_status);
    
           
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } finally {
            if (isset($stmt)) {
                $stmt->close();                   
            }
        }
    }

    public static function getUserIdByMatricule($conn, $matricule) {

        $stmt = $conn->prepare("SELECT ID FROM user WHERE matricule = ?");
        
       
        $stmt->bind_param("s", $matricule);
        
        
        $stmt->execute();
        
        
        $result = $stmt->get_result();
        
        
        if ($row = $result->fetch_assoc()) {
            return $row['ID'];
        } else {
            
            throw new Exception("No user found with the given matricule.");
        }
    
        
        $stmt->close();
    }

    public static function fetchDocumentRequests($conn, $id_user) {
        $stmt = $conn->prepare("SELECT id_document, title, created_at, document_status FROM document WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $documents = [];
        while ($row = $result->fetch_assoc()) {
            $documents[] = $row;
        }
        return $documents;
    }


 ///////////////////////////////////////////////////////////////////////
 //           R E L A T E D - T O - R O O M S                         //
////////////////////////////////////////////////////////////////////////

public static function saveRoomRequest($conn, $num_salle, $created_at, $request_start_time, $request_end_time, $id_user, $sale_status) {
    // Check if the room is already reserved in the requested time slot
    if (self::isRoomReserved($conn, $num_salle, $request_start_time, $request_end_time)) {
        return "Error: The room is already reserved during the requested time slot.";
    }

    try {
        
        $stmt = $conn->prepare("INSERT INTO salle (num_salle, created_at, request_start_time, request_end_time, id_user, sale_status) VALUES (?, ?, ?, ?, ?, ?)");

        
        $stmt->bind_param("ssssis", $num_salle, $created_at, $request_start_time, $request_end_time, $id_user, $sale_status);

        
        if ($stmt->execute()) {
            return true;
        } else {
            return "Error: Failed to submit the room request.";
        }
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}


public static function fetchRoomRequests($conn, $id_user) {
    try {
       
        $stmt = $conn->prepare("SELECT id_salle, num_salle, created_at, request_start_time, request_end_time, sale_status FROM salle WHERE id_user = ?");

        
        $stmt->bind_param("i", $id_user);

        
        $stmt->execute();

        
        $result = $stmt->get_result();

        
        $roomRequests = [];
        while ($row = $result->fetch_assoc()) {
            $roomRequests[] = $row;
        }
        return $roomRequests;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}


public static function isRoomReserved($conn, $num_salle, $request_start_time, $request_end_time) {
    try {
        // Prepare the SQL query to check for overlapping reservations
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS count 
            FROM salle 
            WHERE num_salle = ? 
            AND (
                (request_start_time <= ? AND request_end_time > ?) OR 
                (request_start_time < ? AND request_end_time >= ?) OR 
                (request_start_time >= ? AND request_end_time <= ?)
            )
        ");

        
        $stmt->bind_param("sssssss", 
            $num_salle, 
            $request_end_time, $request_start_time, 
            $request_start_time, $request_end_time, 
            $request_start_time, $request_end_time
        );

       
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Return true if there is at least one conflicting reservation
        return $row['count'] > 0;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
} 


 ///////////////////////////////////////////////////////////////////////
//                  R E L A T E D - T O - T O O L S                  ///
////////////////////////////////////////////////////////////////////////

public static function fetchMaterielRequests($conn, $id_user) {
    try {
        
        $stmt = $conn->prepare("SELECT id_materiel, type_materiel, created_at, request_start_time, request_end_time, status FROM materiel WHERE id_user = ?");

        
        $stmt->bind_param("i", $id_user);

        
        $stmt->execute();

       
        $result = $stmt->get_result();

        
        $materielRequests = [];
        while ($row = $result->fetch_assoc()) {
            $materielRequests[] = $row;
        }
        return $materielRequests;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}


public static function saveMaterielRequest($conn, $type_materiel, $created_at, $request_start_time, $request_end_time, $id_user, $status) {
    // Check if the tool is already reserved during the requested time slot
    if (self::isMaterielReserved($conn, $type_materiel, $request_start_time, $request_end_time)) {
        return "Error: The material is already reserved during the requested time slot.";
    }

    try {
        
        $stmt = $conn->prepare("INSERT INTO materiel (type_materiel, created_at, request_start_time, request_end_time, id_user, status) 
                               VALUES (?, ?, ?, ?, ?, ?)");

       
        $stmt->bind_param("ssssss", $type_materiel, $created_at, $request_start_time, $request_end_time, $id_user, $status);

        
        if ($stmt->execute()) {
            return true;
        } else {
            return "Error: Failed to submit the material request.";
        }
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

public static function isMaterielReserved($conn, $type_materiel, $request_start_time, $request_end_time) {
    try {
        // Prepare the SQL query to check for overlapping tool requests
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS count 
            FROM materiel 
            WHERE type_materiel = ? 
            AND (
                (request_start_time <= ? AND request_end_time > ?) OR 
                (request_start_time < ? AND request_end_time >= ?) OR 
                (request_start_time >= ? AND request_end_time <= ?)
            )
        ");

        
        $stmt->bind_param("sssssss", 
            $type_materiel, 
            $request_end_time, $request_start_time, 
            $request_start_time, $request_end_time, 
            $request_start_time, $request_end_time
        );

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Return true if there is at least one conflicting reservation
        return $row['count'] > 0;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}
public static function getUserDocuments($conn) {
    // SQL query to join user and documents tables
    $sql = "
        SELECT 
            u.matricule, 
            u.nom, 
            u.prenom, 
            u.group, 
            u.specialite, 
            u.email, 
            d.title, 
            d.created_at, 
            d.document_status,
            d.id_document
        FROM 
            user u
        INNER JOIN 
            document d
        ON 
            u.id = d.id_user
    ";

   
    $stmt = $conn->prepare($sql);
    
    
    if ($stmt === false) {
        die("Failed to prepare statement: " . $conn->error);
    }

    
    $stmt->execute();
    
   
    $result = $stmt->get_result();
    
   
    $documents = [];
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }

    
    $stmt->close();

    // Return the results
    return $documents;
}

static public function getUserMaterials($conn) {
    // SQL query to join user and type_materiel tables
    $sql = "
        SELECT 
            u.matricule, 
            u.nom, 
            u.prenom, 
            u.group, 
            u.specialite, 
            u.email, 
            m.id_materiel, 
            m.type_materiel, 
            m.created_at, 
            m.request_start_time, 
            m.request_end_time, 
            m.status
        FROM 
            user u
        INNER JOIN 
            materiel m 
        ON 
            u.id = m.id_user
    ";
    
    
    $stmt = $conn->prepare($sql);
    
    
    if ($stmt === false) {
        die("Failed to prepare statement: " . $conn->error);
    }

    
    $stmt->execute();
    
    
    $result = $stmt->get_result();
    
   
    $materiel = [];
    while ($row = $result->fetch_assoc()) {
        $materiel[] = $row;
    }

   
    $stmt->close();

    
    return $materiel;
}
static public function salle($conn, $order = 'DESC') {
    try {
        // Ensure the order is either DESC (recent first) or ASC (oldest first)
        if ($order !== 'ASC' && $order !== 'DESC') {
            $order = 'DESC'; // Default to DESC if invalid value
        }

        // SQL query to join user and salle tables, ordered by request_start_time in the specified direction
        $sql = "
            SELECT 
                u.matricule, 
                u.nom, 
                u.prenom, 
                u.group, 
                u.specialite, 
                u.email, 
                u.niveau,
                s.num_salle, 
                s.request_start_time, 
                s.request_end_time, 
                s.id_salle,
                s.sale_status
            FROM 
                user u
            INNER JOIN 
                salle s 
            ON 
                u.id = s.id_user
            ORDER BY 
                s.request_start_time $order
        ";
        
        $stmt = $conn->prepare($sql);

        
        if ($stmt === false) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        
        $stmt->execute();

       
        $result = $stmt->get_result();
        
        
        $sale = [];
        while ($row = $result->fetch_assoc()) {
            $sale[] = $row;
        }

        
        $stmt->close();

        
        return $sale;
    } catch (Exception $e) {
        
        error_log("Error in salle function: " . $e->getMessage());
        return [];
    }
}


public static function getUserTypePersonByID($conn, $id) {
   
    $sql = "SELECT id_typeperson FROM user WHERE ID = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error preparing the statement: " . $conn->error);
    }

    
    $stmt->bind_param("i", $id);

    
    if ($stmt->execute()) {
       
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            
            $row = $result->fetch_assoc();
            return $row['id_typeperson'];
        } else {
            // ID not found
            return null;
        }
    } else {
        
        die("Error executing the query: " . $stmt->error);
    }

    
    $stmt->close();
}
public static function checkAdminCredentials($conn, $username, $password) {
    
    $sql = "SELECT * FROM admin WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);

    
    $stmt->bind_param("ss", $username, $password);

   
    $stmt->execute();

    
    $stmt->store_result();

    // Check if any rows were returned (meaning credentials are valid)
    if ($stmt->num_rows > 0) {
        // Credentials are valid, return true
        $stmt->close();
        return true;
    } else {
        // No matching credentials found, return false
        $stmt->close();
        return false;
    }
}}
