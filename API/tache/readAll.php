<?php
// Headers requis
header("Access-Control-Allow-Origin: *"); //accès à l'API par tous (*)
header("Content-Type: application/json; charset=UTF-8"); // envoi une réponse en json
header("Access-Control-Allow-Methods: POST"); // méthode accepté pour la requete pour insérer
header("Access-Control-Max-Age: 3600"); // durée de la requete
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); // headers qu'on autorise (filtrage)

// On vérifie la méthode
if($_SERVER['REQUEST_METHOD'] == 'GET'){
    // On inclut les fichiers de configuration et d'accès aux données
    include_once '../config/Database.php';
    include_once '../models/Tache.php';

    // On instancie la base de données
    $database = new Database();
    $db = $database->getConnexion();

    // On instancie les taches
    $taches = new Tache($db);

    // On récupère les informations envoyées
    $donnees = json_decode(file_get_contents("php://input"));
    
    if(!$taches->readAll()){ // si la lecture n'a pas fonctionné
        
        http_response_code(503);  // On envoie un code 503
        echo json_encode(["message" => "Erreur : Recuperation des informations impossible"]);
    }else {
            $datumAttributes = $taches->readAll();
            $datum = [];
            foreach($datumAttributes as $dataAttributes){
                $datum[] = $dataAttributes;
            }
            http_response_code(201);  // On envoie un code 201
            echo json_encode(["message" => "Lecture reussie", "data" => $datum]);     
        }
    
    

}else

{
    // On gère l'erreur
    http_response_code(405);
    echo json_encode(["message" => "La méthode n'est pas autorisée"]);
}
