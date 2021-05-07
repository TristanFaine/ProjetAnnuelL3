<?php
//Permet de gerer l'affichage et la stylisation des services de l'application web.
    require_once('Router.php');

    class View{
        protected $router;
        protected $feedback;
        protected $title;
        protected $menu;
        protected $content;

        public function __construct(Router &$router, $feedback){
            $this->router = $router;
            $this->feedback = $feedback;
            $this->menu = $this->getMenu();
        }

        public function getMenu(): array{
            return array(
                "Acceuil" => $this->router->getHomeURL(),
                "Selectionner un crawler" => $this->router->getCrawlerListURL(),
                "Interroger la BDD" => $this->router->getImportURL(),
                "À propos" => $this->router->getAboutURL()
            );

        }

    //Pages disponibles:

    public function makeHomePage(){
        $this->title = 'Acceuil: Application Crawler Incremental';
        $this->content = '<h1>Bienvenue sur le prototype de la page d\'acceuil</h1>';
        //Afficher 2 boutons : 1 qui dit "selectionner crawler", l'autre qui dit "extraction de donnees" en js.
        $this->content .= '<h2><a href="' .$this->router->getcrawlerListURL(). '">Cliquer ici pour selectionner un crawler</a></h2>';
        $this->content .= '<h2><a href="' .$this->router->getImportURL(). '">Cliquer ici pour recuperer des donnees deja crawlees</a></h2>';

    }


    //Actions liees aux crawlers:
    public function makeCrawlerListPage(array &$crawlers){
        //Quand on clique, on affiche les taches disponibles.
        $this->title = 'Liste des crawlers disponibles';
        $this->content = '<p>Veuillez selectionner un type de crawler:';
        $this->content .= '<ul class=list>';
        foreach($crawlers as $id => $crawler) {
            $this->content .= '<li><a href="'.$this->router->getTaskListURL($crawler->getId()) . '">';
            $this->content .= $crawler->getSource();
            $this->content .= '</a></li>';
        }
        $this->content .= '</p>';

        //'<a href="'.$this->router->getWaterbottleAskDeletionURL($id).'">Supprimer cette bouteille d\'eau ?</a><br>'.
    }

    public function makeTaskListPage(array &$tasks){
        $this->title = 'Liste des tâches disponibles';
        $this->content = '<p>Liste des tâches disponibles, veuillez selectionner celles que vous voulez executer:</p>';
        $this->content .= "<form method='post' action = '".htmlspecialchars($_SERVER["PHP_SELF"]) . "' > <table> ";
        foreach($tasks as $id => $task) {
            $this->content .= "<tr><td> Tache n°" .$task->getId()." </td>";
            //??? pourquoi il me force le tr
            $this->content .= "<td> <input type='checkbox' name = 'taskIdArray[]' value='".$task->getId()."'> statut : ".$task->getStatus().", point d'entree : ".$task->getEntry(). ", derniere execution le ".$task->getEndDate() . "</td>";
            $this->content .= "</tr>";
        }

        $this->content .= " </table> <input type='submit' class='buttons'> </form>";

        //Ensuite, faire un bouton submit.. je suppose..?
    }
    

    public function makeSourceListPage(array &$sources){
        //Array recieved should be of format Source : Path
        //Represent like this:
        // - Source
        // -    Path 1
        // -    Path 2
        // -        Path 2/1
        //A fancy dragdown menu akin to a file explorer menu should be doable..
        $this->title = 'Liste des sources disponibles';
        $this->content = '<p>Veuillez selectionner une source de donnees:';
        $this->content .= '<ul class=list>';
        foreach($sources as $id => $source) {
            $this->content .= '<li>';
            //if $source[1] contains '/' then split and put in sub-container, somehow.
            
            $this->content .= $source[1];
            $this->content .= '</li>';
        }
        $this->content .= '</p>';
    }

    //Actions liees a l'importation de donnees:


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




    public function makeTestPage(){
            $this->title = 'Test appel AJAX multiple';
            $this->content = "<p>JE DEVRAIS TOUJOURS ETRE VISIBLE, MAIS UNE SEULE FOIS.</p>";

            //make ajax/websocket/normal js here
            /*
            
            */
            

            ///... asynchronous.. signal handler.... HOW.
            ?>
            <script type="text/javascript">
            //how the fuck do I make an asynchronous request to myself?
            console.log("ayo");
            function loadXMLDoc() {
                var xhr = new XMLHttpRequest();

                xhr.onreadystatechange = function() {
                    if (xhr.readyState == XMLHttpRequest.DONE) {   // XMLHttpRequest.DONE == 4
                    if (xhr.status == 200) {
                        alert(xhr.responseText);
                    }
                    else if (xhr.status == 400) {
                        alert('There was an error 400');
                    }
                    else {
                        alert('something else other than 200 was returned');
                    }
                    }
                };

                xhr.open("GET", "ScriptManagxfdder.php", true);
                xhr.send();
            }

            loadXMLDoc();
            </script>
            <?php



    }
    public function makeInvalidTasks($crawlerId){
        $this->router->POSTredirect($this->router->getTaskListURL($crawlerId),'Veuillez choisir au moins une tache..');
        echo $feedback;
    }
    

    public function render(){
        ob_start();
        include("base.html");
        $page = ob_get_contents();
        ob_end_clean();
        echo $page;
    }
    }