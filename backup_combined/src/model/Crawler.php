<?php
    //TODO: Put correct attributes when we decide them.
    class Crawler
    {
        private $id;
        private $source;     // Indicates where the crawler will collect data.
        private $checksum; //I don't know where I'm going with this..
        
        // do not change, used as field name in DB
        const ID_REF = 'id';
        const SOURCE_REF = 'source';
        const CHECKSUM_REF = 'folderchecksum';


        public function __construct(int $id, string $source, $checksum){
            $this->id = $id;
            $this->source = $source;
            $this->checksum = $checksum;
        }

        public function getId() : float{
            return $this->id;
        }

        public function getSource() : string{
            return $this->source;
        }

        public function setSource(string $source){
            $this->source = $source;
        }

        public function getChecksum() : string{
            return $this->checksum;
        }


        public function toArray() : array{
            return array(
                Crawler::ID_REF => $this->id,
                Crawler::SOURCE_REF => $this->source,
                Crawler::CHECKSUM_REF => $this->checksum,
            );
        }

        public static function fromArray(array &$attributes){
            return new Crawler(
                $attributes[Crawler::ID_REF],
                $attributes[Crawler::SOURCE_REF],
                $attributes[Crawler::CHECKSUM_REF]);
        }
    }
