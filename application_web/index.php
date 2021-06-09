<?php
//Cette premiere version de l'application permet de choisir entre 2 options:
//1. Un formulaire pour appeler plusieurs crawlers sur un meme site ou service et envoyer les donnees vers une BDD locale ou a distance.
//2. Un autre formulaire pour faire une requete d'extraction de donnees depuis cette base.


    //On utilise une bdd locale dans le cadre de la demonstration.


    set_include_path('./src');
    require_once('Router.php');

    $router = new Router();

    $router->main();

?>