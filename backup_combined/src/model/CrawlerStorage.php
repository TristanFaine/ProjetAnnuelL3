<?php
    require_once('model/Crawler.php');

    interface CrawlerStorage{
        
        public function create(Crawler &$crawler); //return $id

        public function read($id) : Crawler;

        public function readAll(int $length=-1, int $n=0) : array;

        public function update(Crawler &$crawler, $id) : bool;

        public function delete($id);
    }
