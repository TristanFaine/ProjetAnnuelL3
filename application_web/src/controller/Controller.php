<?php
    require_once('Router.php');
    require_once('view/View.php');

    class Controller{
        private $view;

        public function __construct(Router &$router, View $view){
            $this->view = $view;
        }


        public function apiCall($data, $method ,$endPoint){   
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
        
        public function showHome(){
            //Verification d'existence de session:
            if (file_exists('cache/local_session_info.json')) {
                //Rediriger vers page de demande de continuation du crawling
                $local_session_data = json_decode(file_get_contents("cache/local_session_info.json"), true);
                $this->view->makeResumePage($local_session_data);
            } else {
                $this->view->makeHomePage();
            }
        }

        public function showCrawlers(){
            $crawlers = $this->apiCall('','GET','Crawler/readAll.php')['data'];
            $this->view->makeCrawlerListPage($crawlers);
        }

        public function showTasks($crawlerId){
            //Lecture de chaque tache associee a un crawler
            //On pourrait faire une methode GetBySource plutot que GetById vu qu'actuellement, id et source sont egaux
            $tasks = $this->apiCall('{"crawlerID":' . $crawlerId . '}','GET','tache/readAllByCrawlerId.php')['data'];
            $source = $this->apiCall('{"crawlerID":' . $crawlerId . '}','GET','Crawler/read.php')['data'][0]['source'];
            $this->view->makeTaskListPage($tasks, $source);
        }

        public function doTasks($taskIdArray, $crawlerId){
            if (!empty($taskIdArray)) {
                
                //Reprise de session locale:
                if (file_exists('cache/local_session_info.json')) {
                    //Recuperer les donnees de la derniere session.
                    $local_session_data = json_decode(file_get_contents("cache/local_session_info.json"), true);
                    $local_session_data['lastDate'] = time();
                    file_put_contents('cache/local_session_info.json', json_encode($local_session_data));
                } else {
                    //Il s'agit d'une toute nouvelle session: on cree une nouvelle session dans l'API et on met les memes infos en local.
                    
                    $local_session_data = array(
                        "sessionId" => "unknown",
                        "crawlerId" => $crawlerId,
                        "crawlerSource" => strtolower($this->apiCall('{"crawlerID":' . $crawlerId . '}','GET','Crawler/read.php')['data'][0]['source']),
                        "taskIdArray" => $taskIdArray,
                        "taskStatusArray" => array_fill(0,count($taskIdArray),2),
                        "taskLastDataArray" => array_fill(0,count($taskIdArray),'unknown'),
                        "firstDate" => time(),
                        "lastDate" => time()
                    );
                   
                    $local_session_data["sessionId"] = $this->apiCall('','POST','session/create.php')['data'];

                    $tempArray = array();
                    $tempIndex = 0;
                    foreach ($taskIdArray as $taskId){
                        //il serait preferable de récupérer un identifiant par post/chaîne plutôt que de prendre un seul identifiant global.
                        $tempArray[$tempIndex] = $this->apiCall('{"taskid":' . $taskId . '}','GET','donnees/getLastKnownData.php')['data']['realid'];
                        $tempIndex = $tempIndex + 1;
                    }
                    $local_session_data["taskLastDataArray"] = $tempArray;

                    unset($tempArray);
                    unset($tempIndex);

                    file_put_contents('cache/local_session_info.json', json_encode($local_session_data));
                }
                
                $source = $local_session_data["crawlerSource"];
                $cache_path = "src/crawlers/crawler_".$source."/cache";


                $JSLogic = '<div id="taskContainer" class="list-group">';
                $JSLogic .= '<script>
                //Preparation de valeurs communes aux taches (Id, source, chemin du cache)
                var taskIdArray = [];
                var taskIdArrayCopy = [];
                var taskIndexCount = ' . count($taskIdArray) . ';
                var completionCount = 0;
                var source = ' . json_encode($source) . ';
                var cache_path = ' . json_encode($cache_path) . ';

                </script>';
                
                //On va maintenant appeler les scripts en arriere-plan.
                switch (strtolower($source)) {
                    case "reddit":
                        $script_path = realpath("src/crawlers/crawler_reddit/crawler.py");
                        $error_log_path = realpath("src/crawlers/crawler_reddit/cache/error_log.txt");
                        break;
                    case "discord":
                        $script_path = realpath("src/crawlers/crawler_discord/fetch.js");
                        $error_log_path = realpath("src/crawlers/crawler_discord/cache/error_log.txt");
                        break;
                    case "quora":
                        $script_path = realpath("src/crawlers/crawler_web/quora/question.py");
                        $error_log_path = realpath("src/crawlers/crawler_web/quora/cache/error_log.txt");
                        break;
                    default:
                        //TODO: On essaye de faire une demande invalide, donc afficher une page d'erreur
                        echo "ERROR: INVALID SOURCE";
                        exit();
                        break;
                }
            
                foreach ($taskIdArray as $taskId){
                    //Creation du fichier log.
                    $tempfile = $cache_path.Router::PATH_DELIMITER."Tache".$taskId."Log.json";
                    if(!is_file($tempfile)){
                        file_put_contents($tempfile, '{"status": 2}');
                    }
                    $tempfile = $cache_path.Router::PATH_DELIMITER."Tache".$taskId."Data.json";
                    if(!is_file($tempfile)){
                        file_put_contents($tempfile, ' ');
                    }
                    
                    //
                    $JSLogic .= "<div class='task" . $taskId . "Progress list-group-item'></div>";
                    $JSLogic .= '
                    <script>   
                        taskIdArray.push(' . $taskId . ');
                        taskIdArrayCopy.push(' . $taskId . ');
                    </script>';
                }

                #Execution de taches en arriere-plan
                $tempIndex = 0;
                foreach ($taskIdArray as $taskId){
                    $task = $this->apiCall('{"taskid":' . $taskId . '}','GET','tache/read.php')['data'];
                    $entrypoint = $task["entrypoint"];
                    $lastDataProgression = $local_session_data["taskLastDataArray"][$tempIndex];
                    $limit = $task["datalimit"];

                    //Si ordinateur utilisant windows:
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        switch ($crawlerId) {
                            case 1:
                                $program_name = 'python';
                                break;
                            case 2:
                                $program_name = 'node';
                                break;
                            case 3:
                                $program_name = 'python';
                                break;
                        }

                        $command =  'start /b "" ' . $program_name . ' "' .$script_path . '" ' . escapeshellarg($source) . ' ' .
                        escapeshellarg($taskId) . ' ' .
                        escapeshellarg($entrypoint) .' ' .
                        escapeshellarg($lastDataProgression) . ' ' .
                        escapeshellarg($limit) . ' ' .
                        '> "' . $error_log_path . '" 2>&1';
                    }
                    //Si ordinateur n'utilisant pas windows, donc est probablement compatible avec notation bash standard:
                    else{
                        $command = $script_path . ' ' . escapeshellarg($source) . ' ' .
                        escapeshellarg($taskId) . ' ' .
                        escapeshellarg($entrypoint) .' ' .
                        escapeshellarg($lastDataProgression) . ' ' .
                        escapeshellarg($limit) . ' ' .
                        '> ' . $error_log_path . ' 2>&1 &';
                    }
                    
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
                        pclose(popen($command, "r")); 
                    }
                    else {
                        exec($command);  
                    } 

                   
                    $tempIndex = $tempIndex + 1;            
                }

                unset($tempIndex);
                $JSLogic .= '
                <script>
                //Fonction pour afficher la progression des taches:
                var containerDiv = document.getElementById("taskContainer");
                var pauseToggleValue = 0
                
                //Affichage de la progression des taches:
                function XHRLogSearch() {
                    for (let i = taskIdArrayCopy.length - 1; i >= 0; i--) {
                        var xhr = new XMLHttpRequest();
                        xhr.responseType = "json";
                        xhr.open("get", "/"+cache_path+"/Tache"+taskIdArrayCopy[i]+"Log.json", true);
                        xhr.send();
                        xhr.onload = function() {
                            let taskDiv = document.getElementsByClassName("task" + taskIdArrayCopy[i] + "Progress")[0];
                            if (this.response["status"] === 2){
                                taskDiv.innerHTML = "Preparation de la tache " + taskIdArrayCopy[i];
                            }
                            if (this.response["status"] === 1){
                                //Crawler en cours d\'execution:
                                taskDiv.innerHTML = "Tache " + taskIdArrayCopy[i] +"(" + this.response["entrypoint"] + ") en cours d\'execution : " + this.response["local_index"] + " donnees recuperees";
                            } else if (this.response["status"] === 0) {
                                //Execution finie.
                                taskDiv.innerHTML = "Execution de tache " + taskIdArrayCopy[i] +"(" + this.response["entrypoint"] + ") finie : " + this.response["local_index"] + " donnees recuperees";
                                //Enlever la tache de la liste:
                                taskIdArrayCopy.splice(i, 1)
                                //taskPIDArray.splice(i, 1)
                                completionCount = completionCount + 1;
                            }
                        }
                    }
                    
                    if (completionCount == taskIndexCount) {
                        //Cette condition n\'est remplie que si toutes les taches sont finies. On arrete de regarder les fichiers log
                        //Et on redirige vers la page d\'insertion.
                        window.location.href = "insert";
        
                        return;
                    }
                    setTimeout("XHRLogSearch()",5000);   
                }

                //Action pause/kill sur les crawlers:
                function actionCrawler(action) {
                    var lhr = new XMLHttpRequest();
                        lhr.open("POST", "/src/controller/ActionCrawler.php", true);
                        lhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        lhr.send("source=" + source + "&action=" + action);
                        lhr.onload = function() {
                            switch (action) {
                                case 0:
                                    switch (pauseToggleValue) {
                                        case 0:
                                            document.getElementById("pauseButton").firstChild.textContent = "Reprise";
                                            document.getElementById("pauseButton").classList.remove("btn-primary");
                                            document.getElementById("pauseButton").classList.add("btn-secondary");
                                            pauseToggleValue = 1 - pauseToggleValue;
                                            break;
                                        case 1:
                                            document.getElementById("pauseButton").firstChild.textContent = "Pause";
                                            document.getElementById("pauseButton").classList.remove("btn-secondary");
                                            document.getElementById("pauseButton").classList.add("btn-primary");
                                            pauseToggleValue = 1 - pauseToggleValue;
                                            break;
                                        default:
                                            break;
        
                                    }
                                    break;
                                case 1:
                                    //Redirection vers home
                                    window.location.href = "../home";
                                    break;
                                default:
                                    console.log("Cela ne devrait jamais arriver");
                                    break;
                            }
                        }
                }
                
                </script>';
                
                
                //Closing div "taskContainer"
                
                $JSLogic .= "</div>";
                $JSLogic .= '
                <script>
                //Une fois que notre array est rempli, on lance les appels ajax vers les fichiers log.
                //Retardement de la fonction pour eviter d\'avoir une reponse vide ou NULL
                setTimeout(function(){ XHRLogSearch(); }, 3000);
                
                </script>';
                $JSLogic .= '<button id="pauseButton" class="btn btn-lg btn-primary me-2" type="button" onclick="actionCrawler(0)">Pause</button>';
                $JSLogic .= '<button id="killButton" class="btn btn-lg btn-danger me-2" type="button" onclick="actionCrawler(1)">Arret</button>';
                
                //Fermeture du div "container"
                $JSLogic .= "</div>";
               

                //On met les tag script et autre dans la vue:
                $this->view->makeTaskExecutionPage($JSLogic);
            } else {
                //Si pas de taches selectionnees, demander de selectionner des taches.
                $this->view->makeInvalidTasks($crawlerId);
            }  
        }


    
        public function askDataInsertion(){
            $local_session_data = json_decode(file_get_contents("cache/local_session_info.json"), true);

            $this->view->makeDataInsertionPage($local_session_data['crawlerId']);
        }
        public function insertData(){
            if (file_exists('cache/local_session_info.json')) {
                $local_session_data = json_decode(file_get_contents("cache/local_session_info.json"), true);

                // Lecture des donnees dans le cache crawler:
                $cache_path = "src/crawlers/crawler_". $local_session_data["crawlerSource"] ."/cache";

                //Pour chaque tache effectuee:
                foreach ($local_session_data["taskIdArray"] as $taskId){
                    $data = file_get_contents($cache_path . "/" ."Tache".$taskId."Data.json");            

                    //Note: nos crawlers vont de la donnee la plus nouvelle vers la plus ancienne
                    //1         2       3           X
                    //Youngest    2nd               Oldest
                    //Si on re-ordonne on a bien:
                    //1         2           3           X
                    //Oldest    2nd                     Youngest

                    //Donc la BDD peut simplement choisir la donnee ayant l'id la plus élevée comme reference pour ne pas avoir de doublons.

                    //Cela suppose que le service ou le site web ne permette pas de supprimer arbitrairement des donnees.


                    //Inverser l'objet data:
                    //Il serait peut-etre mieux d'inverser cet array apres une execution d'un script avec succes, car cela suppose
                    //que tout les scripts vont de la donnee la plus nouvelle vers la plus ancienne, ce qui n'est pas forcement le cas..
                    //$input = array_reverse(json_decode($data), true);


                    $insertAttempt = $this->apiCall($data,'POST','donnees/createBatch.php');

                    //Enlever fichiers locaux cache:
                    unlink($cache_path . "/" ."Tache".$taskId."Data.json");
                    unlink($cache_path . "/" ."Tache".$taskId."Log.json");

                    //Mise a jour attribut enddate dans bdd
                    $tempTask = $this->apiCall('{"taskid":' . $taskId . '}','GET','tache/read.php')['data'];
                    $tempTask["enddate"] = time();
                    $this->apiCall(json_encode($tempTask),'PUT','tache/modifier.php');

                }
                //Effacer session a distance
                $tempTask = $this->apiCall('{"token":"' . $local_session_data["sessionId"] . '"}','DELETE','session/deleteByToken.php');
                //Effacer egalement le fichier de session local
                unlink('cache/local_session_info.json');
                $this->view->setFeedback('Insertion reussie');
                $this->view->makeHomePage();
            }
            




        }
        public function showSources(){
            $tasks = $this->apiCall('','GET','tache/readAll.php')['data'];

            $sourceArray = array();
            $tempIndex = 0;
            foreach ($tasks as $task){
                $sourceArray[$tempIndex] = array($task['crawlerid'], $task['id'], $task['entrypoint']);
                $tempIndex++;
            }
            unset($tempIndex);

            $this->view->makeSourceListPage($sourceArray);

        }

        public function showAdmin(){
            //Si non-reconnaissance de $_SESSION['TokenAdmin'], rediriger vers page de login
            //Sinon, afficher page select pour choisir un type d'action et une "unite" (utiliser bootstrap-select ou autre)
            //Genre trois colonnes:
            //1eme colonne = choix action CRUD
            //2eme colonne = choix de type de truc
            //3eme colonne = choix de truc
            //et un bouton "view detail/confirm" pour aller sur la page d'action, qui sera formatee selon l'action choisie
            //et le type de truc.

            if(isset($_SESSION['TokenAdmin']) && !empty($_SESSION['TokenAdmin'])) {
                //makeSelectPage
                //ajouter un check de token pour chaque page/action privee.
            }
            //makeLoginPage
            $this->view->makeLoginPage();
            
        }



        public function DoLogin(){
            //recevoir post depuis formulaire login

            //faire appel API vers BDD pour recuperer le hash 
            //faire password_verify($post, $hash)

            //si ok alors rediriger avec feedback succes + generer et mettre un token dans $_session et la table admin
            //sur chaque page "privee" : voir si le token est le meme que dans la BDD, si c'est pas le cas alors ne pas afficher

            //si pas ok alors rediriger avec feedback erreur



        }



        public function showAbout(){
            $this->view->makeAboutPage();
        }
        public function show404(){
            $this->view->make404();
        }


    }
