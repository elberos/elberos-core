<?php

/*!
 *  Elberos Framework
 *  (c) Copyright 2019-2024 "Ildar Bikmamatov" <support@elberos.org>
 */

namespace Elberos;


class Main
{
	var $twig = null;
	var $controllers = [];
	var $routes = [];
	var $widgets = [];
	var $controller = null;
	var $route = null;
	
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		/* Apply filter */
		add_filter('do_parse_request', array($this, 'do_parse_request'), 9999);
	}
	
		
	/**
	 * Init main function
	 */
	function init()
	{
		if ($this->twig != null) return;
		
		/* Create twig */
		$this->registerTwig();
		
		/* Register controllers */
		$this->registerControllers();
		
		/* Register routes */
		$this->registerRoutes();
		
		/* Register widgets */
		$this->registerWidgets();
		
		/* Setup route */
		$this->setupRoute();
	}
	
	
	/**
	 * Add controller
	 */
	function addController($controller)
	{
		$this->controllers[] = $controller;
	}
	
	
	/**
	 * Add route
	 */
	function addRoute($route)
	{
		$this->routes[] = $route;
	}
	
	
	/**
	 * Add widget
	 */
	function addWidget($widget_name, $class_name)
	{
		$this->widgets[$widget_name] = $class_name;
	}
	
	
	/**
	 * Register controllers
	 */
	function registerControllers()
	{
		do_action('elberos_register_controllers', $this);
	}
	
	
	/**
	 * Register routes
	 */
	function registerRoutes()
	{
		foreach ($this->controllers as $controller)
		{
			$controller->register();
		}
	}
	
	
	/**
	 * Register widgets
	 */
	function registerWidgets()
	{
		do_action('elberos_register_widgets', $this);
	}
	
	
	/**
	 * Register twig
	 */
	function registerTwig()
	{
		$this->twig = new \Elberos\Twig();
		$this->twig->create();
	}
	
	
	/**
	 * Setup route
	 */
	function setupRoute()
	{
		/*var_dump($this->routes);*/
	}
	
	
	/**
	 * Get default templates
	 */
	function getDefaultTemplates()
	{
		$templates = [];
		
		/* Add default pages */
		if (is_404()) $templates[] = "pages/404.twig";
		if (is_archive()) $templates[] = "pages/archive.twig";
		if (is_single()) $templates[] = "pages/single.twig";
		if (is_page() || is_single()) $templates[] = "pages/page.twig";
		if (is_home() && $this->route == null) $templates[] = "pages/index.twig";
		$templates[] = "pages/index.twig";
		
		/* Run filter */
		$templates = apply_filters("elberos_default_template", $templates);
		
		return $templates;
	}
	
	
	/**
	 * Run index
	 */
	function index()
	{
		/* Run index */
		do_action('elberos_index', [$this]);
		
		$templates = $this->getDefaultTemplates();
		echo $this->twig->render($templates);
	}
	
	
	/**
	 * WordPress do_parse_request filter
	 */
	function do_parse_request()
	{
		if (is_admin()) return true;
		if ($this->route == null) return true;
		return false;
	}
}