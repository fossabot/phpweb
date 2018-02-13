<?php

namespace app\zx_apply\model;

use think\Model;

class Infotables extends Model {
	protected $autoWriteTimestamp = true;
	protected $type = [ 
			"aDate" => "date",
			"extra" => "array" 
	];
	public function setIpAttr($value) {
		return ip2long ( $value );
	}
	public function getIpAttr($value) {
		return long2ip ( $value );
	}
	public function setNeFactoryAttr($value) {
		if (strlen ( $value ) == 1 && is_int ( $value )) {
			return $value;
		} else {
			$ne = array_search ( $value, [ 
					"华为",
					"中兴",
					"OLT" 
			] );
			return $ne ? $ne : null;
		}
	}
	public function getNeFactoryAttr($value) {
		$zx_nefactory = [ 
				0 => "华为",
				1 => "中兴",
				2 => "OLT" 
		];
		return $zx_nefactory [$value];
	}
	public function getStatusAttr($value) {
		$statusArr = [ 
				0 => "申请",
				1 => "预分配",
				2 => "已流程",
				3 => "做数据",
				4 => "ip备案",
				9 => "导入" 
		];
		return array_key_exists ( $value, $statusArr ) ? $statusArr [$value] : "";
	}
	/**
	 * 新增Info，type可选导入、申请
	 *
	 * @param string $data        	
	 * @param string $type        	
	 * @return number[]|\think\false[]
	 */
	public static function createInfo($data = "", $type = "") {
		$result = [ ];
		$infotables = new static ();
		if ($type == "import") {
			foreach ( $data as $k => $d ) {
				$data [$k] = array_merge ( [ 
						"tags" => "导入" 
				], $data [$k] );
				Iptables::createIp ( $data [$k] ["zxType"], $data [$k] ["ip"] );
				Vlantables::createVlan ( $data [$k] ["aStation"], $data [$k] ["vlan"], $data [$k] ["cName"] );
				$result [] = $infotables->isUpdate ( false )->allowField ( true )->save ( $data [$k] );
			}
		}
		if ($type == "apply") {
			$data = array_merge ( [
					"tags" => "申请"
			], $data );
			$data ["status"] = 0;
			$result = $infotables->isUpdate ( false )->allowField ( true )->save ( $data );
		}
		return $result;
	}
}