<?php
$GLOBALS['cerberus']->injectModule('news');
$news = new getNews();
$news->adminNews();
?>