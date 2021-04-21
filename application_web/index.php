<?php
//Cette premiere version de l'application permet de choisir entre 2 options:
//1. un formulaire (faire joli plus tard) pour appeler le script de crawling correspondant.
//2. Un autre formulaire pour faire une requete vers la BDD Postgres.

    //set_include_path('./src');
    //require_once('Router.php');

    //$db = "none yet";
    //$router = new Router();

//On essayera de faire mieux que juste des GET pour faire notre application..

//ignorons l'aspect application et regardons si la logique d'appel de scripts fonctionne.


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>La meilleure page web de l'univers</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
</head>

<body>
	<h1>Page d'acceuil</h1>
    <h2>Formulaire d'envoi de donnees</h2>
    <form action="crawl.php" method="get">
    <label for="source">Selectionner une source de donnees :</label>
    <select id="source" name="source">
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
    <label for="other">Pas encore disponible, mais source (ne rien marquer si dump de toute la BDD) :</label>
    <input type="text" name="other"/>
    <br/>
    <input type="submit" value="Submit"/>
    </form>

    <h3 id="pretty"> Bon dans la version non-prototype on utilisera un routeur.</h3>

</body>

</html>
