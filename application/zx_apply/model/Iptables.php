<?php

namespace app\zx_apply\model;

use think\Model;

class Iptables extends Model {
	public static function createIp($zxType = "", $ip = "", $mask = "255.255.255.255") {
		$iptables = new static();
		$data = [ 
				"ipType" => $zxType,
				"ip" => ip2long ( $ip ),
				"mask" => ip2long ( $mask ),
				"ifUse" => 1 
		];
		$iptables->isUpdate ( false )->allowField ( true )->save ( $data );
	}
	public static function generateIP($zxType = "互联网") {
		Db::name ( "iptables" )->where ( "ipType", $zxType )->colnum ( "ip" );
	}
	
	/**
	 * 判断ip是否可用
	 *
	 * @param unknown $long        	
	 * @return boolean
	 */
	private function ifNormal($long) {
		if ($long < 0) {
			//2^31-(-x) 设计负数如何转化并计算。
			$array = explode ( ",", "-255,0,-1" );
		} else {
			$array = explode ( ",", "0,1,255" );
		}
		return in_array ( $long % 256, $array );
	}
}