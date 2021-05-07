<?php
//Cette premiere version de l'application permet de choisir entre 2 options:
//1. Un formulaire pour appeler le script de crawling correspondant, et envoyer les donnees vers la BDD postgres
//2. Un autre formulaire pour faire une requete de dump de la BDD Postgres.
//Une ebauche d'architecture MVC est en cours de developpement.

//Mise en place d'une architecture MVC:

    //On utilise une bdd locale dans le cadre de la demonstration.
    require_once('./api/config/localpostgresql_config.php');
    $dsn = "pgsql:host=".$MY_HOST.";port=".$MY_PORT.";dbname=".$MY_NAME;
    $db = new PDO($dsn, $MY_USER, $MY_PASSWORD);


    
    //Authentification apres selection de source, on suppose.

    set_include_path('./src');

    require_once('Router.php');
    require_once('model/CrawlerPostgreSQL.php');
    require_once('model/TaskPostgreSQL.php');
    //require_once('model/CrawledTextPostgreSQL.php');
    
    $router = new Router();
    //on a besoin des informations sur les crawlers disponibles, les taches de ces crawlers, et des donnees deja recuperees.
    $crawlerStorage = new CrawlerPostgreSQL($db);
    $taskStorage = new TaskPostgreSQL($db);
    //$crawledtextStorage = new CrawledTextMySQL($db);
    //$crawlerStorage, $taskStorage, $crawledtextStorage
    $router->main($crawlerStorage, $taskStorage);

?>