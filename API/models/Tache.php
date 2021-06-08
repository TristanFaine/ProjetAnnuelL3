<?php
class Tache{
    // Connexion à la base de donnée
    private $connexion;

    private $table = "tache";

    // propriétés des objets
    public string $text;
    public string $path;
    public int $index;
    public int $realID;
    

    /*
       * @param $db
     */
    public function __construct($db){ //constructeur avec db de connexion à la base de donnée
        $this->connexion = $db;
    }

   
    public function inserer(){ //méthode
        
        //écrire la requete d'insertion
        $sql = "INSERT INTO " . $this->table . "(text, path, index, realID) VALUES('" . $this->text . "','" . $this->path . "','" . $this->index . "','" . $this->realID .")";
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
        $sql = "UPDATE " . $this->table . "(text, path, index, realID) VALUES('" . $this->text . "','" . $this->path . "','" . $this->index . "','" . $this->realID .")";
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
        $sql = "DELETE " . $this->table . "(text, path, index, realID) WHERE('" . $this->text . "','" . $this->path . "','" . $this->index . "','" . $this->realID .")";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return true;
        }
        return false;
    }
}