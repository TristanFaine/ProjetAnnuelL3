<?php

    class CrawledText
    {
        private $globalId;
        private $text;     // Value of text.
        private $source;   // Name of text's origin : service or website.
        private $path;     // "path" used to access the text's container (A page, a channel, etc..).
        private $index;    // If multiple texts belong to the same container, this assigns them a position. Defaults to 0.
        private $realId;   // Identifies the text's container on a service or website. Defaults to "unknown"

        // do not change, used as field name in DB
        const GLOBALID_REF = 'globalid';
        const TEXT_REF = 'text';
        const SOURCE_REF = 'source';
        const PATH_REF = 'path';
        const INDEX_REF = 'index';
        const REALID_REF = 'realid';

        public function __construct(int $globalId, string $text, $source, $path, int $index, string $realId){
            $this->globalId = $globalId;
            $this->text = $text;
            $this->source = $source;
            $this->path = $path;
            $this->index = $index;
            $this->realId = $realId;
        }

        public function getGlobalId() : float{
            return $this->globalId;
        }

        public function getText() : string{
            return $this->text;
        }
        
        public function getSource() : string{
            return $this->source;
        }

        public function getPath() : string{
            return $this->path;
        }

        public function getIndex() : string{
            return $this->index;
        }

        public function getrealId() : string{
            return $this->realId;
        }

        public function toArray() : array{
            return array(
                CrawledText::GLOBALID_REF => $this->globalId,
                CrawledText::TEXT_REF => $this->text,
                CrawledText::SOURCE_REF => $this->source,
                CrawledText::PATH_REF => $this->path,
                CrawledText::INDEX_REF => $this->index,
                CrawledText::REALID_REF => $this->realId,
            );
        }

        public static function fromArray(array &$attributes){
            return new Waterbottle(
                $attributes[CrawledText::GLOBALID_REF],
                $attributes[CrawledText::TEXT_REF],
                $attributes[CrawledText::SOURCE_REF],
                $attributes[CrawledText::PATH_REF],
                $attributes[CrawledText::INDEX_REF],
                $attributes[CrawledText::REALID_REF]);
        }
    }
