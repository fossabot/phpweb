<?php

namespace app\zx_apply\model;

use think\Model;

class Iptables extends Model {
	public static function createIp($zxType = "", $ip = "", $mask = "255.255.255.255") {
		$data = [ 
				"ipType" => $zxType,
				"ip" => ip2long ( $ip ),
				"mask" => ip2long ( $mask ),
				"ifUse" => 1 
		];
		$this->isUpdate ( false )->save ( $data );
	}
}