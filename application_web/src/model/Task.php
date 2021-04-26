<?php
    //TODO: Put correct attributes when we are sure.
    class Task
    {
        private $id;
        private $crawlerId;
        private $description;   // Value of text.
        private $status;        // This indicates if this task is available or not.
        private $entry;         // entrypoint of a task, eg : "reddit.com/subreddit=France" or "reddit.com/subreddit=Art".
        private $lastTime;      // Last time this task was FULLY executed.
        private $creationDate; 
        //more attributes, such as lastUser could be implemented, but that seems like over-engineering for now.


        // do not change, used as field name in DB
        const ID_REF = 'id';
        const CRAWLERID_REF = 'crawlerid';
        const DESCRIPTION_REF = 'description';
        const STATUS_REF = 'status';
        const ENTRY_REF = 'path';
        const LASTTIME_REF = 'lasttime';
        const CREATIONDATE_REF = 'creationdate';

        public function __construct(int $id, $crawlerId, string $text, $description, $status, $path, $lastTime, $creationDate){
            $this->id = $id;
            $this->crawlerId = $crawlerId;
            $this->description = $description;
            $this->status = $status;
            $this->entry = $entry;
            $this->lastTime = $lastTime;
            $this->creationDate = $creationDate;
        }

        public function getId() : float{
            return $this->id;
        }

        public function getCrawlerId() : float{
            return $this->crawlerId;
        }

        public function getDescription() : string{
            return $this->description;
        }
        
        public function getStatus() : string{
            return $this->status;
        }

        public function getEntry() : string{
            return $this->entry;
        }

        public function getLastTime() : string{
            return $this->lastTime;
        }

        public function getCreationDate() : string{
            return $this->creationDate;
        }


        public function toArray() : array{
            return array(
                Task::ID_REF => $this->id,
                Task::CRAWLERID_REF => $this->crawlerId,
                Task::DESCRIPTION_REF => $this->description,
                Task::STATUS_REF => $this->status,
                Task::ENTRY_REF => $this->entry,
                Task::LASTTIME_REF => $this->lastTime,
                Task::CREATIONDATE_REF => $this->creationDate,
            );
        }

        public static function fromArray(array &$attributes){
            return new Task(
                $attributes[Task::ID_REF],
                $attributes[Task::CRAWLERID_REF],
                $attributes[Task::DESCRIPTION_REF],
                $attributes[Task::STATUS_REF],
                $attributes[Task::ENTRY_REF],
                $attributes[Task::LASTTIME_REF],
                $attributes[Task::CREATIONDATE_REF]);
        }
    }
