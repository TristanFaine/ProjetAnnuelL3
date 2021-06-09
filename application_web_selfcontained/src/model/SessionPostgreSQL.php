<?php

    require_once('AbstractDataBaseStorage.php');
    require_once('model/Session.php');
    require_once('model/SessionStorage.php');

    class SessionPostgreSQL extends AbstractDataBaseStorage implements SessionStorage{

        private $deleteFromToken;

        public function __construct(PDO &$db){
            parent::__construct($db, 'session', 'id');
            $this->deleteFromToken = $db->prepare('DELETE FROM session WHERE '.Session::TOKEN_REF.'=:token');
        }

        public function read($id) : Session{
            $session= $this->readObj($id);
            if($session != null){
                return $session;
            }else{
                throw new Exception("No such session", 1);
                
            }
        }

        public function create(){
            //$bytes = bin2hex(random_bytes(20));
            $obj = new Session (
                bin2hex(random_bytes(16))
            );
            return $this->createObj($obj);
        }

        public function update(Session &$session, $id) : bool{
            return parent::updateObj($session, $id);
        }

        public function deleteFromToken($token){
            $this->deleteFromToken->execute(array(':token' => $token));
        }

        protected function getValuesToInsert(&$obj) : array{
            if($obj === NULL){
                $obj = new Session(0,'unknown');
            }
            return $obj->toArray();
        }

        protected function getObjectFromValues(array &$crawlerAttributes){
            return Session::fromArray($crawlerAttributes);
        }
    }
