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
		return array_search ( $value, [ 
				"华为",
				"中兴",
				"OLT" 
		] );
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
		foreach ( $data as $k => $d ) {
			if ($type == "import") {
				$data [$k] = array_merge ( [ 
						"tags" => "导入" 
				], $data [$k] );
				Iptables::createIp ( $data [$k] ["zxType"], $data [$k] ["ip"] );
				Vlantables::createVlan ( $data [$k] ["aStation"], $data [$k] ["vlan"], $data [$k] ["cName"] );
			}
			if ($type == "apply") {
				$data [$k] ["status"] = 0;
				unset ( $data [$k] ["ip"] );
				unset ( $data [$k] ["vlan"] );
			}
			$infotables = new static();
			$result [] = $infotables->isUpdate ( false )->allowField ( true )->save ( $data [$k] );
		}
		return $result;
	}
}