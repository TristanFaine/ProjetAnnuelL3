<?php
    //TODO: Put correct attributes when we are sure.
    class Task
    {
        private $id;
        private $crawlerId;
        private $status;        // This indicates if this task is available or not.
        private $entry;         // entrypoint of a task, eg : "reddit.com/subreddit=France" or "reddit.com/subreddit=Art".
        private $begindate;      // Last time this task was FULLY executed.
        private $enddate; 
        //more attributes, such as lastUser could be implemented, but that seems like over-engineering for now.


        // do not change, used as field name in DB
        const ID_REF = 'id';
        const CRAWLERID_REF = 'crawlerid';
        const DESCRIPTION_REF = 'description';
        const STATUS_REF = 'status';
        const ENTRY_REF = 'entrypoint';
        const BEGINDATE_REF = 'begindate';
        const ENDDATE_REF = 'enddate';

        public function __construct(int $id, $crawlerId, string $status, $entry, $beginDate, $endDate){
            $this->id = $id;
            $this->crawlerId = $crawlerId;
            $this->status = $status;
            $this->entry = $entry;
            $this->beginDate = $beginDate;
            $this->endDate = $endDate;
        }

        public function getId() : float{
            return $this->id;
        }

        public function getCrawlerId() : float{
            return $this->crawlerId;
        }

        
        public function getStatus() : string{
            return $this->status;
        }

        public function getEntry() : string{
            return $this->entry;
        }

        public function getBeginDate() : string{
            return $this->beginDate;
        }

        public function getendDate() : string{
            return $this->endDate;
        }


        public function toArray() : array{
            return array(
                Task::ID_REF => $this->id,
                Task::CRAWLERID_REF => $this->crawlerId,
                Task::STATUS_REF => $this->status,
                Task::ENTRY_REF => $this->entry,
                Task::BEGINDATE_REF => $this->beginDate,
                Task::ENDDATE_REF => $this->endDate,
            );
        }

        public static function fromArray(array &$attributes){
            return new Task(
                $attributes[Task::ID_REF],
                $attributes[Task::CRAWLERID_REF],
                $attributes[Task::STATUS_REF],
                $attributes[Task::ENTRY_REF],
                $attributes[Task::BEGINDATE_REF],
                $attributes[Task::ENDDATE_REF]);
        }
    }
