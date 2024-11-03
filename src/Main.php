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
	var $response = null;
	
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		/* Apply filter */
		add_action('wp_head', array($this, 'wp_head'), 0);
		add_filter('wp_robots', array($this, 'wp_robots'));
		add_filter('wp_title', array($this, 'wp_title'), 0, 2);
		add_filter('do_parse_request', array($this, 'do_parse_request'), 9999);
		add_filter('rank_math/frontend/disable_integration',
			array($this, 'rank_math_disable_integration'));
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
	 * Returns langs settings
	 */
	function getLangSettings()
	{
		$langs = \Elberos\Helper::wp_langs();
		$langs_code = array_map(function($item){ return $item["slug"]; }, $langs);
		$lang_uri = "";
		if (count($langs_code) > 0)
		{
			$lang_uri = "/(" . implode("|", $langs_code) . ")";
		}
		return
		[
			"langs" => $langs,
			"langs_code" => $langs_code,
			"lang_uri" => $lang_uri,
			"default_lang" => \Elberos\Helper::wp_get_default_lang(),
			"hide_default_lang" => \Elberos\Helper::wp_hide_default_lang(),
		];
	}
	
	
	/**
	 * Returns match url
	 */
	function getRouteMatch($route, $lang_uri)
	{
		$match = isset($route["match"]) ? $route["match"] : null;
		if ($match) return $match;
		
		$match = isset($route["uri"]) ? $route["uri"] : "/";
		$items = ["{locale_uri}", "{locale_url}", "{lang_uri}", "{lang_url}"];
		foreach ($items as $item)
		{
			$match = str_replace($item, $lang_uri, $match);
		}
		$match = str_replace("/", "\\/", $match);
		$match = "/^" . $match . "$/i";
		
		return $match;
	}
	
	
	/**
	 * Setup route
	 */
	function setupRoute()
	{
		/* Get current url */
		$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "/";
		$request_uri_arr = parse_url($request_uri);
		$request = [
			"method" => isset($_SERVER['REQUEST_METHOD']) ?
				strtoupper($_SERVER['REQUEST_METHOD']) : "GET",
			"uri" => $request_uri,
			"path" => isset($request_uri_arr['path']) ? $request_uri_arr['path'] : "/",
			"query" => isset($arequest_uri_arrrr['query']) ? $request_uri_arr['query'] : "",
		];
		
		/* Get lang settings */
		$lang_settings = $this->getLangSettings();
		
		/* Match routes */
		foreach ($this->routes as $pos => $route)
		{
			$match = $this->getRouteMatch($route, $lang_settings["lang_uri"]);
			$flag = preg_match_all($match, $request["path"], $matches);
			
			/* Match default lang */
			if (!$flag &&
				$lang_settings["lang_uri"] != "" &&
				$lang_settings["hide_default_lang"]
			)
			{
				$match = $this->getRouteMatch($route, "");
				$flag = preg_match_all($match, $request["path"], $matches);
			}
			
			/* Setup route */
			if ($flag)
			{
				$this->route = $route;
				$this->route['matches'] = $matches;
				break;
			}
		}
	}
	
	
	/**
	 * Run index
	 */
	function renderRoute()
	{
		if (!$this->route) return;
		call_user_func_array($this->route["method"], []);
	}
	
	
	/**
	 * Render response
	 */
	function renderResponse()
	{
		echo $this->response;
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
		
		/* Render route */
		if ($this->route)
		{
			$this->renderRoute();
		}
		
		/* Render default template */
		else
		{
			$templates = $this->getDefaultTemplates();
			$this->response = $this->twig->render($templates);
		}
		
		/* Render response */
		$this->renderResponse();
	}
	
	
	/**
	 * Rank math disable integration
	 */
	function rank_math_disable_integration()
	{
		if ($this->route == null) return false;
		return true;
	}
	
	
	/**
	 * WordPress head
	 */
	function wp_head()
	{
		if ($this->route == null) return;
		$this->twig->context->seo->render();
	}
	
	
	/**
	 * WordPress robots
	 */
	function wp_robots($robots)
	{
		if ($this->route == null) return $robots;
		return $this->twig->context->seo->robots;
	}
	
	
	/**
	 * WordPress title
	 */
	function wp_title($title, $sep)
	{
		if ($this->route == null) return $title;
		return $this->twig->context->seo->getTitle();
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