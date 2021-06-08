<?php
//Cette premiere version de l'application permet de choisir entre 2 options:
//1. Un formulaire pour appeler plusieurs crawlers sur un meme site ou service et envoyer les donnees vers une BDD locale ou a distance.
//2. Un autre formulaire pour faire une requete d'extraction de donnees depuis cette base.


    //On utilise une bdd locale dans le cadre de la demonstration.
    require_once('./config/localpostgresql_config.php');
    $dsn = "pgsql:host=".$MY_HOST.";port=".$MY_PORT.";dbname=".$MY_NAME;
    $db = new PDO($dsn, $MY_USER, $MY_PASSWORD);

    set_include_path('./src');
    require_once('Router.php');
    require_once('model/CrawlerPostgreSQL.php');
    require_once('model/TaskPostgreSQL.php');
    require_once('model/SessionPostgreSQL.php');
    require_once('model/CrawledTextPostgreSQL.php');
    $router = new Router();
    $crawlerStorage = new CrawlerPostgreSQL($db);
    $taskStorage = new TaskPostgreSQL($db);
    $sessionStorage = new SessionPostgreSQL($db);
    $crawledTextStorage = new CrawledTextPostgreSQL($db);
    $router->main($crawlerStorage, $taskStorage, $sessionStorage, $crawledTextStorage);

?>