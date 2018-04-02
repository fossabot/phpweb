<?php

namespace app\zx_apply\model;

use think\Model;

class Infotables extends Model {
	protected $autoWriteTimestamp = true;
	protected $dateFormat = 'Y-m-d';
	protected $type = [ 
			// "aDate" => "date",
			"extra" => "array" 
	];
	public function setIpAttr($value) {
		if (is_int ( $value )) {
			return $value;
		} else {
			return Iptables::ip_parse ( $value ) [2];
		}
	}
	public function getIpAttr($value, $data) {
		return Iptables::ip_export ( $value, $data ["ipMask"] );
		return $value ? long2ip ( $value ) : null;
	}
	public function setIpBAttr($value) {
		if (is_int ( $value ) || "" == $value) {
			return $value;
		} else {
			return Iptables::ip_parse ( $value ) [2];
		}
	}
	public function getIpBAttr($value, $data) {
		return Iptables::ip_export ( $value, $data ["ipBMask"] );
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
			return is_int ( $ne ) ? $ne : null;
		}
	}
	/*
	 * public function setIpMask(){
	 *
	 * }
	 */
	public function getNeFactoryAttr($value) {
		$zx_nefactory = [ 
				0 => "华为",
				1 => "中兴",
				2 => "OLT" 
		];
		return is_null ( $value ) ? null : $zx_nefactory [$value];
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
		if ($type == "import") {
			foreach ( $data as $k => $d ) {
				$infotables = new static ();
				$data [$k] = array_merge ( [ 
						"tags" => "导入",
						"status" => 9 
				], $data [$k] );
				// Iptables::createIp ( $data [$k] ["zxType"], $data [$k] ["ip"] );
				if ($data [$k] ["vlan"]) {
					// 如果vlan不为空，则记录vlan表
					Vlantables::createVlan ( $data [$k] ["aStation"], $data [$k] ["vlan"], $data [$k] ["cName"] );
				}
				$data [$k] = array_filter ( $data [$k] ); // 清除空元素
				                                          // $result [] = $data [$k];
				$result [] = $infotables->isUpdate ( false )->allowField ( true )->save ( $data [$k], [ ] );
			}
		}
		if ($type == "apply") {
			$infotables = new static ();
			$data ["tags"] = "申请";
			$data ["status"] = 0;
			$result = $infotables->isUpdate ( false )->allowField ( true )->save ( $data, [ ] );
		}
		return $result;
	}
}