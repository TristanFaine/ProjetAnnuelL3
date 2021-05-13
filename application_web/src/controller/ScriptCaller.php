<?php

//TODO: REMOVE THIS ONCE DONE.
//include('../model/TaskStorage.php');


//Ce script permet d' executer les taches 1 par 1, en arriere-plan, et de stocker les donnees JSON dans un fichier.
//TODO: Verifier le type d'action : Commencer depuis 0, ou continuer une execution qui a ete interrompue pour X raison.


//Reception de donnees : le type/source de crawler, l'id de tache a faire, le token.
$results = array(
    'source' => $_POST['source'],
    'taskIdArray[]' => json_decode($_POST['taskIdArray']),
    'token' => $_POST['token'],
    'error' => 'none',
    'taskPIDArray[]' => []
);

//If no token given.. somehow:
if(empty($_POST['token'])){
    $results['error'] = 'INVALID_TOKEN';
    echo json_encode($results);
    exit();
}

//Handle invalid token:
//Faire une table Token? peut-etre.
//Connexion a l'API pour verifier que le token fourni est valide:
//$tokenCheck = api.getToken($results['token'])




//Si tout est OK: executer les scripts en arriere-plan.
//TODO: Essayer avec un script bidon en python, puis faire une vraie execution.



//Formation du chemin pour appeler le script correspondant.
//TODO: remplacer la derniere partie du chemin par crawler.xx (xx peut etre n'importe quoi.)
switch (strtolower($results['source'])) {
	case "reddit":
		$path = realpath("../crawlers/crawler_reddit/crawler.py");
        $error_log_path = realpath("../crawlers/crawler_reddit/cache/error_log.txt");
		break;
	case "discord":
		$path = escapeshellcmd("../crawlers/crawler_discord/fetch.js");
        $error_log_path = realpath("../crawlers/crawler_discord/cache/error_log.txt");
		break;
	case "quora":
		$path = escapeshellcmd("../crawlers/crawler_web/quora/question.py");
        $error_log_path = realpath("../crawlers/crawler_web/quora/cache/error_log.txt");
		break;
	default:
	//On essaye de faire une demande invalide, donc afficher une page d'erreur
        $results['error'] = 'INVALID_SOURCE';
        echo json_encode($results);
        exit();
		break;
}


//Execution de chaque tache en arriere-plan.
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    //Machine Utilisateur = Windows:
    //TODO: Call scripts and get PID using powershell wizardry
    foreach ($results['taskIdArray[]'] as $taskId){
        echo json_encode($results);
        exit();
    }

} else {
    //Machine Utilisateur != Windows:
    foreach ($results['taskIdArray[]'] as $taskId){
        //TODO: Faire un appel a l'API pour recuperer les infos de cette tache.
        //$task = $this->taskStorage->read($taskId);
        echo json_encode($taskId);
        exit();
        $entrypoint = $task->getEntry();
        //TODO: Ameliorer la gestion des erreurs, et afficher sur l'interface lorsqu'une erreur se produit apparait..
        
        $args = array($results['source'], $taskId, $entrypoint);
        
        $command = $path . " " . escapeshellarg(json_encode($args)) . ' > ' . $error_log_path . ' 2>&1 & echo $!; ';
        $pid = exec($command);
        array_push($results['taskPIDArray[]'],intval($pid));
    }
    

    
}

//Envoyer les donnees vers le serveur.
echo json_encode($results);
