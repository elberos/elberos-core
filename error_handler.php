<?php

/*!
 *  Elberos Framework
 *  (c) Copyright 2019-2024 "Ildar Bikmamatov" <support@elberos.org>
 */


defined( 'ABSPATH' ) || exit;


/* Выключаем html отладчик */
add_filter('qm/dispatchers', function($dispatchers, $qm){
	
	unset($dispatchers['html']);
	//var_dump($dispatchers);
	
	return $dispatchers;
}, 999999, 2);


/* Включаем стандартный обработчик ошибок */
if (!defined('WP_DISABLE_FATAL_ERROR_HANDLER'))
{
	define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
}


/**
 * Show stack trace
 */
function elberos_show_stack_trace($e)
{
	$trace = $e->getTrace();
	echo "<br/>\n";
	foreach ($trace as $index => $item)
	{
		if (isset($item['file']))
		{
			echo $index . ") " . htmlspecialchars($item['file']) .
				"(" . htmlspecialchars($item['line']) . ")";
			echo ": " . htmlspecialchars($item['function']);
		}
		else if (isset($item['class']))
		{
			echo $index . ") " . htmlspecialchars($item['class']);
			echo ": " . htmlspecialchars($item['function']);
		}
		else
		{
			echo "internal: " . htmlspecialchars($item['function']);
		}
		
		echo "<br/>\n";
	}
}


/**
 * Show error
 */
function elberos_show_error($e)
{
	if (!$e) return;
	
	status_header(500);
	http_response_code(500);
	
	echo "<b>Fatal Error</b><br/>";
	if ($e instanceof \Exception || $e instanceof \Error)
	{
		$e = $e->getPrevious() ? $e->getPrevious() : $e;
		
		echo nl2br(htmlspecialchars($e->getMessage())) . "<br/>\n";
		echo "in file " . htmlspecialchars($e->getFile()) . ":" .
			htmlspecialchars($e->getLine()) . "\n";
		
		/* Show stack trace */
		elberos_show_stack_trace($e);
	}
	else
	{
		echo nl2br(htmlspecialchars($e['message'])) . "<br/>\n";
		if (isset($e["file"]))
		{
			echo "in file " . htmlspecialchars($e["file"]) . ":" .
				(isset($e["line"]) ? htmlspecialchars($e["line"]) : 0) . "\n";
		}
	}
}


/* Обработчик ошибок */
set_exception_handler( function ($e){
	if (!$e) return;
	elberos_show_error($e);
	exit();
} );


/* Shutdown функция */
register_shutdown_function( function(){
	
	$e = error_get_last();
	
	if (!$e) return;
	if (!($e['type'] & (E_COMPILE_ERROR))) return;
	
	elberos_show_error($e);
} );
