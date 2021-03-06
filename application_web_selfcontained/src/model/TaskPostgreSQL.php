<?php

    require_once('AbstractDataBaseStorage.php');
    require_once('model/Task.php');
    require_once('model/TaskStorage.php');



    class TaskPostgreSQL extends AbstractDataBaseStorage implements TaskStorage{

        private $readAll;

        public function __construct(PDO &$db){
            parent::__construct($db, 'tache', 'id');
            $this->readAll = $db->prepare('SELECT * FROM tache WHERE '.Task::CRAWLERID_REF.'=:id ORDER BY '.Task::BEGINDATE_REF);
        }



        //use in admin control panel.
        public function read($id) : Task{
            $task = $this->readObj($id);
            if($task != null){
                return $task;
            }else{
                throw new Exception("No such task", 1);
                
            }
        }



        public function readEverything(int $length=-1, int $n=0) : array{
            return $this->readALLObj($length, $n);
        }


        public function readAll($crawlerId) : array{
            $this->readAll->execute(array(':id' => $crawlerId));
            $tasksAttributes = $this->readAll->fetchAll(PDO::FETCH_ASSOC);
            $tasks = [];
            foreach($tasksAttributes as $taskAttributes){
                $tasks[] = $this->getObjectFromValues($taskAttributes);
            }
            return $tasks;
        }

        

        public function create(Task &$obj){
            return $this->createObj($obj);
        }

        public function update(Task &$task, $id) : bool{
            return parent::updateObj($task, $id);
        }

        protected function getValuesToInsert(&$obj) : array{
            if($obj === NULL){
                $obj = new Task(0, 0, '', '', 0, 0, 0);
            }
            return $obj->toArray();
        }

        protected function getObjectFromValues(array &$crawledTextAttributes){
            return Task::fromArray($crawledTextAttributes);
        }
    }
