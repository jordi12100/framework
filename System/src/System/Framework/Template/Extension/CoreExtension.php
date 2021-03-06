<?php

namespace System\Framework\Template\Extension;

use System\Framework\HTTP\Response;
use System\Framework\Routing\Route;
use System\Framework\Form\FormHandler;
use System\Framework\Security;

use System\Framework\Config;
use KzykHys\Pygments\Pygments;
class CoreExtension extends \Twig_Extension {

	public function getFilters() {
		return array(new \Twig_SimpleFilter('markdown', function($content) {

			return \Michelf\Markdown::defaultTransform($content);
		}, array('is_safe' => array('html'))), );

	}

	public function getFunctions() {
		return array(new \Twig_SimpleFunction('url', function() {
			return call_user_func_array(array(new Response, 'url'), func_get_args());

		}), new \Twig_SimpleFunction('csrf_token', function() {
			$security = new Security;

			return $security -> generateSignature();

		}), new \Twig_SimpleFunction('base_path', function() {
			$response = new Response;
			return $response -> getBasePath();
			
		}), new \Twig_SimpleFunction('formValue', function($name) {
			$form = new FormHandler;

			return $form -> getValue($name);
		}), new \Twig_SimpleFunction('pygstyles', function($style) {
			$pygments = new Pygments();
			return $pygments->getCss($style);
			
		}), new \Twig_SimpleFunction('formSelectValue', function($name, $value) {
			$form = new FormHandler;

			if (is_array($form -> getValue($name))) {
				return in_array($value, $form -> getValue($name)) ? 'selected="selected"' : '';
			} else {
				return ($form -> getValue($name) == $value) ? 'selected="selected"' : '';
			}
		}, array('is_safe' => array('html'))), new \Twig_SimpleFunction('formRadioValue', function($name, $value) {
			$form = new FormHandler;

			if (is_array($form -> getValue($name))) {
				return in_array($value, $form -> getValue($name)) ? 'checked' : '';
			} else {
				return ($form -> getValue($name) == $value) ? 'checked' : '';
			}

		}, array('is_safe' => array('html'))), new \Twig_SimpleFunction('render', function($controllerName, $args = array()) {
			$route = new Route;
			$data = $route -> parseRouteData($controllerName);

			$controllerClass = '\\Application\\Controller\\' . $data[0] . '\\' . $data[1] . 'Controller';

			if (class_exists($controllerClass)) {
				$controller = new $controllerClass;
				return call_user_func_array(array($controller, $data[2]), $args);
			} else {
				throw new \ErrorException('Could not find controller or method not found: ' . $controllerClass);
			}
		}, array('is_safe' => array('html'))));
	}

	public function getName() {
		return 'project';
	}

}
