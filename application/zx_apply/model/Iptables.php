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
				"ip" => ip2long ( $ip ) 
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
	/**
	 * ip转换
	 * ip字符串10.10.10.10/32转成ip2long形式的数组:ip/mask/ip_start/ip_end
	 *
	 * @param unknown $ip_str        	
	 * @return boolean[]|number[]
	 */
	public static function ip_parse($ip_str) {
		$mask_len = 32;
		if (strpos ( $ip_str, "/" ) > 0) {
			list ( $ip_str, $mask_len ) = explode ( "/", $ip_str );
		}
		$ip = ip2long ( $ip_str );
		$mask = 0xFFFFFFFF << (32 - $mask_len) & 0xFFFFFFFF;
		$ip_start = $ip & $mark;
		$ip_end = $ip | (~ $mark) & 0xFFFFFFFF;
		return array (
				$ip,
				$mask,
				$ip_start,
				$ip_end 
		);
	}
	/**
	 * ip、掩码转换成ip字符串10.10.10.10/32
	 *
	 * @param unknown $ip        	
	 * @param unknown $subnet_mask        	
	 * @return string
	 */
	public static function ip_export($ip, $subnet_mask = "") {
		if ($subnet_mask == - 1 | $subnet_mask == "") {
			return long2ip ( $ip );
		} else {
			$suffix = strlen ( preg_replace ( "/0/", "", decbin ( $subnet_mask ) ) );
			return long2ip ( $ip ) . "/" . $suffix;
		}
	}
}