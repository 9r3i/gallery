<?php
chdir(dirname(dirname(__DIR__)));
require_once(__DIR__.'/galleryLight.php');
(new galleryLight)->requestHandler();
