<?php
// Headers requis
header("Access-Control-Allow-Origin: *"); //accès à l'API par tous (*)
header("Content-Type: application/json; charset=UTF-8"); // envoi une réponse en json
header("Access-Control-Allow-Methods: POST"); // méthode accepté pour la requete pour insérer
header("Access-Control-Max-Age: 3600"); // durée de la requete
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); // headers qu'on autorise (filtrage)

// On vérifie la méthode
if($_SERVER['REQUEST_METHOD'] == 'POST'){
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

    // Ici on a reçu les données
    if(!empty($donnees->text) && !empty($donnees->path) && !empty($donnees->index) && !empty($donnees->realID) ){
    
        //On met les informations dans l'objet taches
        $taches->text = $donnees->text;
        $taches->path = $donnees->path;
        $taches->index = $donnees->index;
        $taches->realID = $donnees->realID;
        if($taches->inserer()){ // Ici la création a fonctionné
            
           
            http_response_code(201);  // On envoie un code 201
            echo json_encode(["message" => "L'ajout a été effectué"]);

        }else // si la création n'a pas fonctionné
        
        {
            
           
            http_response_code(503);  // On envoie un code 503
            echo json_encode(["message" => "L'ajout n'a pas été effectué"]);         
        }
    }

}else

{
    // On gère l'erreur
    http_response_code(405);
    echo json_encode(["message" => "La méthode n'est pas autorisée"]);
}
