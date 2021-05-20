<?php
    require_once('Router.php');
    require_once('view/View.php');
    require_once('model/CrawlerStorage.php');
    require_once('model/TaskStorage.php');
    

    class Controller{
        private $view;
        private $crawlerStorage;
        private $taskStorage;
        private $crawledtextStorage;
        

        
        //TODO: Enlever appels vers base de donnees, vu qu'on utilise une API a la fin.
        public function __construct(Router &$router, View $view, CrawlerStorage &$crawlerStorage, TaskStorage &$taskStorage){
            $this->view = $view;
            $this->taskStorage = $taskStorage;
            $this->crawlerStorage = $crawlerStorage;            
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
            $tasks = $this->taskStorage->readAll($crawlerId);
            $this->view->makeTaskListPage($tasks);

        }

        public function doTasks($taskIdArray, $crawlerId){
            //Verifier que l'on a bien recu des id de taches.
            if (!empty($taskIdArray)) {

                //TODO: Page d'insertion quand toutes les taches sont terminees : ~
                //TODO: permettre action pause/unpause + interruption de crawlers: V
                //TODO: finir appels scripts (quora et discord. peut-etre refaire discord en python) : X
                //      mettre cote incremental dans les crawlers : V
                //TODO: Remplacer bdd locale par utilisation d'API sur serveurs universite : X
                //TODO: Faire page d'extraction (prendre 1ere valeur de 'path' avant le '/') : X
                //TODO: Faire affichage correct avec bootstrap : X
                //TODO: faire authentification avec token : X

                //TODO: Si en avance : Permettre de se connecter a l'API pour transmettre une session en cours sur ordinateur local.
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
                    $session_values = array(
                        "sessionId" => "unknown",
                        "crawlerId" => $crawlerId,
                        "crawlerSource" => strtolower($crawlerAPI->getSource()),
                        "taskIdArray" => $taskIdArray,
                        "taskStatusArray" => array_fill(0,count($taskIdArray),2),
                        "taskLastDataArray" => array_fill(0,count($taskIdArray),'unknown'),
                        "firstDate" => time(),
                        "lastDate" => time()
                    );
                    //TODO: Faire requete (API.getNEWTOKEN)
                    //$session_values["sessionId"] = API.getNEWTOKEN();

                    file_put_contents('cache/local_session_info.json', json_encode($session_values));
                    $local_session_data = $session_values;
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

            //Execution de chaque tache en arriere-plan.
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                //Machine Utilisateur = Windows:
                //TODO: Appeler les scripts en utilisant ps windows
                foreach ($taskIdArray as $taskId){
                    echo "pas encore disponible pour windows, desole";
                    exit();
                }
            } else {
                //Machine Utilisateur != Windows:
                $tempIndex = 0;
                foreach ($taskIdArray as $taskId){
                    //TODO: Faire un appel a l'API pour recuperer les infos de cette tache.
                    $task = $this->taskStorage->read($taskId);
                    $entrypoint = $task->getEntry();
                    $lastDataProgression = $local_session_data["taskLastDataArray"][$tempIndex];
                    //TODO: appel API pour savoir quel est la derniere donnee connue de la BDD.
                    //TODO: Ameliorer la gestion des erreurs, et afficher sur l'interface lorsqu'une erreur se produit.
                    //TODO: mettre attribut limit dans objet tache.
                    $limit = 500;
                    $args = array($source, $taskId, $entrypoint, $lastDataProgression, $limit);
                    $command = $script_path . " " . escapeshellarg(json_encode($args)) . ' > ' . $error_log_path . ' 2>&1 & echo $!; ';
                    $pid = exec($command);
                    $tempIndex = $tempIndex + 1;
                    //TODO: Utiliser ce pid pour permettre d'envoyer des signaux kill ou pause.
                    $JSLogic .= '
                    <script>
                    //taskPIDArray.push(' . $pid . ');
                    </script>';                 
                }
            }
                unset($tempIndex);
                $JSLogic .= '
                <script>
                //Fonction pour afficher la progression des taches:
                var containerDiv = document.getElementById("container");
                
                //Affichage de la progression des taches:
                function XHRLogSearch() {
                    for (let i = taskIdArrayCopy.length - 1; i >= 0; i--) {
                        var xhr = new XMLHttpRequest();
                        xhr.responseType = "json";
                        xhr.open("get", "/"+cache_path+"/Tache"+taskIdArrayCopy[i]+"Log.json", true);
                        xhr.send();
                        xhr.onload = function() {
                            //TODO: Refaire ceci apres integration Bootstrap?
                            console.log(taskIdArrayCopy[i])
                            let taskDiv = document.getElementsByClassName("task" + taskIdArrayCopy[i] + "Progress")[0];
                            if (this.response["status"] === 2){
                                taskDiv.innerHTML = "Preparation de la tache " + taskIdArrayCopy[i];
                            }
                            if (this.response["status"] === 1){
                                //Crawler en cours d\'execution:
                                taskDiv.innerHTML = "Tache " + taskIdArrayCopy[i] +"(" + this.response["entrypoint"] + ") en cours d\'execution : " + this.response["global_index"] + " donnees recuperees";
                            } else if (this.response["status"] === 0) {
                                //Execution finie.
                                taskDiv.innerHTML = "Execution de tache " + taskIdArrayCopy[i] +"(" + this.response["entrypoint"] + ") finie : " + this.response["global_index"] + " donnees recuperees";
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
                            //console.log(this.response);
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

                $JSLogic .= '<div id="pauseButton"> <button type="button" onclick="actionCrawler(0)">Cliquer ici pour mettre en pause les crawlers</button>    <div/>';
                $JSLogic .= '<div id="killButton"> <button type="button" onclick="actionCrawler(1)">Cliquer ici pour arreter les crawlers, et garder une trace de leur progression</button>    <div/>';
                //On met les tag script et autre dans la vue:
                $this->view->makeTaskExecutionPage($JSLogic);
            } else {
                //Si pas de taches selectionnees, demander de selectionner des taches.
                $this->view->makeInvalidTasks($crawlerId);
            }  
        }


    
        public function askDataInsertion(){
            //On verifie que l'utilisateur a deja selectionne une tache.
            $local_session_data = json_decode(file_get_contents("cache/local_session_info.json"), true);
            $this->view->makeDataInsertionPage($local_session_data['crawlerId']);
        }
        public function insertData(){
            if (file_exists('cache/local_session_info.json')) {
                $local_session_data = json_decode(file_get_contents("cache/local_session_info.json"), true);
                

                //TODO: Verifier que la session actuelle est valide (via API)


                //TODO: Lire les donnees dans le cache crawler, et inserer donnees
                $cache_path = "src/crawlers/crawler_". $local_session_data["crawlerSource"] ."/cache";
                echo ($cache_path);

                foreach ($local_session_data["taskIdArray"] as $taskId){
                    $data = file_get_contents($cache_path . "/" ."Tache".$taskId."Data.json");

                    var_dump($data);
                    
                    //TODO:Envoyer en format JSON a l'API.

                    //TODO:Si erreur.. alors redirection erreur

                    //unlink($cache_path . "/" ."Tache".$taskId."Data.json");
                    //unlink($cache_path . "/" ."Tache".$taskId."Log.json");
                }

                //Effacer le fichier session
                //unlink('cache/local_session_info.json');

                //$this->view->makeInsertionCompletePage();

            }
            




        }
        public function showSources(){
            //Find every distinct source + path in the crawled text database
                //$crawlers = $this->crawlerStorage->readAll();
            //Make an array composed of unique source + path:

            //TODO: Connection API, afficher toutes les sources possibles (source + tache => Reddit/france, ou Discord/serveurArt, etc.)
            //                                                                                      art                serveurFrance

            //valeurs bidon pour exemple:
            $bidon11 = array('Discord', 'Fake/path/discord/serveurArt');
            $bidon12 = array('Discord', 'Fake/path/discord/serveurUniv');
            $bidon21 = array('Quora', 'Fake/path/quora/FAQArt');
            $bidon22 = array('Quora', 'Fake/path/quora/FAQPolitique');
            $bidon31 = array('Reddit', 'Fake/path/reddit/art');
            $bidon32 = array('Reddit', 'Fake/path/reddit/france');

            $bidon = array($bidon11,$bidon12,$bidon21,$bidon22,$bidon31,$bidon32);


            $this->view->makeSourceListPage($bidon);

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
