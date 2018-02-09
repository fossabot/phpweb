<?php

namespace app\zx_apply\model;

use think\Model;
use think\Db;

class Vlantables extends Model {
	/**
	 * 录入vlan
	 *
	 * @param string $device        	
	 * @param string $vlan        	
	 * @param string $cName        	
	 */
	public static function createVlan($device = "", $vlan = "", $description = "") {
		$vlantables = new static ();
		$deviceConf = config ( "device" );
		if (array_key_exists ( $device, $deviceConf )) {
			// 根据a端匹配到9312名，则保存vlan
			$data = [ 
					"deviceName" => $deviceConf [$device],
					"vlan" => $vlan,
					"description" => $description 
			];
			$vlantables->isUpdate ( false )->allowField ( true )->save ( $data );
		}
	}
	/**
	 * 自动预分配vlan，返回预分配vlan
	 *
	 * @param string $device        	
	 * @param string $cName        	
	 * @param string $manual
	 *        	0 for write to db(default), 1 for pre-generate.
	 * @return number|string
	 */
	public static function generateVlan($device = "", $cName = "", $manual = false) {
		$vlans = Db::name ( "vlantables" )->where ( "deviceName", $device )->column ( "vlan" );
		for($vlan = 2049; $vlan < 3000; $vlan ++) {
			if (! in_array ( $vlan, $vlans )) {
				$preVlan = $vlan;
				break;
			}
		}
		if ($manual) {
			// 手动分类，推荐空闲vlan
			$result = [ 
					"preVlan" => $preVlan,
					"vlans" => $vlans 
			];
			return $result;
		} else {
			Db::name ( "vlantables" )->insert ( [ 
					"deviceName" => $device,
					"vlan" => $preVlan,
					"description" => $cName 
			] );
			$result = $preVlan;
		}
		return $result;
	}
	/**
	 * 导入更新已使用vlan
	 *
	 * @param string $device        	
	 * @param string $str        	
	 * @return void|string
	 */
	public static function importUsedVlan($device = "", $str = "") {
		$str = str_replace ( "vlan batch", "", $str );
		$str = str_replace ( "\r\n", "", $str );
		$array = explode ( " ", $str );
		$result = [ ];
		for($i = 0; $i < count ( $array );) {
			// 移除空值和范围之外的vlan
			if ((! $array [$i]) || $array [$i] < 2000 || $array [$i] > 3000) {
				array_splice ( $array, $i, 1 );
			} else {
				// 替换 "to"
				if ($array [$i] == "to") {
					array_splice ( $array, $i, 1 );
					// 补充连续的vlan
					for($j = $array [$i - 1] + 1; $j < $array [$i]; $j ++) {
						$array [] = $j;
					}
				}
				$array [$i] = ( int ) $array [$i];
				$i ++;
			}
		}
		sort ( $array, SORT_NUMERIC );
		foreach ( $array as $v ) {
			// 已有，则放弃
			$vlanInfo = Db::name ( "vlantables" )->where ( [ 
					"deviceName" => $device,
					"vlan" => $v 
			] )->select ();
			if (! $vlanInfo) {
				// 无信息，则insert
				$vlanInfo = Db::name ( "vlantables" )->insert ( [ 
						"deviceName" => $device,
						"vlan" => $v,
						"description" => "手动导入-" . date ( "Y-m-d h:i:s", time () ) 
				] );
			}
		}
		return dump ( $array );
	}
}