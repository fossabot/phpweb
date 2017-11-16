<?php

namespace app\zx_apply\controller;

use think\Controller;
use think\Request;
use think\Db;

class Index extends Common {
	/*protected $beforeActionList = [ 
			'checkAuth' 
	];*/
	public function tt(){
		$data = Db::table("temp_tb1")->select();
		//$str = json_encode($data);
		return dump($data);
	}
	public function index() {
		// 暂未配置登录页面
		return $this->fetch ();
		return $this->redirect ( 'apply' );
	}
	public function apply() {
		$zxInfoTitle = Db::table("phpweb_sysinfo")->field("value,option")->where("label","zxInfoTitle")->select();
		$this->assign('zxInfoTitle',json_encode($zxInfoTitle,256));
		return $this->fetch();
	}
	/**
	 * 专线制作数据合成 script.html
	 */
	// public function script()
	
	/**
	 * 空方法 直接 fetch 对应的view
	 *
	 * @return mixed|string|void
	 */
	public function _empty() {
		$request = Request::instance ();
		$dir = APP_PATH . $request->module () . DS . "view" . DS . $request->controller () . DS . $request->action () . "." . config ( 'template.view_suffix' );
		if (file_exists ( $dir ))
			return $this->fetch ( $request->action () );
		else {
			return $this->error ( "请求未找到，将返回上一页...(zx_apply/controller/index.php->_empty())" );
		}
	}
}
