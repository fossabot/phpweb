<?php

namespace app\zx_apply\model;

use think\Model;
use think\Db;

class Iptables extends Model {
	public static function createIp($zxType = "", $ip = "", $mask = "255.255.255.255") {
		$iptables = new static ();
		$data = [ 
				"ipType" => $zxType,
				"ip" => ip2long ( $ip ),
				"mask" => ip2long ( $mask ),
				"ifUse" => 1 
		];
		$iptables->isUpdate ( false )->allowField ( true )->save ( $data );
	}
	
	/**
	 * 自动预分配ip
	 *
	 * @param string $zxType        	
	 */
	public static function generateIP($zxType = "互联网") {
		Db::name ( "iptables" )->where ( "ipType", $zxType )->colnum ( "ip" );
	}
	/**
	 * 是否已分配
	 * 
	 * @param unknown $zxType        	
	 * @param unknown $ip        	
	 * @return array
	 */
	public static function check($zxType, $ip) {
		$data = Db::name ( "iptables" )->where ( [ 
				"ipType" => $zxType,
				"ip" => ip2long($ip) 
		] )->column ( "ifUse" );
		return $data;
	}
	/**
	 * 判断ip是否可用
	 *
	 * @param unknown $long        	
	 * @return boolean
	 */
	private function ifCanUse($long) {
		if ($long < 0) {
			// 2^31-(-x) 设计负数如何转化并计算。
			$array = explode ( ",", "-255,0,-1" );
		} else {
			$array = explode ( ",", "0,1,255" );
		}
		return in_array ( $long % 256, $array );
	}
}