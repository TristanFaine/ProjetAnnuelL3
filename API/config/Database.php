<?php

//connexion à la base de donnée

class Database{
    private $host = "localhost";
    //private $db_name = "api";
    //private $username = "root";
    //private $password = "";
    public $connexion; //propriété public


    //getter de connexion

    public function getConnexion(){// méthode de connexion

        $this->connexion=null;

        try{ // pour gérer les erreurs
            $this->connexion= new PDO("mysql:host=" . $this->host );

            $this->connexion->exec("set names utf8");


        }
        catch(PDOException $exception){
            echo "Erreur de connexion : " .$exception->getMessage();
        }

        return $this->connexion;
    }



}


