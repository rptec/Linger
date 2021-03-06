<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-15
 * Time: 下午6:17
 * Filename: Router.php
 */

namespace ULib;


class Router{
	/**
	 * @var \CLib\Router
	 */
	private $c_router;
	private $router_list;

	function __construct(){
		$this->c_router = c_lib()->load('router')->add('router', new \CLib\Router());
		//对路由信息反序列化
		$router = @unserialize(cfg()->get('option', 'router_list'));
		foreach(array_keys($router) as $c){
			if(empty($router[$c])){
				unset($router[$c]);
			}
		}
		$this->router_list = $this->defaultRouter();
		if(is_array($router)){
			$this->router_list = array_merge($this->router_list, $router);
		}
	}

	private function defaultRouter(){
		return [
			'picture' => 'picture-%number%.html',
			'picture_pager' => 'picture-%number%.html/comment-page-%number%',
			'gallery' => 'gallery-%number%.html',
			'gallery_pager' => 'gallery-%number%.html/comment-page-%number%',
			'user' => 'user/%user_name%',
			'post' => 'post/%post_name%.html',
			'post_pager' => 'post/%post_name%.html/comment-page-%number%',
			'post_list' => 'post_list',
			'post_list_pager' => 'post_list/page-%number%',
			'gallery_list' => 'gallery_list',
			'gallery_list_pager' => 'gallery_list/page-%number%',
			'user_gallery_list' => 'user/%user_name%/gallery',
			'user_gallery_list_pager' => 'user/%user_name%/gallery/page-%number%',
			'time_line' => 'TimeLine',
		];
	}

	public function createRouter(){
		$this->c_router->add_preg(hook()->apply("Router_createRouter",$this->createPregList()));
		//$this->c_router->add_preg('/^picture-([1-9]{1}[0-9]*)\.html$/', 'Show/picture/[1]');
		//$this->c_router->add_preg('/^picture-([1-9]{1}[0-9]*)-p([1-9]{1}[0-9]*)\.html$/', 'Show/picture/[1]/[2]');
	}

	public function get($name){
		if(isset($this->router_list[$name])){
			return $this->router_list[$name];
		} else{
			return '';
		}
	}

	public function getLink($name){
		if(isset($this->router_list[$name])){
			$param = func_get_args();
			array_shift($param);
			$ui = $this->router_list[$name];
			if(@preg_match_all('/(%[\s\S]+?%)/', $ui, $matches) > 0 && isset($matches[1]) && is_array($matches[1])){
				for($i = 0, $l = count($param), $l2 = count($matches[1]); $i < $l && $i < $l2; ++$i){
					$search = @preg_quote($matches[1][$i]);
					$ui = @preg_replace("/$search/", $param[$i], $ui, 1);
				}
			}
			return $ui;
		} else{
			return '';
		}
	}

	private function  createPregList(){
		$rt = [];
		$search = [
			'.',
			'/',
			'%number%',
			'%user_name%',
			'%post_name%',
		];
		$replace = [
			'\.',
			'\\/',
			'([1-9]{1}[0-9]*)',
			'([_a-z]{1}[a-z0-9_.]{5,19})',
			'([a-zA-Z0-9]+[a-zA-Z0-9_-]*)',
		];
		$control_list = [
			'picture' => 'Show/picture/[1]',
			'picture_pager' => 'Show/picture/[1]/[2]',
			'gallery' => 'Show/gallery/[1]',
			'gallery_pager' => 'Show/gallery/[1]/[2]',
			'user' => 'Show/user/[1]',
			'post' => 'Show/post/[1]',
			'post_pager' => 'Show/post/[1]/[2]',
			'post_list' => 'Show/post_list',
			'post_list_pager' => 'Show/post_list/[1]',
			'gallery_list' => 'Show/gallery_list',
			'gallery_list_pager' => 'Show/gallery_list/[1]',
			'user_gallery_list' => 'Show/user_gallery_list/[1]',
			'user_gallery_list_pager' => 'Show/user_gallery_list/[1]/[2]',
			'time_line' => 'Show/time_line',
		];
		foreach($this->router_list as $name => $v){
			$p = "/^" . str_replace($search, $replace, $v) . "$/";
			if(isset($control_list[$name]) && !isset($rt[$p])){
				$rt[$p] = $control_list[$name];
			}
		}
		return $rt;
	}

	public function process($u){
		return $this->c_router->result($u);
	}
} 