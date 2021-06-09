<?php
$source = $_POST['source'];
$action = $_POST['action'];
$cache_path = realpath("../crawlers/crawler_".$source."/cache");

//Action: 0 = pause, Action: 1 = kill


switch ($action) {
    case 0:
        //var_dump($action);
        $pause_status = json_decode(file_get_contents($cache_path . "/pause_file.json"), true);
        file_put_contents($cache_path . "/pause_file.json", json_encode(array("pause" => 1 - $pause_status['pause'])));
        break;
    case 1:
        //var_dump($action);
        file_put_contents($cache_path . "/kill_file.json", json_encode(array("kill" => 1)));
        break;
}
