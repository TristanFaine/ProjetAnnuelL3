<?php
require_once('model/Task.php');

    interface TaskStorage{

        public function create(Task &$task); //return $id

        public function read($id) : Task;

        public function readAll($crawlerId) : array;

        public function update(Task &$task, $id) : bool;

    }
