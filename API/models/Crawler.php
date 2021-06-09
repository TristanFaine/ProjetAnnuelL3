<?php

//Effectuer des actions sur la BDD.

class Crawler{
    // Connexion à la base de donnée
    private $connexion;

    private $table = "crawler";

    // propriétés des objets
    public $source;
    public $folderchecksum;
    

    /*
       * @param $db
     */
    public function __construct($db){ //constructeur avec db de connexion à la base de donnée
        $this->connexion = $db;
    }
    
    
    public function read(){
		//écrire la requete
        $sql = "SELECT * FROM crawler WHERE id = '" . $this->id . "'";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
		
	}
	
	public function readAll(){
		//écrire la requete
        $sql = "SELECT * FROM crawler";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
	}


    public function inserer(){ //méthode
        
        //écrire la requete d'insertion
        $sql = "INSERT INTO " . $this->table . "(source, folderchecksum) VALUES('" . $this->source . "','" . $this->folderchecksum ."')";
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
        $sql = "UPDATE " . $this->table . " SET source = '" . $this->source . "', folderchecksum = '" . $this->folderchecksum  . "' WHERE id = " . $this->id;
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
        $sql = "DELETE FROM " . $this->table . " WHERE id = '" . $this->id . "'";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return true;
        }
        return false;
    }

}
