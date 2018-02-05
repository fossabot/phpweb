<?php

namespace app\zx_apply\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\zx_apply\model\Infotables;

class Index extends Common {
	
	/*
	 * protected $beforeActionList = [
	 * 'checkAuth'
	 * ];
	 */
	
	/**
	 * FOR TEST
	 *
	 * @return void|string
	 */
	public function tt() {
		$t =array_search("", [
				"华为",
				"中兴",
				"OLT"
		]);
		return dump($t?$t:null);
	}
	
	/**
	 * 首页-登录
	 *
	 * @return mixed|string|void
	 */
	public function index() {
		if (request ()->isGet ()) {
			return $this->fetch ();
		}
		if (request ()->isPost ()) {
			// post请求 验证登陆
			$user = Db::table ( "phpweb_check" )->where ( 'email', input ( "post.email" ) )->order ( "time desc" )->find ();
			$msg = "";
			if ($user & $user ["code"] != input ( "post.code" )) {
				$msg = "验证码验证错误";
			} else {
				// 验证码正确，继续验证申请人姓名
				if ($user ["name"] != input ( "post.name" )) {
					$msg = "申请人姓名与申请时不一致<br />申请时为：<b>" . $user ["name"] . "</b><br />申请时间：" . $user ["time"];
				}
			}
			if ($msg) {
				$this->writeLog ( "登陆", "failed", $msg );
				return $this->error ( $msg, null, input ( "post." ) );
			} else if (time () - strtotime ( $user ["time"] ) > 3600 * 24 * 15) {
				$msg = "登陆超时，请重新获取验证码。";
				$this->writeLog ( "登陆", "failed", $msg );
				unset ( $user ["code"] );
				return $this->error ( $msg, "index", $user );
			} else {
				session ( "user", $user );
				$msg = "欢迎回来，" . $user ["name"] . "。";
				$this->writeLog ( "登陆", "success", $msg );
				$url = session ( "to_url" ) ? session ( "to_url" ) : "query";
				return $this->success ( $msg, $url, $user );
			}
		}
	}
	protected function writeLog($k, $status, $msg) {
		$this->log ( $k, [ 
				"status" => $status,
				"name" => input ( "post.name" ),
				"email" => input ( "post.email" ),
				"msg" => $msg 
		] );
	}
	/**
	 * get:加载数据到handsontable并验证,
	 * post:上传,后台处理入库
	 * 1.默认post为新申请,移除ip、vlan信息再保存,
	 * 严格验证，不合规不许提交，标记status：0
	 * 2.带post参数type=import,视为旧信息导入,
	 * 生成ip表（全）和vlan表信息（不全）。直接入库，并标记tags:导入
	 * 3.为了新增字段不修改数据库，将新增字段用json保存到一列。
	 * 在csv转数组时，需要获取额外的字段
	 *
	 * @return void|string|mixed|string
	 */
	public function _ht_apply() {
		if (request ()->isPost ()) {
			$postData = input ( "post.data" );
			// return dump($postData);
			$type = input ( "post.type" );
			$zxInfoTitle = input ( "post.zxInfoTitle", null, null );
			$zxInfoTitle = json_decode ( $zxInfoTitle, JSON_UNESCAPED_UNICODE );
			$dataHeader = $this->getHeader ( $zxInfoTitle ["label"], $zxInfoTitle ["order"], true );
			// 获取数据库的列名
			$dataHeader = explode ( ",", $dataHeader );
			// 根据列名和数据转成php数组
			// $postData = substr ( $postData, 3 ); // 莫名奇妙的前三个字节是垃圾数据。3天才研究出来，只能这样解决！！！
			$data = $this->csv_to_array ( $dataHeader, $postData );
			// return dump($data);
			// 获取额外的字段
			$extraHeader = config ( "extraInfo" );
			foreach ( $data as $k => $v ) {
				$temp = [ ];
				foreach ( $extraHeader as $kk => $vv ) {
					$temp [$kk] = $v [$vv];
				}
				$data [$k] ["extra"] = $temp;
				if ($v ["aStation"] == "柴河局") {
					$data [$k] ["aStation"] .= "-" . $data [$k] ["neFactory"];
				}
			}
			// 若导入，ip/vlan信息要单独存储。
			$result = Infotables::createInfo ( $data, $type );
			// $result = $info->save($data[0]);
			return dump ( $result );
		}
		if (request ()->isGet ()) {
			if (input ( '?get.zxInfoTitle' ) && input ( '?get.t' )) {
				return $this->fetch ();
			}
		}
	}
	
	/**
	 * ip、vlan申请
	 *
	 * @return mixed|string
	 */
	public function apply() {
		// $zxInfoTitle = Db::table("phpweb_sysinfo")->field("value,option")->where("label","zxInfoTitle")->select();
		$zxInfoTitle = [ 
				"label" => "zx_apply-223-rb",
				"order" => "0,1,2,3,4,5,6,7,8,9,10,12,13,14,15,16,17,18,19,30,31,32,33,34,35,36,37,38,39" 
		];
		$this->assign ( 'zxInfoTitle', json_encode ( $zxInfoTitle, 256 ) );
		return $this->fetch ();
	}
	
	/**
	 * 根据label、order 获取表格的 header
	 * $v为false，获取option(default)；为ture，获取value
	 *
	 * @param String $label        	
	 * @param String $order        	
	 * @param boolean $v        	
	 * @return string
	 */
	public function getHeader($label = "label", $order = "order", $v = false) {
		if ($label === "label" || $order === "order") {
			return "{msg:\"你要搞什么？\"}"; // 未输入参数label或order
		}
		$_headerData = Db::table ( "phpweb_sysinfo" )->field ( "value,option" )->where ( [ 
				"label" => $label 
		] )->order ( "id" )->select ();
		$orderArr = explode ( ",", $order );
		$headerArr = [ ];
		$sub = $v ? "value" : "option";
		foreach ( $orderArr as $o ) {
			$headerArr [] = $_headerData [$o] [$sub];
		}
		return implode ( ",", $headerArr );
	}
	/**
	 * 信息查询
	 */
	public function query() {
		return $this->fetch ( "index/query" );
	}
	/**
	 * 更新信息
	 */
	public function update() {
		return $this->fetch ( "index/update" );
	}
}
