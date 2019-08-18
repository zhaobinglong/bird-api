<?php

$command = "cd /home/wwwroot/default/bird-api && git pull 2>&1";

$res = shell_exec($command);

var_dump($res);
