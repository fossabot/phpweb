<?php

namespace app\zx_apply\model;

use think\Model;
use app\common\controller\Common;

class Infotables extends Model {
	public function setIp($value) {
		return ip2long ( $value );
	}
	public function getIp($value) {
		return long2ip ( $value );
	}
	public function setNeFactory($value) {
		return in_array ( $value, [ 
				"华为",
				"中兴" 
		] );
	}
	public function getNeFactory($value) {
		$zx_nefactory = [ 
				0 => "华为",
				1 => "中兴" 
		];
		return $zx_nefactory [$value ["neFactory"]];
	}
}