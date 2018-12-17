<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use VtigerTranslate\Translate;

$totranslate = '/Users/idokobelkowsky/dev/p2p-backend';
$translate = new Translate('/Users/idokobelkowsky/dev/p2p-backend','he_il');

// Overwrite files
//$translate->setOverwrite(false);
echo $translate->full();

// Run on a single module
//echo $translate->module('Accounts');
