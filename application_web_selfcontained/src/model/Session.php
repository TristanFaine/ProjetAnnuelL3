<?php
    class Session
    {
        private $id;
        private $token;
        //private $crawlerId;
        //private $crawlerSource;
        //private $taskIdArray;
        //private $taskStatusArray;
        //private $taskLastDataArray;
        //private $firstDate;
        //private $lastDate;
        
        // do not change, used as field name in DB
        const ID_REF = 'id';
        const TOKEN_REF = 'token';
        //const CRAWLERID_REF = 'crawlerid';
        //const CRAWLERSOURCE_REF = 'crawlersource';
        //const TASKIDARRAY_REF = 'taskidarray';
        //const TASKSTATUSARRAY_REF = 'taskstatusarray';
        //const TASKLASTDATAARRAY_REF = 'tasklastdataarray';
        //const FIRSTDATE_REF = 'firstdate';
        //const LASTDATE_REF  = 'lastdate';

        public function getToken() : string{
            return $this->token;
        }

        public function __construct(string $token){
            $this->token = $token;
        }

        public function toArray() : array{
            return array(
                Session::TOKEN_REF => $this->token
            );
        }

        public static function fromArray(array &$attributes){
            return new Session(
                $attributes[Session::TOKEN_REF]);
        }
    }
