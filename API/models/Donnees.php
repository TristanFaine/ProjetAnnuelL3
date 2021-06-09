<?php
class Donnees{
    // Connexion à la base de donnée
    private $connexion;

    private $table = "crawledtext";

    // propriétés des objets
    public  $text;
    public  $path;
    public  $index;
    public  $realid;
    public  $taskid;

    

    /*
       * @param $db
     */
    public function __construct($db){ //constructeur avec db de connexion à la base de donnée
        $this->connexion = $db;
    }

   
    public function inserer(){ //méthode
        
        //écrire la requete d'insertion
        $sql = "INSERT INTO " . $this->table . "(text, path, index, realid, taskid) VALUES('" .
         $this->text . "','" . $this->path . "','" . $this->index . "','" . $this->realid . "','" . $this->taskid .")";
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
        $sql = "UPDATE " . $this->table .
        " SET crawlerid = '" . $this->crawlerID .
        "' text = '" . $this->text .
        "' path = '" . $this->path .
        "' index = '" . $this->index .
        "' realid = '" . $this->realid .
        "' taskid = '" . $this->taskid . "'";
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return true;
        }
        return false;
    }


    public function supprimer(){ //méthode
        
        //écrire la requete d'insertion
        $sql = "DELETE from " . $this->table . " where id = '" . $this->id . "'";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return true;
        }
        return false;
    }


    public function getLastKnownData(){
        //écrire la requete d'insertion
        $sql = "SELECT realid from " . $this->table . " where id = ( SELECT MAX(id) FROM crawledtext WHERE taskid = '" . $this->taskid . "')";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return $query->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function getAllAssociatedData(){
        //écrire la requete d'insertion
        $sql = "SELECT * from " . $this->table . " WHERE taskid = '" . $this->taskid . "'";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
    }





}