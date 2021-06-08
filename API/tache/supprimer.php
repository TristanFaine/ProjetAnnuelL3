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
    include_once '../models/Tache.php';

    // On instancie la base de données
    $database = new Database();
    $db = $database->getConnexion();

    // On instancie les taches
    $taches = new Tache($db);

    // On récupère l'id de la tache
    $donnees = json_decode(file_get_contents("php://input"));

    if(!empty($donnees->id)){
        $taches->id = $donnees->id;

        if($taches->supprimer()){ // Ici la suppression a fonctionné
            
           
            http_response_code(201);  // On envoie un code 201
            echo json_encode(["message" => "La suppression a été effectuée"]);

        }else // si la suppression n'a pas fonctionné
        
        {
            
           
            http_response_code(503);  // On envoie un code 503
            echo json_encode(["message" => "La suppression n'a pas été effectuée"]);         
        }
    }

}else

{
    // On gère l'erreur
    http_response_code(405);
    echo json_encode(["message" => "La méthode n'est pas autorisée"]);
}
