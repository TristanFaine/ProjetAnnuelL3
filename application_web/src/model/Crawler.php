<?php
    //TODO: Put correct attributes when we decide them.
    class Crawler
    {
        private $id;
        private $source;     // Indicates where the crawler will collect data.
        
        // do not change, used as field name in DB
        const ID_REF = 'id';
        const SOURCE_REF = 'source';


        public function __construct(int $id, string $source){
            $this->id = $id;
            $this->source = $source;
        }

        public function getId() : float{
            return $this->id;
        }

        public function getSource() : string{
            return $this->source;
        }


        public function toArray() : array{
            return array(
                Crawler::ID_REF => $this->id,
                Crawler::SOURCE_REF => $this->source,
            );
        }

        public static function fromArray(array &$attributes){
            return new Crawler(
                $attributes[Crawler::ID_REF],
                $attributes[Crawler::SOURCE_REF]);
        }
    }
