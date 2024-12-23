<?php

/*!
 *  Elberos Framework
 *  (c) Copyright 2019-2024 "Ildar Bikmamatov" <support@elberos.org>
 */

namespace Elberos;


class Twig
{
	var $twig = null;
	var $context = null;
	
	
	/**
	 * Create twig
	 */
	function create($options = null)
	{
		/* Twig options */
		if ($options == null)
		{
			$options = array
			(
				'autoescape'=>true,
				'charset'=>'utf-8',
				'optimizations'=>-1,
			);
		}
		
		/* Twig cache */
		$cache = true;
		if (defined("TWIG_CACHE"))
		{
			$cache = TWIG_CACHE;
		}
		
		/* Enable cache */
		if ($cache)
		{
			$options['cache'] = ABSPATH . 'wp-content/cache/twig';
			$options['auto_reload'] = true;
		}
		
		/* Create twig loader */
		$loader = new \Twig\Loader\FilesystemLoader();
		if (is_dir(get_template_directory() . '/templates'))
		{
			$loader->addPath(get_template_directory() . '/templates');
		}
		do_action('elberos_twig_loader', [$loader]);
		
		/* Create twig instance */
		$twig = new \Twig\Environment($loader, $options);
		
		/* Set strategy */
		$twig->getExtension(\Twig\Extension\EscaperExtension::class)->setDefaultStrategy('html');
		
		/* Add function */
		$twig->addFunction(new \Twig\TwigFunction('function', function($name)
		{
			$args = func_get_args();
			array_shift($args);
			return call_user_func_array($name, $args);
		}));
		
		/* Undefined functions */
		$function = function ($name) {
			if (!function_exists($name))
			{
				return false;
			}
			return new \Twig\TwigFunction($name, $name);
		};
		$twig->registerUndefinedFunctionCallback($function);
		$twig->registerUndefinedFilterCallback($function);
		
		/* Add Helper functions */
		$items = [
			"assets",
			"create_wp_query",
			"widget",
			"widget_content",
			"widget_create",
			"wp_query",
		];
		foreach ($items as $item)
		{
			$twig->addFunction(new \Twig\TwigFunction($item, [\Elberos\Helper::class, $item]));
		}
		
		/* Dump function */
		$twig->addFunction(new \Twig\TwigFunction('dump', [\Elberos\Helper::class, 'dump']));
		
		/* Twig variable */
		$this->twig = $twig;
		$this->context = new \Elberos\Context();
		
		/* Do action */
		do_action('elberos_twig', [$this]);
	}
	
	
	/**
	 * Render template
	 */
	function renderTemplate($template, $context)
	{
		return $this->twig->render($template, $context);
	}
	
	
	/**
	 * Render template
	 */
	function render($template)
	{
		/* set this to context */
		$context = $this->context->getData();
		$context["this"] = $context;
		
		if (gettype($template) == 'array')
		{
			foreach ($template as $t)
			{
				try
				{
					$res = $this->twig->render($t, $context);
					return $res;
				}
				catch (\Twig\Error\LoaderError $err)
				{
				}
			}
		}
		else
		{
			return $this->twig->render($template, $context);
		}
		return "";
	}
}