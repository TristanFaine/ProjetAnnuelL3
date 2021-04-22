<?php
//Permet de gerer l'affichage et la stylisation des services de l'application web.

    require_once('Router.php');

    class PublicView{
        protected $router;
        protected $title;
        protected $menu;

        public function __construct(Router &$router){
            $this->router = $router;
            $this->menu = $this->getMenu();
        }

        public function getMenu(): array{
            return array(
                "Acceuil" => $this->router->getHomeURL(),
                "Utiliser un crawler" => $this->router->getInsertAttemptURL(),
                "Interroger la BDD" => $this->router->getExtractAttemptURL(),
                "À propos" => $this->router->getAboutURL()
            );

        }

    //Pages disponibles:

    public function makeHomePage(){
        $this->title = 'Acceuil: Application Crawler Incremental';
        $this->content = '<h1>Bienvenue sur le prototype de la page d\'acceuil</h1>';
    }


    public function makeAboutPage(){
        $this->title = 'À propos';
        $this->content = '<h1>Remplir cette page plus tard';

    }


    //Pages annexes

    public function make404(){
            $this->title = "Ressource inconnue";
            $this->content = "<h1>Erreur 404 :</h1>";
            $this->content .= "<p>La ressource demandée n'existe pas</p>";
        }



    public function makeErrorPage(Exception $e){
            $this->title = 'Erreur';
            $this->content = $e->getMessage();
        }

    public function render(){
        ob_start();
        include("base.html");
        $page = ob_get_contents();
        ob_end_clean();
        echo $page;
    }
    }