<?php
use Cerberus\Core\Init;

require_once 'cerberus/classloader.php';

// Avoid headers problem
ob_start();

// Load Init class
new Init('test');