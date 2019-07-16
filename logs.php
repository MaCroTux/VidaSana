<?php

echo "<pre>";
$contend = file('images/debug.log');
$contend = array_reverse($contend);
echo implode('',$contend);
echo "</pre>";