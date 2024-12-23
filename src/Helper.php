<?php

/*!
 *  Elberos Framework
 *  (c) Copyright 2019-2024 "Ildar Bikmamatov" <support@elberos.org>
 */

namespace Elberos;


class Helper
{
	/**
	 * Check wp nonce
	 */
	function check_wp_nonce($nonce_action)
	{
		/* Check nonce */
		$nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : false;
		if ($nonce == false)
		{
			return false;
		}
		if (!wp_verify_nonce($nonce, $nonce_action))
		{
			return false;
		}
		return true;
	}
	
	
	/**
	 * Returns styles
	 */
	static function getStyles($class_name, $styles)
	{
		$f = function($item) use ($class_name)
		{
			if ($item[0] == "@") return $item;
			return $class_name . "--" . $item;
		};
		$styles = array_map($f, $styles);
		return implode(" ", $styles);
	}
	
	
	/**
	 * Returns assets url
	 */
	static function assets($src)
	{
		return get_template_directory_uri() . $src;
	}
	
	
	/**
	 * Returns widget instance
	 */
	static function widget_create($widget_name, $params)
	{
		$main = \Elberos_Plugin::main();
		
		/* Get wiget class name */
		if (!isset($main->widgets[$widget_name]))
		{
			throw new \Exception("Widget '" . $widget_name . "' not found");
		}
		$class_name = $main->widgets[$widget_name];
		
		/* Create widget */
		$widget = static::newInstance($class_name, [$params]);
		$widget->setup();
		
		return $widget;
	}
	
	
	/**
	 * Returns widget content
	 */
	static function widget_content($widget_name, $params)
	{
		/* Create widget */
		$widget = static::widget_create($widget_name, $params);
		
		/* Render widget */
		$content = $widget->render();
		return $content;
	}
	
	
	/**
	 * Render widget
	 */
	static function widget($widget_name, $params)
	{
		echo static::widget_content($widget_name, $params);
	}
	
	
	/**
	 * Dump value
	 */
	static function dump($value)
	{
		echo "<pre>";
		var_dump($value);
		echo "</pre>";
	}
	
	
	/**
	 * Returns langs
	 */
	static function wp_langs()
	{
		$res = [];
		if (defined("POLYLANG_VERSION") && function_exists("\\PLL"))
		{
			$links = \PLL()->links;
			if ($links)
			{
				$langs = $links->model->get_languages_list();
				foreach ($langs as $lang)
				{
					$res[] =
					[
						"name" => $lang->name,
						"locale" => $lang->locale,
						"code" => $lang->slug,
						"slug" => $lang->slug,
						"item" => $lang,
					];
				}
			}
		}
		$res = apply_filters("elberos_langs", $res);
		return $res;
	}
	
	
	/**
	 * Returns default lang
	 */
	static function wp_get_default_lang()
	{
		$default_lang = "ru";
		if (defined("POLYLANG_VERSION") && function_exists("\\PLL"))
		{
			$default_lang = \PLL()->options['default_lang'];
		}
		$default_lang = apply_filters("elberos_default_lang", $default_lang);
		return $default_lang;
	}
	
	
	/**
	 * Hide default lang
	 */
	static function wp_hide_default_lang()
	{
		$res = false;
		if (defined("POLYLANG_VERSION") && function_exists("\\PLL"))
		{
			$res = \PLL()->options['hide_default'];
		}
		$res = apply_filters("elberos_hide_default_lang", $res);
		return $res;
	}
	
	
	/**
	 * Returns global WP_Query
	 */
	static function wp_query()
	{
		global $wp_query;
		return $wp_query;
	}
	
	
	/**
	 * Returns new WP_Query instance
	 */
	static function create_wp_query($params)
	{
		return new \WP_Query($params);
	}
	
	
	/**
	 * Create new instance
	 */
	static function newInstance($class_name, $params = [])
	{
		$reflectionClass = new \ReflectionClass($class_name);
		$obj = $reflectionClass->newInstanceArgs($params);
		return $obj;
	}
	
	
	/**
	 * Returns posts images
	 */
	static function get_posts_images($posts, $db_prefix = null)
	{
		global $wpdb;
		
		$posts_id = array_map(
			function($item)
			{
				if ($item instanceof \WP_Post) return $item->ID;
				return $item["ID"];
			},
			$posts
		);
		if (count($posts_id) == 0) return [];
		
		/* Get thumbnails */
		if ($db_prefix == null) $db_prefix = $wpdb->prefix;
		$wp_posts = $db_prefix . "posts";
		$wp_postmeta = $db_prefix . "postmeta";
		$sql = $wpdb->prepare
		(
			"SELECT postmeta.meta_value, post.ID as id, post.post_modified_gmt " .
			"FROM " . $wp_postmeta . " as postmeta " .
			"INNER JOIN " . $wp_posts . " as post on (post.ID = postmeta.post_id) " .
			"WHERE postmeta.meta_key='_thumbnail_id' AND " .
			"postmeta.post_id in (" . implode(",", array_fill(0, count($posts_id), "%d")) . ")",
			$posts_id
		);
		$posts_meta = $wpdb->get_results($sql, ARRAY_A);
		
		/* Get images */
		$images_id = array_map(function($item){ return $item["meta_value"]; }, $posts_meta);
		$post_images = static::get_images_url($images_id, $db_prefix);
		
		/* Build index */
		$posts_meta_index = [];
		foreach ($posts_meta as $item)
		{
			$item_id = $item["id"];
			if (!isset($posts_meta_index[$item_id])) $posts_meta_index[$item_id] = [];
			$posts_meta_index[$item_id][] = $item["meta_value"];
		}
		
		/* Modify posts */
		foreach ($posts as $pos => $post)
		{
			$main_photo = [];
			
			/* Add main photo */
			$post_id = ($post instanceof \WP_Post) ? $post->ID : $post["ID"];
			if (isset($posts_meta_index[$post_id]))
			{
				foreach ($posts_meta_index[$post_id] as $photo_id)
				{
					if (isset($post_images[$photo_id]))
					{
						$main_photo[] = $post_images[$photo_id];
					}
				}
			}
			
			if ($post instanceof \WP_Post)
			{
				$posts[$pos]->main_photo = $main_photo;
			}
			else
			{
				$posts[$pos]["main_photo"] = $main_photo;
			}
		}
		
		return $posts;
	}
	
	
	/**
	 * Returns images urls
	 */
	static function get_images_url($images_id, $db_prefix = null)
	{
		global $wpdb;
		
		if ($db_prefix == null) $db_prefix = $wpdb->prefix;
		if (count($images_id) == 0) return [];
		
		/* Get uploads dir */
		$uploads = wp_get_upload_dir();
		$baseurl = $uploads["baseurl"];
		$posts_meta = [];
		
		/* Get meta */
		$wp_posts = $db_prefix . "posts";
		$wp_postmeta = $db_prefix . "postmeta";
		$sql = $wpdb->prepare
		(
			"SELECT postmeta.meta_value, post.ID as id, post.post_modified_gmt " .
			"FROM " . $wp_postmeta . " as postmeta " .
			"INNER JOIN " . $wp_posts . " as post on (post.ID = postmeta.post_id) " .
			"WHERE postmeta.meta_key='_wp_attachment_metadata' AND " .
			"postmeta.post_id in (" . implode(",", array_fill(0, count($images_id), "%d")) . ")",
			$images_id
		);
		$posts_meta = $wpdb->get_results($sql, ARRAY_A);
		
		/* Get result */
		$result = [];
		foreach ($posts_meta as $item)
		{
			$meta_value = @unserialize($item["meta_value"]);
			if ($meta_value)
			{
				$file = $meta_value["file"];
				$file_dir = dirname($file);
				$meta_value["url"] =
					$baseurl . "/" . $meta_value["file"] .
					"?_=" . strtotime($item["post_modified_gmt"])
				;
				foreach ($meta_value["sizes"] as $key => $size)
				{
					$meta_value["sizes"][$key]["url"] =
						$baseurl . "/" . $file_dir . "/" .
						$size["file"] . "?_=" . strtotime($item["post_modified_gmt"])
					;
				}
			}
			
			$result[$item["id"]] = $meta_value;
		}
		
		return $result;
	}
}