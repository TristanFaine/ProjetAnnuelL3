<?php
    
    require_once('model/CrawledText.php');

    interface CrawledTextStorage{

        //Appeler create 1 fois par texte peut potentiellement causer des problemes de performance
        //Peut-etre qu'une function createBatch(String qui se convertir en Array) serait utile
        //C'est la meme chose, mais la fonction est appelee seulement une seule fois, et la base de donnees aussi.
        
        public function create(CrawledText &$crawledText); //return $globalId

        public function createBatch(&$jsonData);

        public function getLastKnownData($taskId);

        public function readAllAssociatedData($taskId);

        public function read($globalId) : CrawledText;

        public function readAll(int $length=-1, int $n=0) : array;

        public function update(CrawledText &$crawledText, $id) : bool;

        public function delete($id);
    }
