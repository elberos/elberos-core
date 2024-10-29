<?php

/*!
 *  Elberos Framework
 *  (c) Copyright 2019-2024 "Ildar Bikmamatov" <support@elberos.org>
 */

namespace Elberos;


class Controller
{
	public $main;
	
	
	/**
	 * Constructor
	 */
	function __construct($main)
	{
		$this->main = $main;
	}
	
	
	/**
	 * Add route
	 */
	function addRoute($route)
	{
		$this->main->addRoute($route);
	}
	
	
	/**
	 * Register controller
	 */
	function register()
	{
	}
	
	
	/**
	 * Setup controller
	 */
	function setup()
	{
	}
	
	
	/**
	 * Returns context
	 */
	function context()
	{
		return $this->main->twig->context;
	}
	
	
	/**
	 * Render
	 */
	function render($template)
	{
		$content = $this->main->twig->render($template);
		echo $content;
	}
}
