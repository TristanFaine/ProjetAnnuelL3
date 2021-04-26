<?php
    
    require_once('model/CrawledText.php');

    interface CrawledTextStorage{

        //Calling create once per text encountered might cause unnecessary overhead.
        //Perhaps a createBatch(String=JSON) function would be better suited.
        //it's the same thing, but the function isn't called X times, just once.
        
        public function create(CrawledText &$crawledText); //return $globalId

        public function createBatch(&$jsonData);

        public function read($globalId) : CrawledText;

        public function readAll(int $length=-1, int $n=0) : array;

        public function update(CrawledText &$crawledText, $id) : bool;

        public function delete($id);
    }
