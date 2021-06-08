<?php
    
    abstract class AbstractDataBaseStorage{

        protected $db;
        private $tableName;
        private $idName;
        private $readFromID;
        private $readAll;
        private $create;
        private $deleteID;
        private $updateID;

        // if idName is empty, readObj, delete, updateObj & updateDataOfObject will raise exception if called
        protected function __construct(PDO &$db, string $tableName, string $idName=''){
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db = $db;
            $this->tableName = $tableName;
            $this->idName = $idName;
            $this->readAll = $db->prepare('SELECT * FROM '.$tableName);
            $items = '(';
            $values = '(';
            $itemsAndValues = '';
            $first = true;
            $null = NULL;
            foreach ($this->getValuesToInsert($null) as $item => $value) {
                $items .= (($first)?'':', ').$item;
                $values .= (($first)?'':', ').':'.$item;
                $itemsAndValues .= (($first)?'':', ').$item.'=:'.$item;
                $first = false;
            }
            $items .= ')';
            $values .= ')';
            $this->create = $db->prepare('INSERT INTO '.$this->tableName.' '.$items.' VALUES '.$values);
            if($idName !== null){
                $this->readFromID = $db->prepare('SELECT * FROM '.$tableName.' WHERE '.$idName.'=:id');
                $this->deleteID = $db->prepare('DELETE FROM '.$this->tableName.' WHERE '.$idName.'=:id');
                $this->updateID = $db->prepare('UPDATE '.$this->tableName.' SET '.$itemsAndValues.' WHERE '.$this->idName.'=:id');
            }
        }

        // returns the object represented by the values stored in
        // the data base table at the row of the right id
        protected function readObj($id){
            $this->readFromID->execute(array(':id' => $id));
            $res = $this->readFromID->fetch(PDO::FETCH_ASSOC);
            if($res){
                return $this->getObjectFromValues($res);
            }else{
                return null;
            }
        }

        // returns an array of less than $length objects represented at each
        // row from row $n*$length to row ($n+1)*$length-1 of the
        // table with their id as key
        // if $length is negative or null, all objects are returned
        // if $n is negative, a SQL Exception is raised
        // if $n*$length is greater than the number of row of the table, the
        // returned array will be empty
        protected function readAllObj(int $length=-1, int $n=0) : array{
            $objects = array();
            $objectsAttributes=null;
            if($length <= 0){
                $this->readAll->execute();
                $objectsAttributes = $this->readAll->fetchAll(PDO::FETCH_ASSOC);
            }else{
                $stmt = $this->db->query('SELECT * FROM '.$this->tableName.' ORDER BY '.$this->idName.' LIMIT '.($n*$length).','.$length);
                // to do : prepare -> execute
                $objectsAttributes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            if($objectsAttributes){
                foreach($objectsAttributes as $attributes){
                    $objects[$attributes[$this->idName]] = $this->getObjectFromValues($attributes);
                }
            }
            return $objects;
        }

        // returns the primary key of the new object
        // or null if no primary key has been set on this table
        protected function createObj(&$obj){
            $values = $this->getValuesToInsert($obj);
            $formatedValues = $this->getPreparedItemsNameFromValues($values);
            $this->create->execute($formatedValues);
            try{
                return $this->db->lastInsertId();
            }catch(Exception $e){
                return null;
            }
        }

        // returns true if successful, false otherwise
        public function delete($id) : bool{
            $success = $this->deleteID->execute(array(':id' => $id));
            if($success == 0){
                return false;
            }else{
                return true;
            }
        }

        // returns true if successful, false otherwise
        protected function updateObj(&$obj, $id) : bool{
            $values = $this->getValuesToInsert($obj);
            $formatedValues = $this->getPreparedItemsNameFromValues($values);
            $formatedValues[':id'] = $id;
            $success = $this->updateID->execute($formatedValues);
            if($success == 0){
                return false;
            }else{
                return true;
            }
        }

        // must return an object
        // the array passed in parameter is an assiocative array with
        // the value assiociated to the name of the column name
        // used in readObj and readAll
        protected abstract function getObjectFromValues(array &$attributes);

        // must return an assiociative array where the keys are the names
        // of the columns of the table assiociated to their values
        // if null is passed as parameter, only keys will be used
        // used in createObj, updateObj and constructor
        protected abstract function getValuesToInsert(&$obj) : array;      

        private function getPreparedItemsNameFromValues(array &$data) : array{
            $formatedData = array();
            foreach ($data as $key => $value) {
                $formatedData[':'.$key] = $value;
            }
            return $formatedData;
        }  
    }
