<?php

namespace app\zx_apply\controller;

use think\Controller;
use think\Request;
use think\Db;

class Index extends Common {
	
	/*
	 * protected $beforeActionList = [
	 * 'checkAuth'
	 * ];
	 */
	
	/**
	 * FOR TEST
	 *
	 * @return void|string
	 */
	public function tt() {
		$data = Db::table ( "temp_tb1" )->select ();
		// $str = json_encode($data);
		return dump ( $data );
	}
	
	/**
	 * 首页
	 *
	 * @return mixed|string|void
	 */
	public function index() {
		// 暂未配置登录页面
		return $this->fetch ();
		return $this->redirect ( 'apply' );
	}
	public function _ht_apply(){
		if(input('?get.zxInfoTitle')&&input('?get.t')){
			return $this->fetch();
		}
	}
	/**
	 * ip、vlan申请
	 *
	 * @return mixed|string
	 */
	public function apply() {
		// $zxInfoTitle = Db::table("phpweb_sysinfo")->field("value,option")->where("label","zxInfoTitle")->select();
		$zxInfoTitle = [ 
				"label" => "zx_apply-223-rb",
				"order" => "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18" 
		];
		$this->assign ( 'zxInfoTitle', json_encode ( $zxInfoTitle, 256 ) );
		return $this->fetch ();
	}
	
	/**
	 * 根据label、order 获取表格的 header
	 *
	 * @param String $label        	
	 * @param String $order        	
	 * @param boolean $v        	
	 * @return string
	 */
	public function getHeader(String $label = "label", String $order = "order", $v = false) {
		if ($label === "label" || $order === "order") {
			return "{msg:\"你要搞什么？\"}";
		}
		$_headerData = Db::table ( "phpweb_sysinfo" )->field ( "value,option" )->where ( [ 
				"label" => $label 
		] )->order ( "id" )->select ();
		
		$orderArr = explode ( ",", $order );
		$headerArr = [ ];
		$sub = $v ? "value" : "option";
		foreach ( $orderArr as $o ) {
			$headerArr [] = $_headerData [$o] [$sub];
		}
		return implode ( ",", $headerArr );
	}
	
	/**
	 * 信息查询
	 */
	public function query() {
		return $this->fetch ();
	}
	
	/**
	 * 专线制作数据合成 script.html
	 */
	// public function script()
	
	/**
	 * 空方法 _empty 直接 fetch 对应的view
	 *
	 * @return mixed|string|void
	 */
	public function _empty() {
		$request = Request::instance ();
		$dir = APP_PATH . $request->module () . DS . "view" . DS . $request->controller () . DS . $request->action () . "." . config ( 'template.view_suffix' );
		if (file_exists ( $dir ))
			return $this->fetch ( $request->action () );
		else {
			return $this->error ( "请求未找到，将返回上一页...<b>(zx_apply/controller/index.php->_empty())</b>" );
		}
	}
}
