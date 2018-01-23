<?php

namespace app\zx_apply\controller;

use app\common\controller\Common as CCommon;

class Common extends CCommon {
	
	/**
	 * 判断是否已登录--(初始化函数 _initialize 优先于 $beforeActionList 配置)
	 */
	public function _initialize() {
		if (request ()->module () == 'zx_apply') {
			$this->checkAuth ();
		}
		parent::_initialize ();
		$this->assign("title","专线开通全流程辅助--Xianda");
	}
	private function checkAuth() {
		$u = input ( 'get.u' );
		$users = [ 
				'y' => '于显达',
				'sjbz' => '数据班组' 
		];
		foreach ( $users as $k => $v ) {
			if ($k == $u) {
				session ( "user", [ 
						'name' => $v 
				] );
			}
		}
	}
}
