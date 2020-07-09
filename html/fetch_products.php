<?php
# Very basic proxy function to get around the CORS issue

# Read in configuration
$config_json = file_get_contents("../config/config.json");
$config = json_decode($config_json, true);

$dateNowSec = time();
$uri = $config['URL_PRODUCTS'] . '?token=' . $config['TOKEN'] . '&time=' . $dateNowSec;
echo file_get_contents($uri);
