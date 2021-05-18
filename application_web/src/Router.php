<?php

//Rappel de ce que fait un routeur:
//Permet de rediriger vers certaines parties de l'application web, selon le status de l'utilisateur
//On peut peut-etre faire un checksum d'un dossier contenant un certain crawler, pour comparer avec la BDD.

//Liste d'actions possibles:
//Acceuil, SelectCrawler, DoTasks, Insert, Export, About, 404

//Utiliser un token provenant de l'API pour permettre l'authentification.


    require_once('view/View.php');
    require_once('controller/Controller.php');

    class Router{
        const HOME = 'home';
        const CRAWLER_LIST = 'crawlerList';
        const TASK_LIST = 'taskList';
        const EXECUTE = 'execute';
        const INSERT = 'insert';
        const IMPORT = 'import';
        const ABOUT = 'about';
        const PATH_DELIMITER = '/';

        //Mettre l'argument de BDD plus tard
        //CrawledTextStorage &$crawledtextStorage, TaskStorage &$taskStorage, AccountStorage &$accountStorage
        public function main(CrawlerStorage &$crawlerStorage, TaskStorage &$taskStorage){
            if(session_status() == PHP_SESSION_NONE){
                session_name('crawlerAppSession');
                session_start();
            }
            
            $feedback = key_exists('feedback', $_SESSION) ? $_SESSION['feedback'] : '';
            $_SESSION['feedback'] = '';

            $view = new View($this,$feedback);
            
            //mettre argument de BDD plus tard
            $controller = new Controller($this, $view, $crawlerStorage, $taskStorage);
            
            if(!key_exists('PATH_INFO', $_SERVER)){
                $_SERVER['PATH_INFO'] = '';
            }
            $path_infos = explode(Router::PATH_DELIMITER, $_SERVER['PATH_INFO']);

            $length = count($path_infos);

            //si aucune info alors affichage page d'acceuil
            $arg1 = ($length >= 2 && $path_infos[1] !== '') ? $path_infos[1] : Router::HOME;
            
            switch($arg1){
                case Router::HOME:
                    $controller->showHome();
                    break;
                case Router::CRAWLER_LIST:
                    $controller->showCrawlers();
                    break;
                case Router::IMPORT:
                    $controller->showSources();
                    break;
                case Router::ABOUT:
                    $controller->showAbout();
                    break;
                default:
                //Utiliser length pour afficher des cas specialises:
                //par exemple, quand on a index.php/crawlerid=4/action=taskList
                    $action = ($length >= 3) ? $path_infos[2] : '';
                    switch($action){
                        case Router::TASK_LIST:
                            if($_SERVER['REQUEST_METHOD'] === 'GET'){
                                $controller->showTasks($arg1);
                            }else if($_SERVER['REQUEST_METHOD'] === 'POST'){
                                $controller->doTasks($_POST['taskIdArray'], $arg1);
                            }
                            break;
                        case Router::INSERT:
                            if($_SERVER['REQUEST_METHOD'] === 'GET'){
                                //On affiche une page de confirmation d'insertion.
                                $controller->askDataInsertion();
                            }else if($_SERVER['REQUEST_METHOD'] === 'POST'){
                                //Le controleur recupere les donnees JSON.
                                $controller->insertData();
                            }
                            break;
                        default:
                         $controller->show404();
                            break;
                    }
                
            }
            $view->render();

        }




        //Fonctions aidant a la navigation/redirection de l'application web

        //URL de base:
        public function getFileURL() : string
        {
            return $_SERVER['SCRIPT_NAME'];
        }

        public function getHomeURL() : string
        {
            return $this->getFileURL().Router::PATH_DELIMITER.Router::HOME;
        }

        public function getCrawlerListURL(): string
        {
            return $this->getFileURL().Router::PATH_DELIMITER.Router::CRAWLER_LIST;
        }

        public function getCrawlerURL($crawlerID): string
        {
            return $this->getFileURL().Router::PATH_DELIMITER.$crawlerID;
        }

        public function getTaskListURL($crawlerID): string
        {
            return $this->getCrawlerURL($crawlerID).Router::PATH_DELIMITER.Router::TASK_LIST;
        }
        public function getInsertURL($crawlerID): string
        {
            return $this->getCrawlerURL($crawlerID).Router::PATH_DELIMITER.Router::INSERT;
        }

        public function getImportURL(): string
        {
            return $this->getFileURL().Router::PATH_DELIMITER.Router::IMPORT;
        }

        public function getAboutURL() : string
        {
            return $this->getFileURL().Router::PATH_DELIMITER.Router::ABOUT;
        }
        
        public function POSTredirect($url, $feedback)
            //redirige sur une autre page POST
        {
            $_SESSION['feedback'] = $feedback;
            return header("Location: ".htmlspecialchars_decode($url), true, 303);
        }
    }