<?php

function loader($class)
{
    $file = './lib/'.$class . '.php';
    if (file_exists($file)) {
        require $file;
    } else {
    	echo "Class not found!";
		die();
    }
}

spl_autoload_register('loader');