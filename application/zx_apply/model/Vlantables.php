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
		$deviceConf = config ( "aStation" );
		if (array_key_exists ( $device, $deviceConf )) {
			// 根据a端匹配到9312名，则保存vlan
			$data = [ 
					"deviceName" => $deviceConf [$device],
					"vlan" => $vlan == 0 ? null : $vlan,
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
		for($vlan = 2049; $vlan < 3071; $vlan ++) {
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
	 * @param string $deviceName        	
	 * @param string $str        	
	 * @return void|string
	 */
	public static function importUsedVlan($deviceName = "", $str = "") {
		preg_match_all ( '/vlan batch ([\S ]+)/', $str, $str ); // 匹配后结果输出到$str
		$str = implode ( " ", $str [1] );
		// $str = str_replace ( 'vlan batch ', '', $str );
		// $str = preg_replace ( '/[\r\n]/', '', $str );
		$array = explode ( " ", $str );
		$result = [ ];
		$count = count ( $array );
		for($i = 0; $i < $count;) {
			// 遍历数组，替换to，补充连续的vlan
			if ($array [$i] == "to") {
				array_splice ( $array, $i, 1 );
				// 补充连续的vlan
				for($j = $array [$i - 1] + 1; $j < $array [$i]; $j ++) {
					$array [] = $j;
				}
			}
			$array [$i] = ( int ) $array [$i]; // 前端获取的默认是string类型
			$i ++;
		}
		sort ( $array, SORT_NUMERIC ); // 排序
		$validation = 0;
		foreach ( $array as $v ) {
			if ($array [$i] < 2000 || $array [$i] > 3000) {
				// 范围之外的vlan，无操作
				continue;
			}
			// 已有，则放弃
			$vlanInfo = Db::name ( "vlantables" )->where ( [ 
					"deviceName" => $deviceName,
					"vlan" => $v 
			] )->select ();
			if (! $vlanInfo) {
				// 无信息，则insert
				$vlanInfo = Db::name ( "vlantables" )->insert ( [ 
						"deviceName" => $deviceName,
						"vlan" => $v,
						"description" => "手动导入-" . date ( "Y-m-d h:i:s", time () ) 
				] );
				$validation ++;
			}
		}
		return [ 
				"count" => count ( $array ),
				"validation" => $validation 
		];
	}
	/**
	 * 检查vlan是否已分配
	 * 
	 * @param string $zxType        	
	 * @param string $aStation        	
	 * @param number $vlan        	
	 * @return array
	 */
	public static function check($zxType = "", $aStation = "", $vlan = 0) {
		$data = Db::name ( "infotables" )->where ( [ 
				"zxType" => $zxType,
				"aStation" => $aStation,
				"vlan" => $vlan 
		] )->field( "id,cName" )->find();
		return $data;
	}
}