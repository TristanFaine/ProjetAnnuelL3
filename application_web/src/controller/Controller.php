<?php

//Rappel de ce que fait un controlleur:
//Possede une liste de fonctions permettant de faire des choses, quand on l'appele
//cela est pratique pour diviser les diverses fonctionabilitees, et rends le tout lisible.

    require_once('Router.php');
    require_once('view/View.php');
    require_once('model/CrawlerStorage.php');
    require_once('model/TaskStorage.php');
    require_once('model/CrawledTextStorage.php');
    

    class Controller{
        private $view;
        private $crawlerStorage;
        private $taskStorage;
        private $crawledTextStorage;
        

        
        //TODO: ajouter BDD au constructeur plus tard
        //, CrawlerStorage &$crawlerStorage, TaskStorage &$taskStorage, CrawledTextStorage &$crawledtextStorage
        public function __construct(Router &$router, View &$view){
            $this->view = $view;
            //$this->crawledTextStorage = crawledTextStorage;
            //$this->crawlerStorage = $crawlerStorage;
        }
        
        public function showHome(){
           $this->view->makeHomePage();
        }

        public function showCrawlers(){
            //Read every value in crawler table
                //$crawlers = $this->crawlerStorage->readAll();

            //valeurs bidon pour exemple:
            $bidon1 = new Crawler(1,'Discord');
            $bidon2 = new Crawler(2,'Quora');
            $bidon3 = new Crawler(3,'Reddit');

            $bidon = array($bidon1,$bidon2,$bidon3);


            $this->view->makeCrawlerListPage($bidon);

        }

        public function showTasks($crawlerId){
            //Reads every value from tasklist table corresponding to the id:

            //$tasks = $this->crawlerStorage->readAll($crawlerId);

            //$this->view->makeTaskListPage($tasks);

        }

        

        public function callCrawler(){
            //implement code from crawl.php prototype  
            //check if request is valid:

                //try to call a crawler:

                    //if successful, show progression somehow, maybe AJAX?

                        //then attempt to put data in database, or save it in cache:

                //otherwise, go to the error page

            //if invalid, redirect to the request page, while giving information on why the request is invalid
            //Since we combined  stderr and stdout, we can probably just give the same output.

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
            //do later:
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
