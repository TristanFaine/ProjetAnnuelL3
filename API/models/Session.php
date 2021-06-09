<?php
class Session{
    // Connexion à la base de donnée
    private $connexion;

    private $table = "session";

    // propriétés des objets
    public  $status;
    public  $token;

    /*
       * @param $db
     */
    public function __construct($db){ //constructeur avec db de connexion à la base de donnée
        $this->connexion = $db;
    }



    public function deleteByToken(){ //méthode
        $sql = "DELETE FROM " . $this->table . " WHERE token = '" . $this->token . "'";
        $query = $this->connexion->prepare($sql);
        // Exécution de la requête
        if($query->execute()){
            return true;
        }
        return false;
    }
   

    public function create(){ //méthode
        
        //écrire la requete d'insertion
        //faire bin2hex pour avoir un nouveau token
        $randomValue = bin2hex(random_bytes(16));
        $sql = "INSERT INTO " . $this->table . "(token) VALUES('" . $randomValue ."')";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return $randomValue;
        }
        return false;

    }
    public function inserer(){ //méthode
        
        //écrire la requete d'insertion
        $sql = "INSERT INTO " . $this->table . "(token) VALUES('" . $this->token ."')";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return true;
        }
        return false;
    }


    public function modifier(){ //méthode
        
        //écrire la requete d'insertion
        $sql = "UPDATE " . $this->table . "(status, entryPoint, begin, end) VALUES('" . $this->status . "','" . $this->entryPoint . "','" . $this->begin . "','" . $this->end .")";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        
        
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return true;
        }
        return false;
    }


    public function supprimer(){ //méthode
        
        //écrire la requete d'insertion
        $sql = "DELETE FROM " . $this->table . " WHERE token = '" . $this->token . "'";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return true;
        }
        return false;
    }
}