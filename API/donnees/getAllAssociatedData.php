<?php
// Headers requis
header("Access-Control-Allow-Origin: *"); //accès à l'API par tous (*)
header("Content-Type: application/json; charset=UTF-8"); // envoi une réponse en json
header("Access-Control-Allow-Methods: GET"); // méthode accepté pour la requete pour insérer
header("Access-Control-Max-Age: 3600"); // durée de la requete
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); // headers qu'on autorise (filtrage)

// On vérifie la méthode
if($_SERVER['REQUEST_METHOD'] == 'GET'){
    // On inclut les fichiers de configuration et d'accès aux données
    include_once '../config/Database.php';
    include_once '../models/Donnees.php';

    // On instancie la base de données
    $database = new Database();
    $db = $database->getConnexion();

    

    // On instancie les donnees
    $data = new Donnees($db);

    // On récupère les informations envoyées
    $Donnees = json_decode(file_get_contents("php://input"));
    
    // Ici on a reçu les données
    if(!empty($Donnees->taskid)){
    
        //On met les informations dans l'objet donnees
        $data->taskid = $Donnees->taskid;

        $query = $data->getAllAssociatedData();

        if ($query == false) {
            http_response_code(503);  // On envoie un code 503
            echo json_encode(["message" => "Echec de lecture"]); 

        } else {
            
			$datum = [];
			foreach($query as $dataAttributes){
				$datum[] = $dataAttributes;
			}
            http_response_code(201);  // On envoie un code 201
            echo json_encode(["message" => "Lecture avec succes", "data" => $query]);

        }
    }

}else

{
    // On gère l'erreur
    http_response_code(405);
    echo json_encode(["message" => "La méthode n'est pas autorisée"]);
}
