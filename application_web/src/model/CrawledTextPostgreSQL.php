<?php
    
    require_once('AbstractDataBaseStorage.php');
    require_once('model/CrawledText.php');
    require_once('model/CrawledTextStorage.php');

    //Finish implementing this later, after deciding whether to use a json type column
    //or to split data into 5 columns (doable since it's a json with only one depth level)


    class CrawledTextStorageMySQL extends AbstractDataBaseStorage implements CrawledTextStorage{

        public function __construct(PDO &$db){
            parent::__construct($db, 'crawledtext', 'globalid');
        }

        public function read($id) : CrawledText{
            $$crawledText = $this->readObj($id);
            if($$crawledText != null){
                return $$crawledText;
            }else{
                throw new Exception("No such crawled data", 1);
                
            }
        }

        public function readAll(int $length=-1, int $n=0) : array{
            return $this->readALLObj($length, $n);
        }

        public function create(CrawledText &$obj){
            return $this->createObj($obj);
        }

        public function createBatch(&$jsonData){
            //TODO: implement insertion loop based on known JSON format.
            return $this->createObj($obj);
        }

        public function update(CrawledText &$crawledText, $id) : bool{
            return parent::updateObj($crawledText, $id);
        }

        protected function getValuesToInsert(&$obj) : array{
            if($obj === NULL){
                $obj = new CrawledText('', 0.0, '', '', '', '', '');
            }
            return $obj->toArray();
        }

        protected function getObjectFromValues(array &$crawledTextAttributes){
            return CrawledText::fromArray($crawledTextAttributes);
        }
    }
