<?php

namespace app\zx_apply\model;

use think\Model;
use think\Db;

class Iptables extends Model {
	/*
	 * public static function createIp($zxType = "", $ip = "", $mask = "255.255.255.255") {
	 * $iptables = new static ();
	 * $data = [
	 * "ipType" => $zxType,
	 * "ip" => ip2long ( $ip ),
	 * "mask" => ip2long ( $mask ),
	 * "ifUse" => 1
	 * ];
	 * $iptables->isUpdate ( false )->allowField ( true )->save ( $data );
	 * }
	 */
	
	/**
	 * 自动预分配ip
	 *
	 * @param string $zxType        	
	 * @return number|string
	 */
	public static function generateIP($zxType = "互联网") {
		$ips = Db::name ( "infotables" )->where ( "zxType", $zxType )->where ( "ip", "NOT NULL" )->order ( "ip desc" )->limit ( 1 )->column ( "ip" );
		if ($ips) {
			return $ips [0] + 1;
		} else {
			return "暂无参考，请手动分配";
		}
	}
	/**
	 * 是否已分配
	 * 
	 * @param unknown $zxType        	
	 * @param string $ip_str        	
	 * @param string $filed        	
	 * @return array|\think\db\false|PDOStatement|string|\think\Model
	 */
	public static function check($zxType, $ip_str = "", $filed = "ip") {
		$ip = self::ip_parse ( $ip_str );
		$data = Db::name ( "infotables" )->where ( [ 
				"zxType" => $zxType,
				$filed => $ip [2],
				$filed . "Mask" => $ip [1] 
		] )->field ( "id,cName" )->find ();
		return $data;
	}
	/**
	 * 判断ip是否可用
	 *
	 * @param unknown $zxType        	
	 * @param unknown $long        	
	 * @param unknown $mask        	
	 * @return boolean
	 */
	private function ifCanUse($zxType, $long, $mask = -1) {
		
		// todo!!
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
	public static function ip_parse($ip_str = "") {
		if ($ip_str == "") {
			return [ 
					null,
					null,
					null,
					null 
			];
		}
		$mask_len = 32;
		if (strpos ( $ip_str, "/" ) > 0) {
			list ( $ip_str, $mask_len ) = explode ( "/", $ip_str );
		}
		$ip = ip2long ( $ip_str );
		$mask = 0xFFFFFFFF << (32 - $mask_len) & 0xFFFFFFFF;
		$ip_start = $ip & $mask;
		$ip_end = $ip | (~ $mask) & 0xFFFFFFFF;
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
	 * @param unknown $long        	
	 * @param unknown $subnet_mask        	
	 * @return string
	 */
	public static function ip_export($long, $subnet_mask = "") {
		if (! $long) {
			return null;
		}
		if ($subnet_mask == - 1 | $subnet_mask == "") {
			return long2ip ( $long );
		} else {
			$suffix = strlen ( preg_replace ( "/0/", "", decbin ( $subnet_mask ) ) );
			return long2ip ( $long ) . "/" . $suffix;
		}
	}
}