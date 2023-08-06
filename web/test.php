<?php

echo 'aaa';

var_dump(ob_get_level());
ob_start();

echo 'bbb';
var_dump(ob_get_level());
ob_end_flush();
var_dump(ob_get_level());
echo 'ccc';