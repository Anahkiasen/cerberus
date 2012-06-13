<?php
ob_start();

if(!class_exists('Init')) require('cerberus/class/core.init.php');
new Init('test');