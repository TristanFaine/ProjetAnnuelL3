<?php
class Donnees{
    // Connexion à la base de donnée
    private $connexion;

    private $table = "donnees";

    // propriétés des objets
    public  $status;
    public  $entryPoint;
    public  $begin;
    public  $end;

    

    /*
       * @param $db
     */
    public function __construct($db){ //constructeur avec db de connexion à la base de donnée
        $this->connexion = $db;
    }

   
    public function inserer(){ //méthode
        
        //écrire la requete d'insertion
        $sql = "INSERT INTO " . $this->table . "(status, entryPoint, begin, end) VALUES('" . $this->status . "','" . $this->entryPoint . "','" . $this->begin . "','" . $this->end .")";
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
        $sql = "DELETE FROM " . $this->table . "(status, entryPoint, begin, end) WHERE ('" . $this->status . "','" . $this->entryPoint . "','" . $this->begin . "','" . $this->end .")";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return true;
        }
        return false;
    }
}