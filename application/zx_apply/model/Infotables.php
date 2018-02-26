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
		return $value ? long2ip ( $value ) : null;
	}
	public function setIpBAttr($value) {
		return ip2long ( $value );
	}
	public function getIpBAttr($value) {
		return $value ? long2ip ( $value ) : null;
	}
	public function setNeFactoryAttr($value) {
		// if (preg_match_all ( "/[0-9]/", $tt ) == strlen ( $tt )) {
		if (is_numeric ( $value ) && floor ( $value ) == $value) {
			// 是数字，且是整数
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
						"tags" => "导入",
						"status" => 9 
				], $data [$k] );
				// Iptables::createIp ( $data [$k] ["zxType"], $data [$k] ["ip"] );
				if ($data [$k] ["vlan"]) {	
					// 如果vlan不为空，则记录vlan表
					Vlantables::createVlan ( $data [$k] ["aStation"], $data [$k] ["vlan"], $data [$k] ["cName"] );
				}
				$data [$k] = array_filter ( $data [$k] );	// 清除空元素
				$result [] = $infotables->isUpdate ( false )->allowField ( true )->save ( $data [$k] );
			}
		}
		if ($type == "apply") {
			$data ["tags"] = "申请";
			$data ["status"] = 0;
			$result = $infotables->isUpdate ( false )->allowField ( true )->save ( $data );
		}
		return $result;
	}
	public static function updateInfo($data = "") {
		$extraHeader = config ( "extraInfo" );
		foreach ( $extraHeader as $k => $v ) {
			$data ["extra"] [$v] = $data [$v];
			unset ( $data [$v] );
		}
		$result = Infotables::update ( $data ); // 更新单条数据
		
		return $result;
	}
}