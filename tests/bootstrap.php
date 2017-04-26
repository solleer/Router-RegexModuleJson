<?php
//Autoloader for User classes
require __DIR__ . '/../vendor/autoload.php';
spl_autoload_register(function($class) {
	if ($class === 'Solleer\Router\\RegexModuleJson') {
		require_once 'src/RegexModuleJson.php';
	}
});
