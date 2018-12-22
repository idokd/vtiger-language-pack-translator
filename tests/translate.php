<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use VtigerTranslate\Translate;

$options = array(
    'google_api_key' => '<GOOGLE_MAPS_API_KEY>',
    'source' => 'en_US',
    'target' => 'he_il',
    'vtiger' => '/Users/idokobelkowsky/dev/p2p-backend'
);
$translate = new Translate($options);

// Overwrite files
//$translate->setOverwrite(false);
echo $translate->full();

// Run on a single module
//echo $translate->module('Accounts');
