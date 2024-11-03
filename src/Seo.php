<?php

/*!
 *  Elberos Framework
 *  (c) Copyright 2019-2024 "Ildar Bikmamatov" <support@elberos.org>
 */

namespace Elberos;


class Seo
{
	var $canonical_url = "";
	var $title = "";
	var $title_prefix = "";
	var $full_title = false;
	var $description = "";
	var $image = "";
	var $site_name = "";
	var $twitter_card = "summary_large_image";
	var $type = "website";
	var $robots = [
		"follow" => true,
		"index" => true,
	];
	
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		if (class_exists(\RankMath::class))
		{
			$rank_math = \RankMath::get();
			$titles = $rank_math->settings->get("titles");
			
			/* Set title prefix */
			$this->title_prefix = isset($titles["title_separator"]) ?
				$titles["title_separator"] : "";
		}
		
		$this->site_name = get_bloginfo('name');
	}
	
	
	/**
	 * Output meta
	 */
	function print_meta($type, $key, $value)
	{
		echo "<meta " . $type . "='" . $key . "' content='" . esc_attr( $value) . "'/>\n";
	}
	
	
	/**
	 * Returns title
	 */
	function getTitle()
	{
		if ($this->full_title) return $this->title;
		return $this->title . " " . $this->title_prefix . " " . $this->site_name;
	}
	
	
	/**
	 * Setup title
	 */
	function setTitle($title, $full_title = false)
	{
		$this->title = $title;
		$this->full_title = $full_title;
	}
	
	
	/**
	 * Setup description
	 */
	function setDescription($description)
	{
		$this->description = $description;
	}
	
	
	/**
	 * Setup image
	 */
	function setImage($image)
	{
		$this->image = $image;
	}
	
	
	/**
	 * Render seo tags
	 */
	function render()
	{
		if ($this->description != "")
		{
			$this->print_meta("name", "description", $this->description);
		}
		
		/* Open Graph */
		$this->print_meta("property", "og:locale", get_locale());
		$this->print_meta("property", "og:type", $this->type);
		$this->print_meta("property", "og:url", get_bloginfo('url'));
		$this->print_meta("property", "og:site_name", $this->site_name);
		if ($this->title != "")
		{
			$this->print_meta("property", "og:title", $this->getTitle());
		}
		if ($this->description != "")
		{
			$this->print_meta("property", "og:description", $this->description);
		}
		if ($this->image != "")
		{
			$this->print_meta("property", "og:image", $this->image);
		}
		
		/* Twitter */
		$this->print_meta("property", "twitter:card", $this->twitter_card);
		if ($this->title != "")
		{
			$this->print_meta("property", "twitter:title", $this->getTitle());
		}
		if ($this->description != "")
		{
			$this->print_meta("property", "twitter:description", $this->description);
		}
		if ($this->image != "")
		{
			$this->print_meta("property", "twitter:image", $this->image);
		}
	}
}