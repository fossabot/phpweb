<?php

namespace app\checkME60\controller;

use app\common\controller\Common;

class Index extends Common {
	public function index() {
		return $this->redirect ( "main" );
	}

	
	
}
