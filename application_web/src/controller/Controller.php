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
                //2. AffichageUtil envoie les taches a faire vers Manager, en indiquant si c'est un debut de tache ou une reprise.
                //3. Manager demande a faire les taches 1 par 1, selon la source, entrypoint, etc.. et renvoie les pid a l'interface.
                //4. AffichageUtil regarde de temps en temps les fichiers log de chaque tache, et renvoie la progression
                //5. AffichageUtil regarde en meme temps si Manager renvoie une reponse, si c'est le cas, alors c'est que toutes les taches sont terminees (normalement)
                //6. Si en fait, manager renvoie les pid.. il faudrait avoir un autre moyen de savoir si toute les taches sont terminees.
                //Peut-etre que dans la logique de script, si l'array des PID est vide, alors on va vers la page d'insertion?
                //Et la page d'insertion recupere les donnees des fichiers .json



                //Une alternative serait de creer un serveur/manager PHP,JS, ou autre, utilisant le protocole websocket, lui communiquer une liste de taches
                //et de recuperer le resultat en l'ecoutant.
                //Mettre cela en place pour notre client web semble etre overkill et serait plutot approprie pour la communication API, meme si une architecture REST
                //est toujours la technique la plus appropriee.


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
                var taskPIDArray = [];
                var taskIndexCount = <?php echo count($taskIdArray)?>;
                var source = <?php echo json_encode($source)?>;
                var cache_path = <?php echo json_encode($cache_path)?>;
                //Affichage de la progression des taches:
                function XHRLogSearch() {
                    //TODO: Verifier si taskIndexCount est vide/taille 0, si c'est le cas, renvoyer vers la page d'insertion
                    for (let i = 0; i < taskIndexCount; i++) {
                        var xhr = new XMLHttpRequest();
                        xhr.open("get", "/"+cache_path+"/Tache"+taskIdArray[i]+"Log.txt", true);
                        xhr.send();
                        xhr.onload = function() {
                            //TODO: Refaire ceci apres integration Bootstrap?
                            let taskDiv = containerDiv.getElementsByClassName('taskProgress')[i];
                            taskDiv.innerHTML = this.responseText;
                            //console.log("tache " + i + " existe : " + this.responseText);

                            //TODO: Si le message commence par "Tache " +i+ " terminee", alors enlever de la pile js.

                            //TODO: Remplacer le txt brut par un json pour pouvoir recuperer des informations en plus.
                            //genre status=done ou notdone
                            //progression = le message qu'on souhaite montrer sur l'interface.
                            //et endDate = un print date a la toute fin.
                        }
                    }
                    setTimeout("XHRLogSearch()",5000);   
                }
                </script>
                <?php
                foreach ($taskIdArray as $taskId){
                    //Creation du fichier log.
                    if (!touch($cache_path.Router::PATH_DELIMITER."Tache".$taskId."Log.txt")){
                        //TODO: meilleure gestion de l'erreur.
                        echo('Acces aux logs impossible, veuillez verifier la structure des dossiers de l\'application');
                        throw new Exception('Acces aux logs impossible, veuillez verifier la structure des dossiers de l\'application');
                    }                 
                    file_put_contents($cache_path.Router::PATH_DELIMITER."Tache".$taskId."Log.txt","La tache " .$taskId. " n'a pas encore ete executee");
                    echo "<div class='taskProgress'></div>";
                    ?>
                    <script>   
                        taskIdArray.push(<?php echo $taskId?>);
                    </script>
                    <?php
                }
                //Closing div "container"
                echo "</div>";
                ?>
                <script>
                //Une fois que notre array est rempli, on lance les appels ajax vers les fichiers log.
                XHRLogSearch();

                //On appelle ensuite l'appeleur de scripts
                //On envoie la liste de taches a faire, et le type de source.
                var xhrCaller = new XMLHttpRequest();
                var xhrSentData = ''
                    + 'source=' + window.encodeURIComponent(source)
                    + '&taskIdArray=' + JSON.stringify(taskIdArray)
                    + '&token=' + window.encodeURIComponent('unknown');


                //On recoit une reponse, ainsi que la liste des pids.
                //TODO: Effacer cette fonction si l'on se rends compte que l'on n'en a pas besoin.
                function XHRGetCallerData() {
                    xhrCaller.open("POST", "/src/controller/ScriptCaller.php", true);
                    xhrCaller.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
                    xhrCaller.send(xhrSentData);
                    xhrCaller.onload = function() {
                        const jsonData = JSON.parse(this.responseText);
                            //TODO: Faire une vraie gestion d'erreur..
                            if (jsonData['error'] === 'INVALID_TOKEN') {
                                console.log("Votre token d'authentification est invalide, veuillez refaire une requete depuis la selection de crawler.");
                            }
                            console.log(jsonData);
                            //Recuperer les pids... et faire quelque chose avec.
                    } 
                }

                //XHRGetCallerData();


                //En fait, on n'a pas besoin de faire un appel AJAX vu qu'on utilise ce truc qu'une seule fois. donc on va le faire en dessous.
                //C'est juste moins lisible et comprehensible.
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
                        echo "pas encore fait pour windows dsl";
                    }
                } else {
                    //Machine Utilisateur != Windows:
                    foreach ($taskIdArray as $taskId){
                        //TODO: Faire un appel a l'API pour recuperer les infos de cette tache.
                        $task = $this->taskStorage->read($taskId);
                        $entrypoint = $task->getEntry();

                        //TODO: Ameliorer la gestion des erreurs, et afficher sur l'interface lorsqu'une erreur se produit.
                        //Faire un fichier ErrorLog commun.
                        //Du coup pour l'affichage de celui-ci...hm
                        $args = array($source, $taskId, $entrypoint);
                        $command = $script_path . " " . escapeshellarg(json_encode($args)) . ' > ' . $error_log_path . ' 2>&1 & echo $!; ';
                        $pid = exec($command);
                        ?>
                        <script>   
                        taskPIDArray.push(<?php echo $pid?>);
                        console.log(taskPIDArray);
                        </script>
                        
                        <?php
                    

                    //TODO: finir appels script
                    //TODO: remplacer lecture log txt par json
                    //TODO: permettre envoi signaux sigstop/sigcont ou equivalent windows
                    //TODO: Affichage page d'insertion quand toutes les taches sont terminees.
                    //TODO: faire authentification avec token
                    }

                }



                

            } else {
                //Si pas de taches selectionnees, demander de selectionner des taches.
                $this->view->makeInvalidTasks($crawlerId);
            }  
        }



        public function showSources(){
            //Find every distinct source + path in the crawled text database
                //$crawlers = $this->crawlerStorage->readAll();
            //Make an array composed of unique source + path:

            //TODO: Connection API, afficher toutes les sources possibles (source + tache => Reddit/france, ou Discord/serveurArt, etc.)

            //valeurs bidon pour exemple:
            $bidon11 = array('Discord', 'Fake/path/1');
            $bidon12 = array('Discord', 'Fake/path/2');
            $bidon13 = array('Discord', 'Fake/path/3');
            $bidon21 = array('Quora', 'Fake/path/1');
            $bidon22 = array('Quora', 'Fake/path/2');
            $bidon311 = array('Reddit', 'Fake/path/1/3');

            $bidon = array($bidon11,$bidon12,$bidon13,$bidon21,$bidon22,$bidon311);


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
