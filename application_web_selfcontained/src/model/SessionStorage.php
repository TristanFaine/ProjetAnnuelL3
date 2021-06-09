<?php
    require_once('model/Session.php');

    interface SessionStorage{
        
        public function create(); //Ceci redonne l'id dans la bdd et non le token.

        public function read($id) : Session;

        public function update(Session &$session, $id) : bool;

        public function deleteFromToken($token);
    }
