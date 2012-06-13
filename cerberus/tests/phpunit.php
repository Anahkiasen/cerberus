<?php
ob_start();

if(!class_exists('Init')) require('class/core.init.php');
new Init('test', '../');