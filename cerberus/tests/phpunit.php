<?php
// Avoid headers problem
ob_start();

// Load Init class
if(!class_exists('Init')) require('cerberus/class/core.init.php');
new Init('test');