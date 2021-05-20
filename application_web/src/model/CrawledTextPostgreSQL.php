<?php
    
    require_once('AbstractDataBaseStorage.php');
    require_once('model/CrawledText.php');
    require_once('model/CrawledTextStorage.php');

    //Finish implementing this later, after deciding whether to use a json type column
    //or to split data into 5 columns (doable since it's a json with only one depth level)


    class CrawledTextPostgreSQL extends AbstractDataBaseStorage implements CrawledTextStorage{

        private $createBatch;
        private $getLastKnownData;

        public function __construct(PDO &$db){
            parent::__construct($db, 'crawledtext', 'globalid');
            //TODO: find a way to insert properly
            $this->createBatch = $db->prepare('DELETE FROM session WHERE '.Session::TOKEN_REF.'=:token');
            //select realid from crawledtext where id = (select MAX(id) from crawledtext where taskId = 1);
            $this->getLastKnownData = $db->prepare('SELECT '.CrawledText::REALID_REF.' FROM crawledtext WHERE id = ( SELECT MAX(id) FROM crawledtext WHERE '.CrawledText::TASKID_REF.'=:taskid' .')');
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

        public function getLastKnownData($taskId){
            $this->getLastKnownData->execute(array(':taskid' => $taskId));
            $attribut = $this->getLastKnownData->fetch(PDO::FETCH_ASSOC);
            return $attribut;    
        }

        public function create(CrawledText &$obj){
            return $this->createObj($obj);
        }

        public function createBatch(&$jsonData){
            //On recoit un String json convertit en objet generique stdclass
            ////https://stackoverflow.com/questions/931407/what-is-stdclass-in-php
            
            //TODO: Diviser fichier json en array contenant 999 valeurs chacune
            $postgresMAXINSERT = 999;

            foreach ($jsonData as $dataObject) {
                echo $dataObject->text;
                    //$this->deleteFromToken->execute(array(':token' => $token));
                
            }
            

            //Pour chaque partie de l'array, faire insertion
            //$this->createBatch->execute(array(':token' => $token));
            return $this->createObj($obj);
        }

        public function update(CrawledText &$crawledText, $id) : bool{
            return parent::updateObj($crawledText, $id);
        }

        protected function getValuesToInsert(&$obj) : array{
            if($obj === NULL){
                $obj = new CrawledText('', '',0, '', 0);
            }
            return $obj->toArray();
        }

        protected function getObjectFromValues(array &$crawledTextAttributes){
            return CrawledText::fromArray($crawledTextAttributes);
        }
    }
