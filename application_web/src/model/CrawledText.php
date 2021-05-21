<?php
    class CrawledText
    {
        //following attributes can be put in a JSON format
        private $text;
        private $path;     // "Chemin" utilise pour acceder a l'emplacement du text (une page, une chaine, etc).
        private $index;    // Si plusieurs textes appartiennent au meme container, cela indique leur position.
        private $realId;   // Identifie l'emplacement du texte sur un site ou service.
        private $taskId;

        // do not change, used as field name in DB
        const TEXT_REF = 'text';
        const PATH_REF = 'path';
        const INDEX_REF = 'index';
        const REALID_REF = 'realid';
        const TASKID_REF = 'taskid';

        public function __construct(string $text, $path, int $index, string $realId, int $taskId){
            $this->text = $text;
            $this->path = $path;
            $this->index = $index;
            $this->realId = $realId;
            $this->taskId = $taskId;
        }

        public function getText() : string{
            return $this->text;
        }
        
        public function getPath() : string{
            return $this->path;
        }

        public function getIndex() : string{
            return $this->index;
        }

        public function getRealId() : string{
            return $this->realId;
        }

        public function getTaskId() : string{
            return $this->TaskId;
        }

        public function toArray() : array{
            return array(
                CrawledText::TEXT_REF => $this->text,
                CrawledText::PATH_REF => $this->path,
                CrawledText::INDEX_REF => $this->index,
                CrawledText::REALID_REF => $this->realId,
                CrawledText::TASKID_REF => $this->taskId
            );
        }

        public static function fromArray(array &$attributes){
            return new CrawledText(
                $attributes[CrawledText::TEXT_REF],
                $attributes[CrawledText::PATH_REF],
                $attributes[CrawledText::INDEX_REF],
                $attributes[CrawledText::REALID_REF],
                $attributes[CrawledText::TASKID_REF]);
        }
    }
