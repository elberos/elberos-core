<?php

/*!
 *  Elberos Framework
 *  (c) Copyright 2019-2024 "Ildar Bikmamatov" <support@elberos.org>
 */

namespace Elberos\Widget;


class Menu extends \Elberos\Widget\BaseWidget
{
	var $name = "";
	var $header = false;
	var $mobile = false;
	
	
	/**
	 * Create widget
	 */
	function __construct($params)
	{
		parent::__construct($params);
		
		if (isset($params["name"])) $this->name = $params["name"];
		if (isset($params["header"])) $this->header = $params["header"];
		if (isset($params["mobile"])) $this->mobile = $params["mobile"];
	}
	
	
	/**
	 * Build menu items
	 */
	function buildItems($menu_items, $parent_id = 0)
	{
		if (!$menu_items) return [];
		
		$items = [];
		foreach ($menu_items as $item)
		{
			if ($item->menu_item_parent != $parent_id) continue;
			$item->subitems = $this->buildItems($menu_items, $item->ID);
			$items[] = $item;
		}
		return $items;
	}
	
	
	/**
	 * Render widget
	 */
	function render()
	{
		/* Find menu is exists */
		$locations = get_nav_menu_locations();
		if (!isset($locations[$this->name])) return;
		
		/* Get menu items */
		$menu_id = $locations[$this->name];
		$menu_items = wp_get_nav_menu_items($menu_id);
		
		/* Build menu items */
		$items = $this->buildItems($menu_items);
		
		/* Render header menu */
		$main = \Elberos_Plugin::main();
		$content = $main->twig->renderTemplate("@core/widget/header_menu.twig", [
			"widget" => $this,
			"items" => $items,
		]);
		echo $content;
	}
}