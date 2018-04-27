<?php

namespace app\zx_apply\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\zx_apply\model\Infotables;
use Overtrue\Pinyin\Pinyin;
use think\Error;

class Index extends Common {
	
	/*
	 * protected $beforeActionList = [
	 * 'checkAuth'
	 * ];
	 */
	public function ch2arr($str) {
		$length = mb_strlen ( $str, 'utf-8' );
		$array = [ ];
		for($i = 0; $i < $length; $i ++)
			$array [] = mb_substr ( $str, $i, 1, 'utf-8' );
		return $array;
	}
	/**
	 * FOR TEST
	 *
	 * @return void|string
	 */
	public function tt() {
		$data = Infotables::get ( 62 )->toArray ();
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
			if (! $user) {
				return $this->result ( [ 
						"code" => 0 
				], 0, "该邮箱还未申请验证码" );
			}
			if ($user ["code"] != input ( "post.code" )) {
				$msg = "验证码错误";
			} else {
				// 验证码正确，继续验证申请人姓名
				if ($user ["name"] != input ( "post.name" )) {
					$msg = "申请人姓名与申请时不一致<br />申请时为：<b>" . $user ["name"] . "</b><br />申请时间：" . $user ["time"];
				}
			}
			if ($msg) {
				$this->writeLog ( "登陆", "failed", $msg );
				return $this->error ( $msg, null, input ( "post." ) );
			} else if (time () - strtotime ( $user ["time"] ) > 3600 * 24 * 15) { // 15天内可直接登陆
				$msg = "登陆超时，请重新获取验证码。";
				$this->writeLog ( "登陆", "failed", $msg );
				unset ( $user ["code"] );
				return $this->error ( $msg, "index", $user );
			} else {
				$e = explode ( "@", $user ["email"] );
				// 附加role。
				if ($e [1] == "ln.chinamobile.com" && in_array ( $e [0], config ( "manageEmails" ) )) {
					$user ["role"] = "manage";
				} else {
					$user ["role"] = "index";
				}
				session ( "user", $user );
				$msg = "欢迎回来，" . $user ["name"] . "。";
				$this->writeLog ( "登陆", "success", $msg );
				$url = session ( "to_url" ) ? session ( "to_url" ) : session ( "user.role" ) . "/query";
				session ( "to_url", null );
				return $this->success ( $msg, $url, $user );
			}
		}
	}
	protected function writeLog($k, $status, $msg) {
		$this->log ( $k, [ 
				"status" => $status,
				"name" => input ( "post.name" ),
				"email" => input ( "post.email" ),
				"msg" => strip_tags ( $msg ) 
		] );
	}
	
	/**
	 * 数据专线申请开通
	 */
	public function apply() {
		if (request ()->isGet ()) {
			return $this->fetch ();
		} else if (request ()->isPost ()) {
			$data = input ( "post." );
			$this->checkInstanceID ( null, $data ["instanceId"] ); // 检查instanceId
			$extraHeader = config ( "extraInfo" );
			foreach ( $extraHeader as $k => $v ) {
				$data ["extra"] [$v] = $data [$v];
				unset ( $data [$v] );
			}
			$result = Infotables::createInfo ( $data, "apply" );
			$redirectUrl = "../" . session ( "user.role" ) . "/query.html";
			return $this->result ( null, $result, $redirectUrl );
			// return json_encode ( $data, 256 );
		}
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
	 * 根据order获取handsontable组件的colWidth
	 *
	 * @param unknown $order        	
	 * @return string|void
	 */
	protected function getColWidths($order = null) {
		if (! is_null ( $order )) {
			$orderArr = explode ( ",", $order );
			$result = [ ];
			foreach ( $orderArr as $v ) {
				$result [] = config ( "colWidth" ) [$v];
			}
			return implode ( ",", $result );
		}
		return $this->result ( null, 0, "~缺参数~" );
	}
	/**
	 * 信息查询
	 */
	public function query() {
		if (request ()->isGet ()) {
			// 访问
			$aStation = array_keys ( config ( "aStation" ) );
			$zxTitle = [ 
					"label" => "zx_apply-new-rb",
					"order" => "24,1,2,3,4,5,6,7,8,9,10,12,13,14,15,16,17,18,19,20,29,30,31,32,33,34,35,36,37,26,22,23" 
			];
			$this->assign ( [ 
					"aStationData" => implode ( ",", $aStation ),
					"colHeaderData" => $this->getHeader ( $zxTitle ["label"], $zxTitle ["order"] ),
					"colWidthsData" => $this->getColWidths ( $zxTitle ["order"] ),
					"data" => $this->getInfoData ()->toJson () 
			] );
			return $this->fetch ();
		}
		if (request ()->isPost ()) {
			// 获取台账
			// return $this->getInfoData();
			input ( "post.r" ) == "info" && $data = $this->getInfoData ()->toArray ();
			input ( "post.r" ) == "search" && $data = $this->querySearch ( input ( "post." ) );
			input ( "get.r" ) == "update" && $data = $this->queryUpdateInfo ( input ( "post." ) );
			return $data;
		}
		if (request ()->isPut ()) {
			// 相关操作
			input ( "post.r" ) == "export" && $data = $this->queryExport ();
			return $data;
		}
	}
	/**
	 * 获取台账信息
	 *
	 * @param number $limit        	
	 * @return string
	 */
	private function getInfoData($limit = 100) {
		// return collection ( Infotables::order ( "create_time desc" )->limit ( $limit )->select () );
		return collection ( Infotables::where ( "aPerson", session ( "user.name" ) )->order ( "create_time desc" )->limit ( $limit )->select () );
	}
	protected function querySearch($data) {
		$result = collection ( Infotables::where ( $data ["where"] [0], "like", "%" . $data ["where"] [2] . "%" )->order ( "create_time desc" )->select () )->toArray ();
		return $result;
	}
	
	/**
	 * 从query.html更新台账
	 *
	 * @param unknown $updateData        	
	 * @return number|\think\false
	 */
	protected function queryUpdateInfo($updateData) {
		$result = 0;
		$new = [ ];
		$infotables = new Infotables ();
		foreach ( $updateData as $k => $v ) {
			$line_and_id = explode ( "-", $k );
			$result += $infotables->isUpdate ( true )->allowField ( true )->save ( $v, [ 
					"id" => $line_and_id [1] 
			] );
			// 反查询刚才修改后的数据库里的值，用于前后端数据的一致性
			$data = $infotables->where ( "id", $line_and_id [1] )->find ();
			foreach ( $v as $kk => $vv ) {
				$dbNew [$k] [$kk] = $data->$kk;
			}
		}
		return $this->result ( $dbNew, 1, $result );
	}
	protected function queryExport() {
		$data = collection ( Infotables::where ( "aPerson", session ( "user.name" ) )->order ( "create_time" )->select () )->toArray ();
		$colHeader = "申请时间,产品实例标识,专线类别,带宽,网元厂家,A端基站,客户名称,单位详细地址,客户需求说明(选填),VLAN,IP,联系人姓名(客户侧),联系电话(客户侧),联系人邮箱(客户侧)*,负责人姓名(移动侧)*,负责人电话(移动侧)*,负责人邮箱(移动侧)*,备注,是否ONU带\n(默认为否),单位性质*,单位分类*,行业分类*,使用单位证件类型*,使用单位证件号码*,单位所在省*,单位所在市*,单位所在县*,应用服务类型*";
		$colName = "create_time,instanceId,zxType,bandWidth,neFactory,aStation,cName,cAddress,cNeeds,vlan,ip,cPerson,cPhone,cEmail,mPerson,mPhone,mEmail,marks,ifOnu,extra.unitProperty,extra.unitCategory,extra.industryCategory,extra.credential,extra.credentialnum,extra.province,extra.city,extra.county,extra.appServType";
		return [ 
				"data" => $data,
				"colHeader" => $colHeader,
				"colName" => $colName 
		];
	}
	/**
	 * 更新信息
	 */
	public function update() {
		return $this->fetch ( "index/update" );
	}
	/**
	 * 检查 instanceId 是否重复
	 * 可输入$data数组或instanceId
	 *
	 * @param unknown $info        	
	 * @param unknown $data        	
	 */
	protected function checkInstanceID($info, $data) {
		if (null == $info) {
			$instanceId = $data;
		} else if ($info ["id"] != $data ["id"]) {
			$instanceId = $data ["instanceId"];
		} else {
			return;
		}
		$info = Infotables::get ( [ 
				"instanceId" => $instanceId 
		] );
		if ($info) {
			return $this->error ( "实例标识重复，请重试", null, "该实例标识对应客户名为：<br>" . $info ["cName"]."<br>申请人：".$info["aPerson"] );
		}
	}
}
