<?php
//Autoloader for User classes
require __DIR__ . '/../vendor/autoload.php';
require_once 'src/RegexModuleJson.php';
require_once 'src/ModuleJsonAuthorize.php';
spl_autoload_register(function($class) {
	if ($class === 'Solleer\Router\\RegexModuleJson') {
		require_once 'src/RegexModuleJson.php';
	}
});
