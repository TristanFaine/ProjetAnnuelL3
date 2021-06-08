<?php
//Permet de gerer l'affichage et la stylisation des services de l'application web.
    require_once('Router.php');

    class View{
        protected $router;
        protected $feedback;
        protected $title;
        protected $content;

        public function __construct(Router &$router, $feedback){
            $this->router = $router;
            $this->feedback = $feedback;
        }

        //Pages disponibles:

        public function makeHomePage(){
            $this->title = 'Acceuil - Application Crawler Incremental';
            $this->content = '<div class="col-lg-8 mx-auto p-3 py-md-5">';
                $this->content .= '<h1>Aucune session detectee</h1>';
                $this->content .='<hr class="col-3 col-md-2 mb-5">';
                $this->content .= '<div class="row g-5">';
                    $this->content .= '<div class="col-md-6">';
                        $this->content .= '<h1>Insertion de donnees</h1>';
                        $this->content .= '<div class="list-group">';
                            $this->content .= '<a class="list-group-item" style="font-size:25px" href="' .$this->router->getCrawlerListURL(). '">Choisir un crawler</a>';
                        $this->content .= '</div>';
                    $this->content .= '</div>';
                    $this->content .= '<div class="col-md-6">';
                        $this->content .= '<h1>Extraction de donnees</h1>';
                        $this->content .= '<ul class="list-group">';
                            $this->content .= '<a class="list-group-item" style="font-size:25px" href="' .$this->router->getExportURL(). '">Choisir une source de donnees</a>';
                        $this->content .= '</div>';
                    $this->content .= '</div>';
            $this->content .= '</div>';

        }

        public function makeResumePage(array &$local_session_data){
            $this->title = 'Reprise du crawling';
            $this->content = '<div class="container">';
                $this->content .= '<h4 class="display-4">Reprise de la session : ' . $local_session_data["sessionId"] . '</h4>';
                $this->content .= '<div class="list-group">';
                    $this-> content .= '<div class="list-group-item">Crawler utilisé : ' . $local_session_data["crawlerId"] . '(' .$local_session_data["crawlerSource"] .')</div>';
            
                    for($i = 0; $i <= count($local_session_data["taskIdArray"]) - 1; $i++){
                        switch($local_session_data["taskStatusArray"][$i]) {
                            case 0:
                                $taskStatus = "Finie";
                                break;
                            case 1:
                                $taskStatus = "Interrompue en cours d'exécution";
                                break;
                            case 2:
                                $taskStatus = "Pas encore exécutée";
                                break;
                        };
                        $this->content .= '<div class="list-group-item">Tache : ' . $local_session_data["taskIdArray"][$i] . ' | Status : ' . $taskStatus . '</div>';
                    }
                    $this->content .= '<div class="list-group-item"> Premiere exécution le : ' . date('m/d/Y h:i:s a',$local_session_data["firstDate"]) . '</div>';
                    $this->content .= '<div class="list-group-item"> Derniere exécution le : ' . date('m/d/Y h:i:s a',$local_session_data["lastDate"]) . '</div>';

            $this->content .= '</div>';

            $this->content .= '<hr>';

            $this->content .= '<div class="row g-5">';
                $this->content .= '<div class="col-md-6">';
                    $this->content .= '<div class="list-group">';
                        $this->content .= '<a class="list-group-item" style="font-size:25px" href="' . $this->router->getTaskListURL($local_session_data["crawlerId"]) . '">Reprendre le crawling</a>';
                    $this->content .= '</div>';
                $this->content .= '</div>';
                $this->content .= '<div class="col-md-6">';
                    $this->content .= '<div class="list-group">';
                        $this->content .= '<a class="list-group-item" style="font-size:25px" href="' . $this->router->getInsertURL($local_session_data["crawlerId"]) . '">Inserer les donnees</a>';
                    $this->content .= '</div>';
                $this->content .= '</div>';
            $this->content .= '</div>';
        }


        //Actions liees aux crawlers:
        public function makeCrawlerListPage(array &$crawlers){
            $this->title = 'Liste des crawlers disponibles';

            $this->content = '<div class="container">';
            $this->content .= '<h3 class="display-3">Veuillez choisir un crawler</h3>';
            $this->content .= '<hr >';
            $this->content .= '<div class="list-group">';
            foreach($crawlers as $id => $crawler) {
                $this->content .= '<a class="list-group-item" href="'.$this->router->getTaskListURL($crawler->getId()) . '">';
                $this->content .= $crawler->getSource();
                $this->content .= '</a>';
            }
            $this->content .= '</div>';
        }

        public function makeTaskListPage(array &$tasks, $source){
            $this->title = 'Liste des tâches disponibles';
            $this->content = '<div class="container">';
            $this->content .= '<h4 id="instructions_util" class="display-4">Liste des tâches disponibles</h4>';
            $this->content .= '<h2 id="info_source">Le crawler choisi va crawl le service : ' . $source . '</h2>';
            $this->content .= '<hr class="col-6 mb-4">';
            $this->content .= '<form method="post" action = "'.htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
            foreach($tasks as $id => $task) {
                $this->content .= '<div class="form-check">';
                $this->content .= '<input class="form-check-input" type="checkbox" name = "taskIdArray[]" value="'.$task->getId().'" id = "taskCheckbox' . $task->getId() . '">';
                $this->content .= '<label class="list-group-item" for="taskCheckbox' . $task->getId() . '"> Tache n°' . $task->getId() . ' | point d\'entree : '.$task->getEntry(). ' | limite de crawl : '.$task->getLimit(). ' donnees | derniere execution le '. date('m/d/Y h:i:s a',$task->getEndDate()) . '</label>';
                $this->content .= '</div>';
            }

            $this->content .= "<input type='submit' class='btn btn-primary' value='Envoyer'> </form>";
            $this->content .= "</div>";

        }

        public function makeTaskExecutionPage($JSLogic) {
            $this->title = 'Execution de taches';
            $this->content = '<div class="container">';
            $this->content .= '<h4 id="instructions_util" class="display-4">Veuillez patienter </h4>';
            
            //Contient toutes les balises <script> pour la verification asynchrone de la progression des scripts.
            $this->content .= $JSLogic;
            
        }

        public function makeDataInsertionPage($crawlerId) {
            $local_session_data = json_decode(file_get_contents("cache/local_session_info.json"), true);
            //TODO: Rajouter des infos pour l'utilisateur
            //Par exemple : nombre de donnees recuperees par cette tache | Statut d'execution

            $this->title = 'Confirmation d\'insertion';
            
            $this->content = '<div class="container">';
            $this->content .= '<h4 id="instructions_util" class="display-4">Crawler du service : ' . $local_session_data["crawlerSource"] . '</h4>'; 

            $this->content .= '<div class="list-group">';
            foreach($local_session_data["taskIdArray"] as $taskId) {
                $this->content .=  '<div class="list-group-item">Donnees de tache '. $taskId . '</div>';
                //afficher egalement status
            };
            $this->content .= '</div>';

            $this->content .= '<hr>';
            $this->content .= '<form action="'.$this->router->getInsertURL($crawlerId).'" method="post">'.
                        '<button type="submit" class="btn btn-primary">Confirmer l\'insertion</button></form>';

            
            $this->content .= '</div>';
        }

        public function makeInsertionCompletePage(){
            $this->title = 'Insertion reussie';
            $this->content = '<p id="instructions_util">Insertion reussie, vous pouvez maintenant quitter le navigateur, ou faire une nouvelle action.</p>';
            $this->content .= '<h2><a href="' .$this->router->getHomeURL(). '">Cliquer ici pour revenir sur la page d\'acceuil</a></h2>';
        }

        



        //Actions liees a l'exportation de donnees:

        public function makeSourceListPage(array &$sources){
            //Affichage de format "Crawler X | Tache Y  | PointEntree X
            $this->title = 'Liste des sources disponibles';
            $this->content = '<h4 class="display-4">Veuillez selectionner une source de donnees</h4>';
        //TODO-Bonus: Rajouter un span class="badge" pour afficher le nombre de donnees total extrait par cette tache.
            $this->content .= '<div class="list-group">';
            foreach($sources as $id => $source) {
                $this->content .= '<a class="list-group-item list-group-item-action" href="/src/controller/Download.php?taskId=' . $source[1] . '">';
                //TODO:Remplacer cette etape par un appel a l'API, ou ajouter un attribut a objet tache, ou faire depuis un fichier de configuration externe.
                switch ($source[0]) {
                    case 1:
                        $this->content .= 'Crawler du site reddit.com' . ' | Tache : ' . $source[1] . ' | Point d\'entree: reddit.com/r/' . $source[2] . '</a>';
                        break;
                    case 2:
                        $this->content .= 'Crawler du logiciel Discord' . ' | Tache : ' . $source[1] . ' | Point d\'entree: '. $source[2] . '</a>';
                        break;

                    case 3:
                        $this->content .= 'Crawler du site quora.com' . ' | Tache : ' . $source[1] . ' | Point d\'entree: ' . $source[2] . '</a>';
                        break;
                    }
            }
            $this->content .= '</div>';
        }


        public function makeLoginPage(){
            $this->title = 'Login';

            //TODO: faire submit -> appel API -> redirection page acceuil avec feedback
            //faire <form action="'.$this->router->getInsertURL($crawlerId).'" method="post">'
            $this ->content = '
            <div class="container text-center">
                <div class="form-signin col align-self-center">
                    <form action="'.$this->router->getLoginURL() . '"  method="post">
                    <h1 class="h3 mb-3 fw-normal">Accès administratif </h1>

                    <div class="form-floating">
                        <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                        <label for="floatingPassword">Password</label>
                    </div>
                    <button class="w-50 btn btn-lg btn-primary mt-4" type="submit">Connexion</button>
                    </form>
                </div>
          </div>';




        }
        //Pages annexes

        public function makeAboutPage(){
            $this->title = 'À propos';
            $this->content = '<div class="container py-4">';
            $this->content .= 
            '<header class="pb-3 mb-4 border-bottom">
                <span class="fs-4">À propos</span>
            </header>';
            $this-> content .= 
            '<div class="p-5 mb-4 bg-light rounded-3">
                <div class="container-fluid py-5">
                    <h1 class="display-5 fw-bold">Description</h1>
                    <p class="col-md-8 fs-4">Cette application Web PHP est une "telecommande" permettant d\'interroger une base de donnees a distance, et d\'appeler des crawlers depuis l\'ordinateur de l\'utilisateur pour pouvoir extraire des donnees de divers sites ou applications.</p>
                    <a href="https:www.postman.com/" class="btn btn-primary btn-lg" type="button">Documentation de l\'API</a>
                </div>
            </div>';
            $this->content .=
            '<div class="row align-items-md-stretch">
                <div class="col-md-6">
                    <div class="h-100 p-5 text-white bg-dark rounded-3">
                        <h2>Insertion</h2>
                        <ul>   
                            <li>Selection d\'un crawler</li>
                            <li>Selection d\'une tache</li>
                            <li>Execution de cette tache en local</li>
                            <li>Envoi des donnees vers une base de donnees a distance</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="h-100 p-5 bg-light border rounded-3">
                        <h2>Extraction</h2>
                        <ul>   
                            <li>Selection d\'une source de donnees</li>
                            <li>Extraction sous format JSON</li>
                        </ul>
                    </div>
                </div>
            </div>';
            $this->content .= '</div>';
        }

        public function make404(){
                $this->title = "Ressource inconnue";
                $this->content = "<h1>Erreur 404 :</h1>";
                $this->content .= "<p>La ressource demandée n'existe pas</p>";
            }
        
        
    

        public function makeErrorPage(Exception $e){
                $this->title = 'Erreur';
                $this->content = $e->getMessage();
            }

        public function makeInvalidTasks($crawlerId){
            $this->router->POSTredirect($this->router->getTaskListURL($crawlerId),'Veuillez choisir au moins une tache.');
        }

        public function setFeedback($feedback){
            $this->feedback = $feedback;
        }
        

        public function render(){
            ob_start();
            include("base.html");
            $page = ob_get_contents();
            ob_end_clean();
            echo $page;
        }
    }