<?php


    require_once('AbstractDataBaseStorage.php');
    require_once('model/Crawler.php');
    require_once('model/CrawlerStorage.php');

    class CrawlerPostgreSQL extends AbstractDataBaseStorage implements CrawlerStorage{

        public function __construct(PDO &$db){
            parent::__construct($db, 'crawler', 'id');
        }

        public function read($id) : Crawler{
            $crawler = $this->readObj($id);
            if($crawler != null){
                return $crawler;
            }else{
                throw new Exception("No such crawler", 1);
                
            }
        }

        public function readAll(int $length=-1, int $n=0) : array{
            return $this->readALLObj($length, $n);
        }

        public function create(Crawler &$obj){
            return $this->createObj($obj);
        }

        public function update(Crawler &$crawler, $id) : bool{
            return parent::updateObj($crawler, $id);
        }

        protected function getValuesToInsert(&$obj) : array{
            if($obj === NULL){
                $obj = new Crawler(999999, 'undefined', 'undefined');
            }
            return $obj->toArray();
        }

        protected function getObjectFromValues(array &$crawlerAttributes){
            return Crawler::fromArray($crawlerAttributes);
        }
    }
