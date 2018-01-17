<?php

namespace app\zx_apply\model;

use think\Model;

class Infotables extends Model {
	protected $autoWriteTimestamp = true;
	protected $type = [ 
			"aDate" => "date" 
	];
	public function setIpAttr($value) {
		return ip2long ( $value );
	}
	public function getIpAttr($value) {
		return long2ip ( $value );
	}
	public function setNeFactoryAttr($value) {
		return array_search( $value, [ 
				"华为",
				"中兴" 
		] );
	}
	public function getNeFactoryAttr($value) {
		$zx_nefactory = [ 
				0 => "华为",
				1 => "中兴" 
		];
		return $zx_nefactory [$value];
	}
}