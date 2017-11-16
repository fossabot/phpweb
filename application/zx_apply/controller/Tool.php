<?php

namespace app\zx_apply\controller;

use think\Controller;
use app\common\controller\Common;

class Tool extends Common {
	public function index() {
		// 暂未配置登录页面
		return $this->redirect ( "main" );
	}
}