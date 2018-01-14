<?php

namespace app\zx_apply\model;

use think\Model;

class Infitables extends Model {
	public function setIp($value){
		return ip2long($value);
	}
	
}