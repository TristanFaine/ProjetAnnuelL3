<?php
//source : https://linuxhint.com/download_file_php/
if(isset($_GET['taskId']))
{

    //TODO: Remplacer cela par un appel API car bon quand meme..
    require_once('../../config/localpostgresql_config.php');
    $dsn = "pgsql:host=".$MY_HOST.";port=".$MY_PORT.";dbname=".$MY_NAME;
    $db = new PDO($dsn, $MY_USER, $MY_PASSWORD);

    set_include_path('../');
    require_once('model/CrawledTextPostgreSQL.php');
    $crawledTextStorage = new CrawledTextPostgreSQL($db);


    $dataList = $crawledTextStorage->readAllAssociatedData($_GET['taskId']);
    



    //Pour illusion, faut specifier le chemin complet
    $url = realpath("../../cache/tache" . $_GET['taskId'] . 'Export.json');

    echo $url;

    file_put_contents($url, json_encode($dataList));


    //Effacer le cache
    clearstatcache();

    //Si le fichier existe
    if(file_exists($url)) {

        //Definir informations header
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($url).'"');
        header('Content-Length: ' . filesize($url));
        header('Pragma: public');

        //Vide le tampon d'ecriture
        flush();

        //Envoyer le fichier vers dossier de telechargement qu'utilisera le navigateur
        readfile($url,true);

        die();
    }
    else{
        echo "Ce fichier n'existe pas";
    }
} else {
    echo "Aucun fichier n'a ete defini";
}


?>