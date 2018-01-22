<?php

namespace app\zx_apply\model;

use think\Model;
use think\Db;

class Vlantables extends Model {
	/**
	 * 录入vlan
	 *
	 * @param string $aStation        	
	 * @param string $vlan        	
	 * @param string $cName        	
	 */
	public static function createVlan($aStation = "", $vlan = "", $cName = "") {
		$devices = json_decode ( config ( "device9312" ), true );
		
		$date = [ 
				"deviceName" => $devices [config ( "aStation" ) [$aStation]],
				"vlan" => $vlan,
				"description" => $cName 
		];
		$this->isUpdate ( false )->save ( $date );
	}
	/**
	 * 自动预分配vlan
	 *
	 * @param string $aStation        	
	 * @param string $cName        	
	 */
	public static function generateVlan($aStation = "", $cName = "") {
	}
	/**
	 * 导入更新已使用vlan
	 *
	 * @param string $str        	
	 * @return void|string
	 */
	public static function importUsedVlan($aStation, $str = "") {
		$str = str_replace ( "vlan batch", "", $str );
		$str = str_replace ( "\r\n", "", $str );
		$array = explode ( " ", $str );
		$result = [ ];
		for($i = 0; $i < count ( $array );) {
			// 移除空值
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
			$vlanInfo = Db::name("vlantables")->where(["deviceName"=>$aStation,"vlan"=>$v])->select();
			if(!$vlanInfo){
				// 无信息，则insert
				$vlanInfo = Db::name("vlantables")->insert(["deviceName"=>$aStation,"vlan"=>$v,"description"=>"手动导入-".date("Y-m-d h:i:s",time())]);
			}
		}
		return dump ( $array );
	}
}