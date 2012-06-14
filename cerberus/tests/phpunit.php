<?php
// Avoid headers problem
ob_start();

// Bogus config file
define('PATH_CONF', 'tests.json');

// Load Init class
if(!class_exists('Init')) require('cerberus/class/core.init.php');
new Init('test');