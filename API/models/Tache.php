<?php
class Tache{
    // Connexion à la base de donnée
    private $connexion;

    private $table = "tache";

    // propriétés des objets
    public int $id;
    public int $crawlerID;
    public int $status;
    public string $entrypoint;
    public int $begindate;
    public int $enddate;
    public int $datalimit;
    

    /*
       * @param $db
     */
    public function __construct($db){ //constructeur avec db de connexion à la base de donnée
        $this->connexion = $db;
    }

    public function read(){
        //écrire la requete d'insertion
        $sql = "SELECT * FROM " . $this->table . " WHERE id = '" . $this->id . "'";
        // préparation à la requete (objet PDO avec ses méthodes prepare et execute)
        $query = $this->connexion->prepare($sql);

        // Exécution de la requête
        if($query->execute()){
            return $query->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function readAll(){
		//écrire la requete
        $sql = "SELECT * FROM " . $this->table;
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
        $sql = "INSERT INTO " . $this->table . "(crawlerid, status, entrypoint, begindate, enddate, datalimit) VALUES('" .
         $this->crawlerid . "','" . $this->status . "','" . $this->entrypoint . "','" . $this->begindate .
         "','" . $this->enddate . "','" . $this->datalimit . "')";
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
        "' ,status = '" . $this->status .
        "' ,entrypoint = '" . $this->entrypoint .
        "' ,begindate = '" . $this->begindate .
        "' ,enddate = '" . $this->enddate .
        "' ,datalimit = '" . $this->datalimit .
        "' WHERE id = '" . $this->id . "'";
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
    
    
    public function readAllByCrawlerId(){
		$sql = "SELECT * FROM tache WHERE crawlerid = '" . $this->crawlerID . "'";
		$query = $this->connexion->prepare($sql);
		if($query->execute()){
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
	}
	
	
	

}
