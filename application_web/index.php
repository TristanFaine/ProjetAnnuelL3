<?php
//Cette premiere version de l'application permet de choisir entre 2 options:
//1. Un formulaire pour appeler le script de crawling correspondant, et envoyer les donnees vers la BDD postgres
//2. Un autre formulaire pour faire une requete de dump de la BDD Postgres.
//Une ebauche d'architecture MVC est en cours de developpement.

//Mise en place d'une architecture MVC:

    require('dump/postgresql_config.php');
    //^mettre le vrai chemin ../../private ou autre

    set_include_path('./src');

    require_once('Router.php');
    require_once('model/CrawlerPostgreSQL.php');
    require_once('model/TaskPostgreSQL.php');
    require_once('model/CrawledTextPostgreSQL.php');
    $dsn = 'mysql:host='.$POSTGRESQL_HOST.';port=.'.$POSTGRESQL_PORT.';dbname='.$POSTGRESQL_DB.';charset=utf8mb4';
    //$db = new PDO($dsn, $POSTGRESQL_USER, $POSTGRESQL_PASSWORD);
    
    
    
    $router = new Router();
    //on a besoin des informations sur les crawlers disponibles, les taches de ces crawlers, et des donnees deja recuperees.
    //$crawlerStorage = new CrawlerStoragePostgreSQL($db);
    //$taskStorage = new TaskStoragePostgreSQL($db);
    //$crawledtextStorage = new CrawledTextStorageMySQL($db);
    //$crawlerStorage, $taskStorage, $crawledtextStorage
    $router->main();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>Acceuil: Application Crawler Incremental</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
</head>

<body>
	<h1>Bienvenue sur le prototype de la page d'acceuil</h1>
    <h2>Formulaire d'envoi de donnees</h2>
    <form action="crawl.php" method="post">
    <label for="source">Selectionner une source de donnees :</label>
    <select id="source" name="source">
        <option value="Test">Test</option>
        <option value="Reddit">Reddit</option>
        <option value="Discord">Discord</option>
        <option value="Quora">Quora</option>
    </select> 
    <br/>
    <label for="limit">Limite de pages/commentaires/autre : (0 = pas de limite)</label>
    <input type="number" name="limit" value="0"/>
    <br/>
    <label for="aux">Arguments supplementaires : (Separer par un "|")</label>
    <input type="text" name="aux"/>
    <br/>
    <input type="submit" value="Submit"/>
    </form>


    <h2>Formulaire de reception de donnees</h2>
    <form action="get.php" method="get">
    <label for="other">Pas encore disponible, mais veuillez indiquer le type de source (Reddit,Discord... ne rien marquer si dump de toute la BDD) :</label>
    <input type="text" name="other"/>
    <br/>
    <input type="submit" value="Submit"/>
    </form>

    <h3 id="pretty"> Bon dans la version non-prototype on utilisera un routeur/MVC.</h3>

</body>

</html>
