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
                //2. AffichageUtil envoie les taches a faire vers Manager.
                //3. Manager demande a faire les taches 1 par 1, selon la source, entrypoint, etc..
                //4. AffichageUtil regarde de temps en temps les fichiers log de chaque tache, et renvoie la progression
                //5. AffichageUtil regarde en meme temps si Manager renvoie une reponse, si c'est le cas, alors c'est que toutes les taches sont terminees (normalement)

                //Une alternative serait de creer un serveur/manager PHP,JS, ou autre, utilisant le protocole websocket, lui communiquer une liste de taches
                //et de recuperer le resultat en l'ecoutant.
                //Mettre cela en place pour notre client web semble etre overkill et serait plutot approprie pour la communication API, meme si une architecture REST
                //est toujours la technique la plus appropriee.

                echo '<p id="instructions_util">Veuillez patienter le temps que les scripts s\'executent.</p>';
                echo "<div id='container'>";

                //Recup le chemin correspondant a la source via api.
                //TODO: Faire appel API au lieu d'utiliser la BDD locale.
                $crawlerAPI = $this->crawlerStorage->read($crawlerId);
                $source = strtolower($crawlerAPI->getSource());
                $cache_path = "src/crawlers/crawler_".$source."/cache";
                
                //Etape 1. affichage des logs
                ?>
                <script>
                //Preparation de valeurs communes aux taches.
                var containerDiv = document.getElementById('container');
                var taskIdArray = [];
                var taskIndexCount = <?php echo count($taskIdArray)?>;   
                var cache_path = <?php echo json_encode($cache_path)?>;
                function XHRLogSearch() {
                    for (let i = 0; i < taskIndexCount; i++) {
                        var xhr = new XMLHttpRequest();
                        xhr.open("get", "/"+cache_path+"/Tache"+taskIdArray[i]+"Log.txt", true);
                        xhr.send();
                        xhr.onload = function() {
                            let taskDiv = containerDiv.getElementsByClassName('taskProgress')[i];
                            taskDiv.innerHTML = this.responseText;
                            //console.log("tache " + i + " existe : " + this.responseText);
                        }
                    }
                    setTimeout("XHRLogSearch()",5000);   
                }
                </script>
                <?php
                foreach ($taskIdArray as $taskId){
                    //Creation du fichier log.
                    touch($cache_path.Router::PATH_DELIMITER."Tache".$taskId."Log.txt");
                    file_put_contents($cache_path.Router::PATH_DELIMITER."Tache".$taskId."Log.txt","La tache " .$taskId. " n'a pas encore ete executee");
                    echo "<div class='taskProgress'>La tache " .$taskId. " n'a pas encore ete executee.</div>";
                    ?>
                    <script>   
                        //On remplit l'array qui contient les identifiants de taches
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
                var source = "reddit";
                var xhrSentData = ''
                    + 'source=' + window.encodeURIComponent(source)
                    + '&taskIdArray[]=' + window.encodeURIComponent(taskIdArray); 

                //On recoit une reponse seulement lorsque l'execution du Caller est terminee.
                function XHRGetCallerData() {
                    xhrCaller.open("POST", "/src/controller/ScriptCaller.php", true);
                    xhrCaller.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
                    xhrCaller.send(xhrSentData);
                    xhrCaller.onload = function() {
                        const jsonData = JSON.parse(this.responseText);
                        console.log(jsonData);

                        //Si probleme d'authentification
                        //Rediriger vers la page d'insertion... ou faire l'insertion depuis le Caller..? hmm. a voir plus tard avec l'authentification.

                    } 
                }

                XHRGetCallerData();
                </script>
                

                <?php
                

            } else {
                //Si pas de taches alors re-afficher la page.
                $this->view->makeInvalidTasks($crawlerId);
            }  
        }



        public function showSources(){
            //Find every distinct source + path in the crawled text database
                //$crawlers = $this->crawlerStorage->readAll();
            //Make an array composed of unique source + path:

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
