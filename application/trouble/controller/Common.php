<?php

namespace app\trouble\controller;
use app\common\controller\Common as CCommon;
use think\Controller;
use think\Request;
use think\Config;

class Common extends CCommon{
	
	/**
	 * 判断是否已登录--(初始化函数 _initialize 优先于 $beforeActionList 配置)
	 */
	public function _initialize() {
		$request = Request::instance ();
		if ($request->controller () == 'common') {
			return $this->error ( "非法访问！你很6啊。然而我会带你回去。" );
		}
		// url : index.php/【MODULE】/【CONTROLLER】/【ACTION】.html
		$permitActions = [ 
				"index",
				"main",
				"getvcode"
		];
		// 若 url 不允许 未登录访问，则跳转
		if ( (! in_array ( $request->action (), $permitActions )) && (! input ( 'session.user/a' ))) {
			// $this->assign ( "version", $request->controller () );
			// $this->assign ( "version", "登陆超时" );
			return $this->error ( '您未登录或登录超时，请先登录！', 'index/index' );
		} else {
			$this->assign ( "version", config ( "version" ) );
		}
	}
	
}
