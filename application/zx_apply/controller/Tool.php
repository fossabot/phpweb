<?php

namespace app\zx_apply\controller;

use think\Controller;
use app\common\controller\Common;

class Tool extends Common {
	public function _initialize() {
		if (input ( "get.uu" )=="y") {
			session ( "user", [ 
					"name" => "{Test}" 
			] );
		}
		parent::_initialize ();
	}
	public function index() {
		// 暂未配置登录页面
		return $this->redirect ( "main" );
	}
}