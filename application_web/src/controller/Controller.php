<?php

//Rappel de ce que fait un controlleur:
//Possede une liste de fonctions permettant de faire des choses, quand on l'appele
//cela est pratique pour diviser les diverses fonctionabilitees, et rends le tout lisible.

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
           $this->view->makeHomePage();
        }

        public function showCrawlers(){
            //Connexion API -> Montrer Crawlers disponibles.
            //On suppose que l'API renvoie du JSON, et qu'il faut aussi envoyer du JSON pour communiquer avec elle.
            //Sans API, je vais devoir faire ma demonstration avec la BDD locale.

            //Read every value in crawler table
            $crawlers = $this->crawlerStorage->readAll();
            //var_dump($crawlers);

            $this->view->makeCrawlerListPage($crawlers);

        }

        public function showTasks($crawlerId){
            //Reads every value from tasklist table corresponding to the id:
            $tasks = $this->taskStorage->readAll($crawlerId);
            //var_dump($tasks);
            $this->view->makeTaskListPage($tasks);

        }

        public function doTasks($taskIdArray, $crawlerId){
            //Check if post contains anything:
            if (!empty($taskIdArray)) {
                //OK C'EST BON J'AI LA BONNE IDEE... JE PENSE
                //1. AffichageUtil recoit une liste de taches et demande a creer un fichier tachelog$taskid.txt (log) pour chaque tache.
                //   De plus, il cree un div pour chaque tache, pour pouvoir indiquer la progression de chacune.
                // ^ a refaire avec un framework type bootstrap
                //2. Il demande a faire les taches 1 par 1, selon la source, entrypoint, etc.. et renvoie les pid.
                //3. Ilregarde de temps en temps les fichiers log de chaque tache, et renvoie la progression
                //4. Quand toutes les taches sont finies.. il redirige vers la page d'insertion
                //   Et la page d'insertion recupere les donnees des fichiers .json



                //Une alternative serait de creer un serveur/manager PHP, JS, ou autre, utilisant le protocole websocket, lui communiquer une liste de taches
                //et de recuperer le resultat en l'ecoutant.
                //Mettre cela en place pour notre client web semble etre overkill et serait plutot approprie pour la communication API, meme si une architecture REST
                //est toujours la technique la plus appropriee, car on ne reste pas en ecoute sur le serveur tout le temps.


                //TODO: Faire appels de script depuis manager : X
                //TODO: Faire affichage page insertion quand taches terminees : X
                //TODO: Permettre pause/unpause de script : X (envoi signaux, soit prise en compte par defaut, soit utiliser une libraire.)
                //TODO: Permettre relance des scripts si une tache est interrompue. (mettre dans cookie, session, ou autre?) (ou genre un dossier cache global, vu qu'on garde la progression en local.) : X
                //TODO: Jolifier le tout avec un framework type Bootstrap : X
                
                echo '<p id="instructions_util">Veuillez patienter le temps que les scripts s\'executent.</p>';
                echo "<div id='container'>";

                //Recup le chemin correspondant a la source via api.
                //TODO: Faire appel API au lieu d'utiliser la BDD locale.
                $crawlerAPI = $this->crawlerStorage->read($crawlerId);
                $source = strtolower($crawlerAPI->getSource());
                $cache_path = "src/crawlers/crawler_".$source."/cache";
                ?>

                <script>
                //Preparation de valeurs communes aux taches (Id, PID du script, source)
                var containerDiv = document.getElementById('container');
                var taskIdArray = [];
                var taskIdArrayCopy = [];
                var taskPIDArray = [];
                var taskIndexCount = <?php echo count($taskIdArray)?>;
                var completionCount = 0;
                var source = <?php echo json_encode($source)?>;
                var cache_path = <?php echo json_encode($cache_path)?>;
                //Affichage de la progression des taches:
                function XHRLogSearch() {
                    for (let i = taskIdArrayCopy.length - 1; i >= 0; i--) {
                        var xhr = new XMLHttpRequest();
                        xhr.responseType = 'json';
                        xhr.open("get", "/"+cache_path+"/Tache"+taskIdArrayCopy[i]+"Log.json", true);
                        xhr.send();
                        xhr.onload = function() {
                            //TODO: Refaire ceci apres integration Bootstrap?
                            let taskDiv = containerDiv.getElementsByClassName('taskProgress')[i];
                            if (this.response['status'] === 1){
                                //Crawler en cours d'execution:
                                taskDiv.innerHTML = "Tache " + taskIdArrayCopy[i] +"(" + this.response['entrypoint'] + ") en cours d'execution : " + this.response["global_index"] + " donnees recuperees";
                                //TODO: bouton d'envoi de signal kill ou sigcont sigstop (equivalent windows aussi)
                            } else if (this.response['status'] === 0) {
                                //Execution finie.
                                taskDiv.innerHTML = "Execution de tache " + taskIdArrayCopy[i] +"(" + this.response['entrypoint'] + ") finie : " + this.response["global_index"] + " donnees recuperees";
                                //Enlever la tache de la liste:
                                taskIdArrayCopy.splice(i, 1)
                                taskPIDArray.splice(i, 1)
                                completionCount = completionCount + 1;
                            }
                        }
                    }
                    
                    if (completionCount == taskIndexCount) {
                        //Cette condition n'est remplie que si toutes les taches sont finies. On arrete de regarder les fichiers log.

                        return;
                    }
                    setTimeout("XHRLogSearch()",5000);   
                }
                </script>
                <?php
                foreach ($taskIdArray as $taskId){
                    //Creation du fichier log.
                    if (!touch($cache_path.Router::PATH_DELIMITER."Tache".$taskId."Log.json")){
                        //TODO: meilleure gestion de l'erreur.
                        echo('Acces aux logs impossible, veuillez verifier la structure des dossiers de l\'application');
                        throw new Exception('Acces aux logs impossible, veuillez verifier la structure des dossiers de l\'application');
                    }                 
                    file_put_contents($cache_path.Router::PATH_DELIMITER."Tache".$taskId."Log.json","La tache " .$taskId. " n'a pas encore ete executee");
                    echo "<div class='taskProgress'></div>";
                    ?>
                    <script>   
                        taskIdArray.push(<?php echo $taskId?>);
                        taskIdArrayCopy.push(<?php echo $taskId?>);
                    </script>
                    <?php
                }
                //Closing div "container"
                echo "</div>";
                ?>
                <script>
                //Une fois que notre array est rempli, on lance les appels ajax vers les fichiers log.
                //TODO: Retarder la premiere execution de cette fonction, pour ne pas avoir de NULL.
                XHRLogSearch();
                
                </script>
                <?php
                //On va maintenant appeler les scripts en arriere-plan et recuperer leur PID.
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
                            $results['error'] = 'INVALID_SOURCE';
                            exit();
                            break;
                    }

                //Execution de chaque tache en arriere-plan.
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    //Machine Utilisateur = Windows:
                    //TODO: Call scripts and get PID using powershell wizardry
                    foreach ($taskIdArray as $taskId){
                        echo "pas encore disponible pour windows, desole";
                    }
                } else {
                    //Machine Utilisateur != Windows:
                    foreach ($taskIdArray as $taskId){
                        //TODO: Faire un appel a l'API pour recuperer les infos de cette tache.
                        $task = $this->taskStorage->read($taskId);
                        $entrypoint = $task->getEntry();

                        //TODO: Ameliorer la gestion des erreurs, et afficher sur l'interface lorsqu'une erreur se produit.
                        //Faire un fichier ErrorLog commun.
                        //Du coup pour l'affichage de celui-ci...hmm
                        $args = array($source, $taskId, $entrypoint);
                        $command = $script_path . " " . escapeshellarg(json_encode($args)) . ' > ' . $error_log_path . ' 2>&1 & echo $!; ';
                        $pid = exec($command);
                        ?>
                        <script>   
                        taskPIDArray.push(<?php echo $pid?>);
                        </script>
                        
                        <?php
                    

                    //TODO: permettre envoi signaux sigstop/sigcont ou equivalent windows
                    //Du coup, si on a une liste de PID dans js.. trouver un moyen d'envoyer ceux-ci a PHP.
                    //TODO: finir appels scripts (quora et discord. peut-etre refaire discord en python)
                    //      mettre cote incremental dans les crawlers (mettre dernier ID connu ou dernier texte connu)
                    //TODO: Page d'insertion quand toutes les taches sont terminees.
                    //TODO: Faire page d'extraction (prendre 1ere valeur de 'path' avant le '/')
                    //TODO: faire authentification avec token
                    //TODO: Remplacer bdd locale par utilisation d'API sur serveurs universite.
                    }

                }



                

            } else {
                //Si pas de taches selectionnees, demander de selectionner des taches.
                $this->view->makeInvalidTasks($crawlerId);
            }  
        }


    
        public function insertData($taskIdArray, $crawlerId){
            //TODO: Prendre en compte l'authentification par token pour eviter d'avoir des problemes tel des doublons.

            //TODO: dire a l'utilisateur ce qui va etre envoyee a la bdd


            //TODO: bouton de confirmation


            //TODO: commencer l'insertion des donnees.

            //TODO: afficher succes/erreur de l'operation.




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
            //check if request is valid:

                //try to read from database:

                    //if successful, put it somewhere, and redirect to a success page

                //otherwise, go to the error page

                
            //if invalid, redirect to the request page, while giving information on why the request is invalid.
            
        }


        public function showAbout(){
            $this->view->makeAboutPage();
        }
        public function show404(){
            $this->view->make404();
        }


    }
