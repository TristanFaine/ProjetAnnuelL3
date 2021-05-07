<?php

//Ce script permet d' executer les taches 1 par 1, en arriere-plan, et on renvoie les donnees JSON en array PHP vers la telecommande.

//Recevoir les donnees envoyees via POST (liste d'id de taches + source + token):

//ne rien faire si token invalide (ou n'existe pas), ou alors echo TOKEN INVALID et faire quelque chose avec la telecommande.



//Appeler chaque tache selon informations recues:




// Preparer les donnees
$results = array(
    'value' => 'hello',
    'status' => 'done',
    'errors' => 'aucune on espere',
    'count' => 20,
    'data' => 'je serais un array',
    'source' => $_POST['source'],
    'taskIdArray' => $_POST['taskIdArray']
);



//Envoyer les donnees vers le serveur
echo json_encode($results);
