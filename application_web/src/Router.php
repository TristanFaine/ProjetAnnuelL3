<?php

//Rappel de ce que fait un routeur:
//Permet de rediriger vers certaines parties de l'application web, selon le status de l'utilisateur
//Pour l'instant, on laisse un acces libre a cet application.

    require_once('view/View.php');
    require_once('controller/Controller.php');
    //require_once('model/TextStorage.php');

    class Router{
        const HOME = 'home';
        const INSERT = 'insert';
        const EXPORT = 'export';
        const ABOUT = 'about';
        const PATH_DELIMITER = '/';

        //Mettre l'argument de BDD plus tard
        public function main(){
            //Vu qu'on ne fait pas d'authentification, on n'a pas vraiment besoin de faire une session mais bon.
            if(session_status() == PHP_SESSION_NONE){
                session_name('crawlerAppSession');
                session_start();
            }
            
            $feedback = key_exists('feedback', $_SESSION) ? $_SESSION['feedback'] : '';
            $_SESSION['feedback'] = '';

            $view = new PublicView($this,$feedback);
            
            //mettre argument de BDD plus tard
            $controller = new Controller($this, $view);
            
            if(!key_exists('PATH_INFO', $_SERVER)){
                $_SERVER['PATH_INFO'] = '';
            }
            $path_infos = explode(Router::PATH_DELIMITER, $_SERVER['PATH_INFO']);

            $length = count($path_info);

            //si aucune info alors affichage page d'acceuil
            $arg1 = ($length >= 2 && $path_infos[1] !== '') ? $path_infos[1] : Router::HOME;
            
            switch($arg1){
                case Router::HOME:
                    $controller->showHome();
                    break;
                case Router::INSERT:
                    if($_SERVER['REQUEST_METHOD'] === 'GET'){
                        $controller->showInsertPage();
                    } else if($_SERVER['REQUEST_METHOD'] === 'POST'){
                        $controller->callCrawler($_POST);
                    }
                    break;
                case Router::EXPORT:
                    //demande OU execution
                    if($_SERVER['REQUEST_METHOD'] === 'GET'){
                        $controller->showExportPage();
                    } else if($_SERVER['REQUEST_METHOD'] === 'POST'){
                        $controller->dumpDatabase($_POST);
                    }
                    break;
                case Router::ABOUT:
                    $controller->showAbout();
                    break;
                default:
                $controller->show404();
            }
            $view->render();

        }

        //Fonctions aidant a la navigation/redirection de l'application web

        public function getFileURL() : string{
            return $_SERVER['SCRIPT_NAME'];
        }

        public function getHomeURL() : string{
            return $this->getFileURL().Router::PATH_DELIMITER.Router::HOME;
        }

        public function getInsertURL(): string
        {
            return $this->getFileURL().Router::PATH_DELIMITER.Router::INSERT;
        }

        public function getExportURL(): string
        {
            return $this->getFileURL().Router::PATH_DELIMITER.Router::EXPORT;
        }

        public function getAboutURL() : string
        {
            return $this->getFileURL().Router::PATH_DELIMITER.Router::ABOUT;
        }
        
        public function POSTredirect($url, $feedback)
        {
            $_SESSION['feedback'] = $feedback;
            return header("Location: ".htmlspecialchars_decode($url), true, 303);
        }
    }