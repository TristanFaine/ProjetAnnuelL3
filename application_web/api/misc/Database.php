<?php

//connexion à la base de donnée

class Database{
    private $host = "localhost";
    private $db_name = "testdb";
    private $port = "5432";
    private $username = "postgres";
    private $password = "W4htgafo94tA";
    public $connexion; //propriété public


    //getter de connexion

    public function getConnexion(){// méthode de connexion

        $this->connexion=null;

        try{ // pour gérer les erreurs
            $dsn = "pgsql:host=".$this->host.";port=".$this->port.";dbname=".$this->db_name;
            
            $this->connexion = new PDO($dsn, $this->username, $this->password);
            
        }
        catch(PDOException $exception){
            echo "Erreur de connexion : " .$exception->getMessage();
        }

        return $this->connexion;
    }



}


