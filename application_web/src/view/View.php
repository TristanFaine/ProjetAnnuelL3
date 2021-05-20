<?php
//Permet de gerer l'affichage et la stylisation des services de l'application web.
    require_once('Router.php');

    class View{
        protected $router;
        protected $feedback;
        protected $title;
        //protected $menu;
        protected $content;

        public function __construct(Router &$router, $feedback){
            $this->router = $router;
            $this->feedback = $feedback;
            //$this->menu = $this->getMenu();
        }

        /*
        public function getMenu(): array{
            return array(
                "Acceuil" => $this->router->getHomeURL(),
                "Selectionner un crawler" => $this->router->getCrawlerListURL(),
                "Interroger la BDD" => $this->router->getImportURL(),
                "À propos" => $this->router->getAboutURL()
            );

        }
        */

    //Pages disponibles:

    public function makeHomePage(){
        $this->title = 'Acceuil: Application Crawler Incremental';
        $this->content = '<h1>Page d\'acceuil de crawler incremental, aucune session detectee.</h1>';
        $this->content .= '<h2><a href="' .$this->router->getCrawlerListURL(). '">Cliquer ici pour selectionner un crawler</a></h2>';
        $this->content .= '<h2><a href="' .$this->router->getImportURL(). '">Cliquer ici pour recuperer des donnees deja crawlees</a></h2>';

    }

    public function makeResumePage(array &$local_session_data){
        $this->title = 'Reprise du crawling';
        $this->content = '<div id="instructions_util">Reprise de la session : ' . $local_session_data["sessionId"];
        $this->content .= '<br/>Crawler utilise : ' . $local_session_data["crawlerId"] . '(' .$local_session_data["crawlerSource"] .')';
        for($i = 0; $i <= count($local_session_data["taskIdArray"]) - 1; $i++){
            switch($local_session_data["taskStatusArray"][$i]) {
                case 0:
                    $taskStatus = "Finie";
                    break;
                case 1:
                    $taskStatus = "Interrompue en cours d'execution";
                    break;
                case 2:
                    $taskStatus = "Pas encore executee";
                    break;
            };
            $this->content .= "<br/>Tache : " . $local_session_data["taskIdArray"][$i] . " | Status : " . $taskStatus;
        }
        $this->content .= '<br/> Premiere execution le : ' . date('m/d/Y h:i:s a',$local_session_data["firstDate"]);
        $this->content .= '<br/> Derniere execution le : ' . date('m/d/Y h:i:s a',$local_session_data["lastDate"]);


        $this->content .= '<h2><a href="' . $this->router->getTaskListURL($local_session_data["crawlerId"]) . '">Cliquer ici pour reprendre le crawling</a></h2>';
        $this->content .= '<h2><a href="' . $this->router->getInsertURL($local_session_data["crawlerId"]) . '">Cliquer ici pour inserer les donnees et effacer le cache</a></h2>';
    }


    //Actions liees aux crawlers:
    public function makeCrawlerListPage(array &$crawlers){
        $this->title = 'Liste des crawlers disponibles';
        $this->content = '<p>Veuillez selectionner un type de crawler:';
        $this->content .= '<ul class=list>';
        foreach($crawlers as $id => $crawler) {
            $this->content .= '<li><a href="'.$this->router->getTaskListURL($crawler->getId()) . '">';
            $this->content .= $crawler->getSource();
            $this->content .= '</a></li>';
        }
        $this->content .= '</p>';

    }

    public function makeTaskListPage(array &$tasks, $source){
        //TODO: Ameliorer l'UI en indiquant les infos sur le crawler actuel.
        $this->title = 'Liste des tâches disponibles';
        $this->content = '<p>Liste des tâches disponibles, veuillez selectionner celles que vous voulez executer:</p>';
        $this->content .= '<p>Le crawler choisi va crawl le service : ' . $source . '</p>';
        $this->content .= '<form method="post" action = "'.htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
        foreach($tasks as $id => $task) {
            $this->content .= '<div class="form-check">';
            //"<tr><td> Tache n°" .$task->getId()." </td>";
            $this->content .= '<input class="form-check-input" type="checkbox" name = "taskIdArray[]" value="'.$task->getId().'" id = "taskCheckbox' . $task->getId() . '">';
            $this->content .= '<label class="form-check-label" for="flexCheckDefault"> Tache n°' . $task->getId() .' statut : '.$task->getStatus().', point d\'entree : '.$task->getEntry(). ', derniere execution le '. date('m/d/Y h:i:s a',$task->getEndDate()) . '</label>';
            $this->content .= '</div>';
        }

        $this->content .= "<input type='submit' class='btn btn-primary' value='Envoyer'> </form>";

    }

    public function makeTaskExecutionPage($JSLogic) {
        $this->title = 'Execution de taches';
        $this->content = '<p id="instructions_util">Veuillez patienter le temps que les scripts s\'executent.</p>';
        //Contient toutes les balises <script> pour la verification asynchrone de la progression des scripts.
        $this->content .= $JSLogic;
        
    }

    public function makeDataInsertionPage($crawlerId) {
        $local_session_data = json_decode(file_get_contents("cache/local_session_info.json"), true);

        $this->title = 'Confirmation d\'insertion';
        
        $this->content = '<div id="instructions_util">Si vous souhaitez inserer les donnees recuperees par le crawler ' . $local_session_data["crawlerSource"] . ' pour les taches : '; 
        foreach($local_session_data["taskIdArray"] as $taskId) {$this->content .= $taskId . ", ";};
        $this->content .= '<br/>Alors veuillez cliquer sur le bouton "Insertion" ci-dessous. </div>';

        $this->content .= '<form action="'.$this->router->getInsertURL($crawlerId).'", method="post">'.
                    '<label><button type="submit">Confirmer</button></label></form>';

        
        
    }

    public function makeInsertionCompletePage(){
        $this->title = 'Insertion reussie';
        $this->content = '<p id="instructions_util">Insertion reussie, vous pouvez maintenant quitter le navigateur, ou faire une nouvelle action.</p>';
        $this->content .= '<h2><a href="' .$this->router->getHomeURL(). '">Cliquer ici pour revenir sur la page d\'acceuil</a></h2>';

    }
    //Actions liees a l'importation de donnees:

    public function makeSourceListPage(array &$sources){

        //Affichage de format "Tache X : Source/EntryPoint
        $this->title = 'Liste des sources disponibles';
        $this->content .= '<h4 class="display-4">Veuillez selectionner une source de donnees: </h4>';
        
        $this->content .= '<ul>';

        
    //TODO:Remplacer cette etape par un appel a l'API, ou ajouter un attribut a objet tache, ou faire depuis un fichier de configuration externe.     
        foreach($sources as $id => $source) {
            $this->content .= '<li>';
            
            switch ($source[0]) {
                case 1:
                    $this->content .= 'Crawler du site reddit.com' . ' | Tache : ' . $source[1] . ' | Point d\'entree: reddit.com/r/' . $source[2];
                    break;
                case 2:
                    $this->content .= 'Crawler du logiciel Discord' . ' | Tache : ' . $source[1] . ' | Point d\'entree: ' . $source[2];
                    break;

                case 3:
                    $this->content .= 'Crawler du site quora.com' . ' | Tache : ' . $source[1] . ' | Point d\'entree: ' . $source[2];
                    break;
                }
            
            $this->content .= '</li>';
        }
        $this->content .= '</ul>';

        //TODO: Faire bouton pour confirmer => appel API => redirection succes
    }




    //Pages annexes

    public function makeAboutPage(){
        $this->title = 'À propos';
        $this->content = '<h2>Description :</h2>';
        $this->content .= '<p>Cette application Web PHP est une "telecommande" permettant d\'interroger une base de donnees a distance, et d\'appeler des crawlers depuis l\'ordinateur de l\'utilisateur pour pouvoir extraire des donnees sur divers sites ou applications : discord, reddit.com, etc...</p>';
        $this->content .= 'Pour cela, elle utilise une API hebergee sur les serveurs de l\'universite de Caen.';
        $this->content .= '<h2>Utilisation :</h2>';
        $this->content .= '<p>Deux actions sont possibles: </p>';
        $this->content .= '<ul> <li> Selection d\'un crawler -> Selection d\'une tache -> Execution de cette tache en local -> Envoi des donnees vers une base de donnees a distance </li>';
        $this->content .= '<li> Selection d\'une source de donnees -> Recuperation des donnees depuis la base de donnees, et extraction sous format JSON dans le dossier nomme dump. </li>';
        $this->content .= '</ul>';
        $this->content .= '<p>Deux actions sont possibles: </p>';
        $this->content .= '<p> Une documentation de l\'API utilisee pour communiquer avec la base de donnees est disponible ici:<br/> NOT YET IMPLEMENTED</p>';
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
        $this->router->POSTredirect($this->router->getTaskListURL($crawlerId),'Veuillez choisir au moins une tache..');
    }
    

    public function render(){
        ob_start();
        include("base.html");
        $page = ob_get_contents();
        ob_end_clean();
        echo $page;
    }
    }