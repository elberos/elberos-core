<?php

/*!
 *  Elberos Framework
 *  (c) Copyright 2019-2024 "Ildar Bikmamatov" <support@elberos.org>
 */

namespace Elberos\Widget;


class Logo extends \Elberos\Widget\BaseWidget
{
	var $href = "";
	var $image = "";
	var $title = "";
	var $title_raw = false;
	
	
	/**
	 * Create widget
	 */
	function __construct($params)
	{
		parent::__construct($params);
		
		if (isset($params["href"])) $this->href = $params["href"];
		if (isset($params["image"])) $this->image = $params["image"];
		if (isset($params["title"])) $this->title = $params["title"];
		if (isset($params["title_raw"])) $this->title_raw = $params["title_raw"];
	}
	
    
	/**
	 * Setup widget
	 */
	function setup()
	{
	}
	
	
	/**
	 * Render widget
	 */
	function render()
	{
		/* Render menu */
		$main = \Elberos_Plugin::main();
		$content = $main->twig->renderTemplate("@core/widget/logo.twig", [
			"widget" => $this,
		]);
		return $content;
	}
}