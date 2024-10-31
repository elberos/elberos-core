<?php
/**
 * Plugin Name: Elberos Framework
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
	protected static $composer = null;
	protected static $main = null;
	
	
	/**
	 * Get composer
	 */
	public static function composer()
	{
		return static::$composer;
	}
	
	
	/**
	 * Get main
	 */
	public static function main()
	{
		return static::$main;
	}
	
	
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
		
		/* Disable updates */
		add_filter('site_transient_update_plugins', [static::class, 'disable_updates']);
		
		/* Register twig folders */
		add_action('elberos_twig_loader', [static::class, 'twig_loader']);
		
		/* Register widgets */
		add_action('elberos_register_widgets', [static::class, 'register_widgets']);
		
		/* WordPress init */
		add_action('init', [static::class, 'wordpress_init']);
	}
	
	
	/**
	 * Disable auto updates
	 */
	public static function disable_updates($value)
	{
		$name = plugin_basename(__FILE__);
		if (isset($value->response[$name]))
		{
			unset($value->response[$name]);
		}
		return $value;
	}
	
	
	/**
	 * Register twig folders
	 */
	public static function twig_loader($loader)
	{
		$loader->addPath(__DIR__ . "/templates", "core");
	}
	
	
	/**
	 * Register widgets
	 */
	public static function register_widgets($main)
	{
		$main->addWidget("logo", \Elberos\Widget\Logo::class);
		$main->addWidget("menu", \Elberos\Widget\Menu::class);
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


/* Enable error handler if needs */
if (defined("ELBEROS_ERROR_HANDLER"))
{
	include __DIR__ . "/error_handler.php";
}


/**
 * Render function
 */
function elberos()
{
	Elberos_Plugin::main()->index();
}


/* Init plugin */
Elberos_Plugin::init();

}