<?php

//Rappel de ce que fait un controlleur:
//Possede une liste de fonctions permettant de faire des choses, quand on l'appele
//cela est pratique pour diviser les diverses fonctionabilitees, et rends le tout lisible.

    require_once('Router.php');
    require_once('view/View.php');
    require_once('model/CrawledTextStorage.php');

    class Controller{
        private $view;
        private $crawledtextStorage;

        public function __construct(Router &$router, View &$view, CrawledTextStorage &$crawledtextStorage){
            $this->view = $view;
            $this->textStorage = $textStorage;
        }
        
        public function showHome(){
           $this->view->makeHomePage();
        }

        public function show404(){
            $this->view->make404();
        }

        public function callCrawler(){
            //implement code from crawl.php prototype  
            //check if request is valid:

                //try to call a crawler:

                    //if successful, show progression somehow:

                        //then attempt to put data in database.

                //otherwise, go to the error page

            //if invalid, redirect to the request page, while giving information on why the request is invalid.
        }
        public function dumpDatabase(){
            //do later:
            //check if request is valid:

                //try to read from database:

                    //if successful, put it somewhere, and redirect to a success page

                //otherwise, go to the error page

                
            //if invalid, redirect to the request page, while giving information on why the request is invalid.
            
        }


    }
