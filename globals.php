<?php

ini_set('display_errors', 1);
$doc_root = $_SERVER["DOCUMENT_ROOT"];
$doc_root = rtrim($doc_root, '/');
define('BASE_PATH', $doc_root.DIRECTORY_SEPARATOR.'oop');

