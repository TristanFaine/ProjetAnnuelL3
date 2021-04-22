<?php
    
    require_once('model/CrawledText.php');

    interface CrawledTextStorage{

        //Calling create once per text encountered might cause unnecessary overhead.
        //Perhaps a createBatch(String=JSON) function would be better suited
        public function create(CrawledText &$crawledText); //return $globalId

        public function read($globalId) : CrawledText;

        public function readAll(int $length=-1, int $n=0) : array;

        public function update(CrawledText &$crawledText, $id) : bool;

        public function delete($id);
    }
