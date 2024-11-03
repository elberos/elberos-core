<?php

/*!
 *  Elberos Framework
 *  (c) Copyright 2019-2024 "Ildar Bikmamatov" <support@elberos.org>
 */

namespace Elberos\Widget;


class BaseWidget
{
	/**
	 * Create widget
	 */
	function __construct($params)
	{
	}
	
	
	/**
	 * Setup widget
	 */
	function setup()
	{
	}
	
	
	/**
	 * Get param value
	 */
	function get($key)
	{
		return $this->$key;
	}
	
	
	/**
	 * Set param value
	 */
	function set($key, $value)
	{
		$this->$key = $value;
	}
	
	
	/**
	 * Set new params
	 */
	function setParams($params)
	{
		if (!$params) return;
		foreach ($params as $key => $value)
		{
			$this->set($key, $value);
		}
	}
	
	
	/**
	 * Render widget
	 */
	function render($params = null)
	{
	}
}