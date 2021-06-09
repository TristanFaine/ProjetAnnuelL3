<?php
// Headers requis
header("Access-Control-Allow-Origin: *"); //accès à l'API par tous (*)
header("Content-Type: application/json; charset=UTF-8"); // envoi une réponse en json
header("Access-Control-Allow-Methods: DELETE"); // méthode accepté pour la requete pour insérer
header("Access-Control-Max-Age: 3600"); // durée de la requete
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); // headers qu'on autorise (filtrage)

// On vérifie la méthode
if($_SERVER['REQUEST_METHOD'] == 'DELETE'){
    // On inclut les fichiers de configuration et d'accès aux données
    include_once '../config/Database.php';
    
    include_once '../models/Session.php';

    // On instancie la base de données
    $database = new Database();
    $db = $database->getConnexion();

    // On instancie les taches
    $session = new Session($db);

    // On récupère les informations envoyées
    $donnees = json_decode(file_get_contents("php://input"));

    

    $session->token = $donnees->token;

    //On met les informations dans l'objet taches
    $deleteAttempt = $session->deleteByToken();

    if($deleteAttempt){
        http_response_code(201);
        echo json_encode(["message" => "SUCCESS : Effacement de la donnee"]);
    }else  {
        http_response_code(503);
        echo json_encode(["message" => "ERROR : Echec d'effacement de la donnee"]);         
    }

}else

{
    // On gère l'erreur
    http_response_code(405);
    echo json_encode(["message" => "La méthode n'est pas autorisée"]);
}
