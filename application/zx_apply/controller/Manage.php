<?php

namespace app\zx_apply\controller;

use think\Controller;

class Manage extends Index {
	public function index() {
		return $this->redirect( "todo" );
	}
	/**
	 * ip、vlan申请
	 *
	 * @return mixed|string
	 */
	public function import() {
		return $this->apply ();
	}
	/**
	 * 专线制作数据合成 script.html
	 */
	// public function script()
	public function settings() {
		if (! strpos ( request ()->header ( "referer" ), request ()->action () )) {
			session ( "settings_back_url", request ()->header ( "referer" ) );
		}
		return $this->fetch ();
	}
}