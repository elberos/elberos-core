<?php

/*!
 *  Elberos Framework
 *  (c) Copyright 2019-2024 "Ildar Bikmamatov" <support@elberos.org>
 */

namespace Elberos;


class Context implements \ArrayAccess
{
	public $__data = [
		"title" => "",
		"description" => "",
	];
	
	
	/**
	 * Returns all data
	 */
	function getData()
	{
		return $this->__data;
	}
	
	
	/**
	 * Get and set methods
	 */
	public function get($key, $value = null)
	{
		return isset($this->__data[$key]) ? $this->__data[$key] : $value;
	}
	public function set($key, $value)
	{
		$this->__data[$key] = $value;
	}
	public function exists($key)
	{
		return $this->__data && isset($this->__data[$key]);
	}
	public function unset($key)
	{
		if ($this->__data && isset($this->__data[$key]))
		{
			unset($this->__data[$key]);
		}
	}
	
	
	/**
	 * Array methods
	 */
	public function offsetExists($key)
	{
		return $this->exists($key);
	}
	public function offsetUnset($offset)
	{
		$this->unset($key);
	}
	public function offsetGet($key)
	{
		return $this->get($key);
	}
	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
	}
	
	
	/**
	 * Magic methods
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}
	public function __get($key)
	{
		return $this->get($key);
	}
	public function __isset($key)
	{
		return $this->exists($key);
	}
	public function __unset($key)
	{
		$this->unset($key);
	}
}