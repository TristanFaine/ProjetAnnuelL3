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

}
