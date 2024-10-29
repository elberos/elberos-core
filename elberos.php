<?php
/**
 * Plugin Name: Elberos Framework for WordPress
 * Description: Elberos plugin for WordPress
 * Version:     3.0.0
 * Author:      Elberos team <support@bayrell.org>
 * License:     GNU GENERAL PUBLIC LICENSE 3.0
 */

/* Check if Wordpress */
if (!defined('ABSPATH')) exit;

if ( !class_exists( 'Elberos_Plugin' ) ) 
{

class Elberos_Plugin
{
	static $composer = null;
	static $main = null;
	
	
	/**
	 * Register Plugin
	 */
	public static function init()
	{
		/* Get composer instance */
		static::$composer = require_once __DIR__ . "/vendor/autoload.php";
		
		/* Register Elberos plugin */
		static::$composer->addPsr4("Elberos\\", __DIR__ . "/src");
		
		/* Register main class */
		static::$main = new \Elberos\Main();
		
		/* WordPress init */
		add_action('init', [static::class, 'wordpress_init']);
	}
	
	
	/**
	 * WordPress init
	 */
	public static function wordpress_init()
	{
		do_action('elberos_register_composer', static::$composer);
		
		/* Init main app */
		static::$main->init();
	}
}


function elberos()
{
	Elberos_Plugin::$main->index();
}


Elberos_Plugin::init();

}