<?php
//source : https://linuxhint.com/download_file_php/
if(isset($_GET['taskId']))
{   

    function apiCall($data, $method ,$endPoint){   
        $opts = array('http' =>
            array(
                'method'  => $method,
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $data
            )
        );
        $context  = stream_context_create($opts);
        $result = file_get_contents('http://192.168.1.47:81/api/' . $endPoint, false, $context);
        return json_decode($result,true);
    }

    set_include_path('../');

    //remplacer par appel api

    $dataList = apiCall('{"taskid": "' . $_GET['taskId'] . '"}','GET','donnees/getAllAssociatedData.php');
    

    //Faut specifier le chemin complet du cache (local)
    $url = "../../cache/tache" . $_GET['taskId'] . 'Export.json';
    file_put_contents($url, json_encode($dataList['data']));

    
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