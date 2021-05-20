<?php
    require_once('Router.php');
    require_once('view/View.php');
    require_once('model/CrawlerStorage.php');
    require_once('model/TaskStorage.php');
    require_once('model/SessionStorage.php');

    class Controller{
        private $view;
        private $crawlerStorage;
        private $taskStorage;
        private $sessionStorage;
        private $crawledTextStorage;
        

        
        //TODO: Enlever appels vers base de donnees, vu qu'on utilise une API a la fin.
        public function __construct(Router &$router, View $view, CrawlerStorage &$crawlerStorage, TaskStorage &$taskStorage, SessionStorage &$sessionStorage, CrawledTextStorage $crawledTextStorage){
            $this->view = $view;
            $this->taskStorage = $taskStorage;
            $this->crawlerStorage = $crawlerStorage;
            $this->sessionStorage = $sessionStorage;
            $this->crawledTextStorage = $crawledTextStorage;
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
            $crawlers = $this->crawlerStorage->readAll();
            $this->view->makeCrawlerListPage($crawlers);
        }

        public function showTasks($crawlerId){
            //Lecture de chaque tache associee a un crawler
            //TODO: Refaire affichage pour soit appeler l'API, soit ne jamais avoir besoin de re-appeler l'API..

            //On pourrait faire une methode GetBySource plutot que GetById vu qu'actuellement, id et source sont egaux
            $tasks = $this->taskStorage->readAll($crawlerId);
            $this->view->makeTaskListPage($tasks, $this->crawlerStorage->read($crawlerId)->getSource());
        }

        public function doTasks($taskIdArray, $crawlerId){
            //Verifier que l'on a bien recu des id de taches.
            if (!empty($taskIdArray)) {

                //TODO: Page d'insertion quand toutes les taches sont terminees : V
                //TODO: permettre action pause/unpause + interruption de crawlers: V
                //TODO: finir appels scripts (quora et discord. peut-etre refaire discord en python) : X
                //      mettre cote incremental dans les crawlers : V
                //TODO: Remplacer bdd locale par utilisation d'API sur serveurs universite : X
                //TODO: Faire page d'extraction (prendre 1ere valeur de 'path' avant le '/') : X
                //TODO: Faire affichage correct avec bootstrap : V
                //TODO: faire authentification avec token : ~ (verifier avec API)

                //TODO: Si en avance : Permettre de se connecter a l'API pour transmettre une session en cours sur autre ordinateur local.
                //Cela requirerait de mettre un dossier cache sur le serveur pour chaque session en cours
               
                //Reprise de session locale:
                if (file_exists('cache/local_session_info.json')) {
                    //Recuperer les donnees de la derniere session.
                    $local_session_data = json_decode(file_get_contents("cache/local_session_info.json"), true);
                    $local_session_data['lastDate'] = time();
                    file_put_contents('cache/local_session_info.json', json_encode($local_session_data));
                } else {
                    //Il s'agit d'une toute nouvelle session: on cree une nouvelle session dans l'API et on met les memes infos en local.
                    //TODO: Remplacer par call API
                    $crawlerAPI = $this->crawlerStorage->read($crawlerId);
                    
                    $local_session_data = array(
                        "sessionId" => "unknown",
                        "crawlerId" => $crawlerId,
                        "crawlerSource" => strtolower($crawlerAPI->getSource()),
                        "taskIdArray" => $taskIdArray,
                        "taskStatusArray" => array_fill(0,count($taskIdArray),2),
                        "taskLastDataArray" => array_fill(0,count($taskIdArray),'unknown'),
                        "firstDate" => time(),
                        "lastDate" => time()
                    );
                    //TODO: Remplacer par requete (API.getNEWTOKEN)
                    $local_session_data["sessionId"] = $this->sessionStorage->read($this->sessionStorage->create())->getToken();
                    //$local_session_data["sessionId"] = API.getNEWTOKEN();

                    //TODO: Remplacer taskLastDataArray => unknown par un appel API:
                    $tempArray = array();
                    $tempIndex = 0;
                    foreach ($taskIdArray as $taskId){
                        //TODO: Faire appel API pour savoir quelle est la derniere donnee d'une certaine tache/
                        //$tempArray[$tempIndex] = API.getLastDataOfTask($taskId);

                        //Recherche locale.
                        $thing = $this->crawledTextStorage->getLastKnownData($taskId);
                        $tempArray[$tempIndex] = $thing["realid"];
                        $tempIndex = $tempIndex + 1;
                    }
                    $local_session_data["taskLastDataArray"] = $tempArray;

                    unset($tempArray);
                    unset($tempIndex);

                    file_put_contents('cache/local_session_info.json', json_encode($local_session_data));
                }
                
                $source = $local_session_data["crawlerSource"];
                $cache_path = "src/crawlers/crawler_".$source."/cache";


                $JSLogic = '<div id="container">';
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
            
            #Execution de taches en arriere-plan
            $tempIndex = 0;
            foreach ($taskIdArray as $taskId){
                //TODO: Faire un appel a l'API pour recuperer les infos de cette tache.
                $task = $this->taskStorage->read($taskId);
                $entrypoint = $task->getEntry();
                $lastDataProgression = $local_session_data["taskLastDataArray"][$tempIndex];
                //TODO: appel API pour savoir quel est la derniere donnee connue de la BDD.
                //TODO: Ameliorer la gestion des erreurs, et afficher sur l'interface lorsqu'une erreur se produit.
                $limit = $task->getLimit();
                $args = array($source, $taskId, $entrypoint, $lastDataProgression, $limit);

                //Si ordinateur utilisant windows:
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $command = "start /b " . $script_path . " " . escapeshellarg(json_encode($args)) . ' > ' . $error_log_path . ' 2>&1';
                    //Note : Cela suppose qu'il existe un moyen d'associer les fichiers .py a python, ce qui est normalement fait par defaut lors de l'installation de celui-ci sur windows.
                }
                else{
                    $command = $script_path . " " . escapeshellarg(json_encode($args)) . ' > ' . $error_log_path . ' 2>&1 &';
                }
                exec($command);
                $tempIndex = $tempIndex + 1;            
            }

                unset($tempIndex);
                $JSLogic .= '
                <script>
                //Fonction pour afficher la progression des taches:
                var containerDiv = document.getElementById("container");
                var pauseToggleValue = 0
                
                //Affichage de la progression des taches:
                function XHRLogSearch() {
                    for (let i = taskIdArrayCopy.length - 1; i >= 0; i--) {
                        var xhr = new XMLHttpRequest();
                        xhr.responseType = "json";
                        xhr.open("get", "/"+cache_path+"/Tache"+taskIdArrayCopy[i]+"Log.json", true);
                        xhr.send();
                        xhr.onload = function() {
                            //TODO: Refaire ceci apres integration Bootstrap?
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
                                            document.getElementById("pauseButton").firstChild.textContent = "Cliquer ici pour relancer les crawlers";
                                            document.getElementById("pauseButton").classList.remove("btn-primary");
                                            document.getElementById("pauseButton").classList.add("btn-secondary");
                                            pauseToggleValue = 1 - pauseToggleValue;
                                            break;
                                        case 1:
                                            document.getElementById("pauseButton").firstChild.textContent = "Cliquer ici pour mettre en pause les crawlers";
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
                
                foreach ($taskIdArray as $taskId){
                    //Creation du fichier log.
                    $tempfile = $cache_path.Router::PATH_DELIMITER."Tache".$taskId."Log.json";
                    if(!is_file($tempfile)){
                        file_put_contents($cache_path.Router::PATH_DELIMITER."Tache".$taskId."Log.json","{\"status\": 2}");
                    }
                    
                    $JSLogic .= "<div class='task" . $taskId . "Progress'></div>";
                    $JSLogic .= '
                    <script>   
                        taskIdArray.push(' . $taskId . ');
                        taskIdArrayCopy.push(' . $taskId . ');
                    </script>';
                }
                //Closing div "container"
                
                $JSLogic .= "</div>";
                $JSLogic .= '
                <script>
                //Une fois que notre array est rempli, on lance les appels ajax vers les fichiers log.
                //Retardement de la fonction pour eviter d\'avoir une reponse vide ou NULL
                setTimeout(function(){ XHRLogSearch(); }, 3000);
                
                </script>';
                $JSLogic .= '<div id="buttonContainer" class="btn-group-vertical">';
                $JSLogic .= '<button id="pauseButton" class="crawlerActionButton btn btn-primary" type="button" onclick="actionCrawler(0)">Cliquer ici pour mettre en pause les crawlers</button>';
                $JSLogic .= '<button id="killButton" class="crawlerActionButton btn btn-danger" type="button btn-danger" onclick="actionCrawler(1)">Cliquer ici pour arreter les crawlers, et garder une trace de leur progression</button>';
                $JSLogic .= '</div>';
                //On met les tag script et autre dans la vue:
                $this->view->makeTaskExecutionPage($JSLogic);
            } else {
                //Si pas de taches selectionnees, demander de selectionner des taches.
                $this->view->makeInvalidTasks($crawlerId);
            }  
        }


    
        public function askDataInsertion(){
            $local_session_data = json_decode(file_get_contents("cache/local_session_info.json"), true);

            //get id from token since we use delete($id)...
            

            //j'espere que delete fonctionne..
            //$local_session_data["sessionId"] = $this->sessionStorage->read($this->sessionStorage->create())->getToken();

            $this->view->makeDataInsertionPage($local_session_data['crawlerId']);
        }
        public function insertData(){
            if (file_exists('cache/local_session_info.json')) {
                $local_session_data = json_decode(file_get_contents("cache/local_session_info.json"), true);
                
                //TODO:
                //Ce qu'on veut inserer:
                //Les donnees dans la table data
                //enddate dans table task

                //TODO: Verifier que la session actuelle est valide (via API)
                //try API.validSession($local_session_data['SessionId'])


                //TODO: Lire les donnees dans le cache crawler, et inserer donnees
                $cache_path = "src/crawlers/crawler_". $local_session_data["crawlerSource"] ."/cache";

                foreach ($local_session_data["taskIdArray"] as $taskId){
                    $data = file_get_contents($cache_path . "/" ."Tache".$taskId."Data.json");            
                    //TODO:Envoyer en format JSON a l'API.

                    //Note: nos crawlers vont de la donnee la plus nouvelle vers la plus ancienne
                    //1         2       3           X
                    //Youngest    2nd               Oldest


                    //Si on re-ordonne on a bien:
                    //1         2           3           X
                    //Oldest    2nd                     Youngest

                    //Donc la BDD peut simplement choisir la donnee ayant l'id le plus eleve comme reference pour ne pas avoir de doublons.

                    //Cela suppose que le service ou le site web ne permette pas de supprimer arbitrairement des donnees.


                    //Inverser l'objet data:
                    $input = array_reverse(json_decode($data), true);
                    foreach ($input as $dataObject) {
                        $dataEntry = new CrawledText(
                            $dataObject->text,
                            $dataObject->path,
                            $dataObject->index,
                            $dataObject->realID,
                            $dataObject->taskID
                        );                        
                        //alors ca rentre mais le format dans postgres est pas joli joli.
                        $this->crawledTextStorage->create($dataEntry);
                    }
                    
                    //$this->crawledTextStorage->createBatch(json_decode($data));

                    //TODO:Si erreur.. alors redirection page erreur

                    //Enlever fichiers locaux cache:
                    unlink($cache_path . "/" ."Tache".$taskId."Data.json");
                    unlink($cache_path . "/" ."Tache".$taskId."Log.json");

                    //TODO: Complement : Mettre a jour attribut enddate dans bdd
                    $tempTask = $this->taskStorage->read($taskId);
                    $tempTask->setEndDate(time());
                    $this->taskStorage->update($tempTask,$taskId);
                }


                //Effacer session a distance
                //TODO: REMPLACER PAR APPEL API.
                $this->sessionStorage->deleteFromToken($local_session_data["sessionId"]);
                //Effacer le fichier session local
                unlink('cache/local_session_info.json');


                

                $this->view->makeInsertionCompletePage();

            }
            




        }
        public function showSources(){
            //TODO: Refaire avec connection API
            //Pour chaque tache possible:
            $tasks = $this->taskStorage->readEverything();
            //Faire array source => entrypoint
            //var_dump($tasks);

            $sourceArray = array();
            $tempIndex = 0;
            foreach ($tasks as $task){
                $sourceArray[$tempIndex] = array($task->getCrawlerId(), $task->getId(), $task->getEntry());
                $tempIndex++;
            }
            unset($tempIndex);


            //valeurs bidon pour exemple:
            $bidon11 = array('Discord', 'Fake/path/discord/serveurArt');
            $bidon12 = array('Discord', 'Fake/path/discord/serveurUniv');
            $bidon21 = array('Quora', 'Fake/path/quora/FAQArt');
            $bidon22 = array('Quora', 'Fake/path/quora/FAQPolitique');
            $bidon31 = array('Reddit', 'Fake/path/reddit/art');
            $bidon32 = array('Reddit', 'Fake/path/reddit/france');

            $bidon = array($bidon11,$bidon12,$bidon21,$bidon22,$bidon31,$bidon32);


            $this->view->makeSourceListPage($sourceArray);

        }


        public function dumpDatabase(){
            //TODO:
            //Verifier si requete valide

                //Essayer de lire depuis bDD:

                    //mettre cela quelque part et rediriger vers page de succes

                //Sinon, mettre page erreur

                
            //Si invalide, redirection vers la page d'extraction, en indiquant pourquoi la requete est invalide.
            
        }


        public function showAbout(){
            $this->view->makeAboutPage();
        }
        public function show404(){
            $this->view->make404();
        }


    }
