<?php
require "vendor/autoload.php";

$dataStore = new \Previewtechs\GoogleDataStore\DataStore([
    'apiKey' => '',
    'projectId' => ''
]);

$dataStore->find();
$result = $dataStore->getResponse();
var_dump($result);
die();