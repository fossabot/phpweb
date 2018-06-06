<?php

namespace app\zx_apply\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\zx_apply\model\Infotables;
use Overtrue\Pinyin\Pinyin;
use think\Error;
use think\Cache;

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
		return "This is <b>Index->tt";
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
			// 发邮件通知
			$subject = "[待办]ip申请-" . ($data ["ifOnu"] ? "onu" : "9312") . "-" . $data ["cName"] . $data ["instanceId"];
			$body = "<p>请登陆系统及时处理：</p><br> 内网： <a href='http://10.65.178.202/zx_apply/index/index.html#Manage/todo'>http://10.65.178.202/zx_apply/index/index.html#Manage/todo</a><br>外网： <a href='http://223.100.98.60:800/zx_apply/index/index.html#Manage/todo'>http://223.100.98.60:800/zx_apply/index/index.html#Manage/todo</a>";
			$this->sendManageNotice ( $subject, $body );
			$v = [ 
					"username" => session ( "user.name" ),
					"email" => session ( "user.email" ),
					"cName" => $data ["cName"],
					"instanceId" => $data ["instanceId"] 
			];
			$this->log ( "提交申请", $v );
			$redirectUrl = "../" . session ( "user.role" ) . "/query.html";
			return $this->result ( null, $result, $redirectUrl );
			// return json_encode ( $data, 256 );
		}
	}
	/**
	 * 修改提交
	 */
	public function re_apply(){
		if (request ()->isGet ()) {
			return $this->error("Nothing here. Do not try again!");
		}
		$data = input ( "post." );
		$this->checkInstanceID ( null, $data ["instanceId"] ); // 检查instanceId
		$extraHeader = config ( "extraInfo" );
		foreach ( $extraHeader as $k => $v ) {
			$data ["extra"] [$v] = $data [$v];
			unset ( $data [$v] );
		}
		$result = Infotables::createInfo ( $data, "apply" );
		// 发邮件通知
		$subject = "[待办]ip申请-" . ($data ["ifOnu"] ? "onu" : "9312") . "-" . $data ["cName"] . $data ["instanceId"];
		$body = "<p>请登陆系统及时处理：</p><br> 内网： <a href='http://10.65.178.202/zx_apply/index/index.html#Manage/todo'>http://10.65.178.202/zx_apply/index/index.html#Manage/todo</a><br>外网： <a href='http://223.100.98.60:800/zx_apply/index/index.html#Manage/todo'>http://223.100.98.60:800/zx_apply/index/index.html#Manage/todo</a>";
		$this->sendManageNotice ( $subject, $body );
		$v = [
				"username" => session ( "user.name" ),
				"email" => session ( "user.email" ),
				"cName" => $data ["cName"],
				"instanceId" => $data ["instanceId"]
		];
		$this->log ( "提交申请", $v );
		$redirectUrl = "../" . session ( "user.role" ) . "/query.html";
		return $this->result ( null, $result, $redirectUrl );
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
					"order" => "26,24,1,2,3,4,5,6,7,8,9,10,12,13,14,15,16,17,18,19,20,29,30,31,32,33,34,35,36,37,22,23" 
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
			input ( "post.r" ) == "brief" && $data = $this->querySearchBrief ( input ( "post." ) );
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
	 * @param string $zxType        	
	 * @param number $limit        	
	 * @return \think\model\Collection|\think\Collection
	 */
	protected function getInfoData($zxType = "互联网", $limit = 100) {
		$where = "";
		if (session ( "user.role" ) != "manage") {
			$where = [ 
					"aEmail" => session ( "user.email" ) 
			];
		}
		return collection ( Infotables::where ( "zxType", $zxType )->where ( $where )->order ( "status,ip desc" )->limit ( $limit )->select () );
	}
	/**
	 * 全局查询
	 *
	 * @param unknown $data        	
	 * @return array
	 */
	protected function querySearch($data) {
		$where = "";
		if (session ( "user.role" ) != "manage") {
			$where = [ 
					"aEmail" => session ( "user.email" ) 
			];
		}
		return collection ( Infotables::where ( $where )->where ( $data ["where"] [0], "like", "%" . $data ["where"] [2] . "%" )->order ( "ip desc" )->select () )->toArray ();
	}
	/**
	 * 基本信息查询
	 *
	 * @param unknown $data        	
	 * @return array
	 */
	private function querySearchBrief($data) {
		if (! isset ( $data ["where"] [2] ) || $data ["where"] [2] == "") {
			return;
		}
		$field = "create_time,instanceId,cName,cAddress,vlan,ip,aPerson,aEmail";
		$result = collection ( Infotables::where ( $data ["where"] [0], "like", "%" . $data ["where"] [2] . "%" )->field ( $field )->order ( "ip desc" )->select () )->toArray ();
		$v = $data;
		$v ["resultLen"] = count ( $result );
		$v ["url"] = request ()->url ( true );
		$this->log ( "基本信息查询", $v );
		if (Cache::get ( 'querySearchBriefTimes' )) {
			Cache::inc ( 'querySearchBriefTimes' );
		} else {
			Cache::set ( 'querySearchBriefTimes', 1, 600 );
		}
		$querySearchBriefTimes = Cache::get ( 'querySearchBriefTimes' );
		if ($querySearchBriefTimes > 10) {
			$address = config ( "manageEmails" );
			foreach ( $address as $k => $v ) {
				$address [$k] = $v . "@ln.chinamobile.com";
			}
			$this->noticeManage ( "[频繁查询]" . session ( "user.name" ) . "-10分钟内：" . $querySearchBriefTimes, null, $address );
			if ($querySearchBriefTimes > 12) {
				session ( null );
				return $this->error ( "已退出登陆！", "index" );
			}
			return $querySearchBriefTimes;
		}
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
	/**
	 * 从query.html删除台账条目
	 *
	 * @param unknown $input
	 */
	protected function queryDelete($input) {
		$result = Infotables::destroy ( $input ["id"] );
		// 同步删除vlantables
		foreach ( $input ["id"] as $id ) {
			Vlantables::destroy ( [
					"infoId" => $id
			] );
		}
		return $result;
	}
	/**
	 * 导出全量台账-基于专线类型
	 *
	 * @param string $zxType        	
	 * @return string[]|array[]
	 */
	protected function queryExport($zxType = "互联网") {
		if ($zxType == "互联网") {
			$colHeader = "申请时间,产品实例标识,带宽,网元厂家,A端基站,客户名称,单位详细地址,客户需求说明(选填),VLAN,IP,联系人姓名(客户侧),联系电话(客户侧),联系人邮箱(客户侧)*,负责人姓名(移动侧)*,负责人电话(移动侧)*,负责人邮箱(移动侧)*,备注,是否ONU带\n(默认为否),单位性质*,单位分类*,行业分类*,使用单位证件类型*,使用单位证件号码*,单位所在省*,单位所在市*,单位所在县*,应用服务类型*";
			$colName = "create_time,instanceId,bandWidth,neFactory,aStation,cName,cAddress,cNeeds,vlan,ip,cPerson,cPhone,cEmail,mPerson,mPhone,mEmail,marks,ifOnu,extra.unitProperty,extra.unitCategory,extra.industryCategory,extra.credential,extra.credentialnum,extra.province,extra.city,extra.county,extra.appServType";
			$field = "";
		} else if ($zxType == "营业厅") {
			$colHeader = "申请时间,产品实例标识,网元厂家,A端基站,客户名称,单位详细地址,VLAN,互联IP,业务IP,联系人姓名(客户侧),联系电话(客户侧)";
			$colName = "create_time,instanceId,neFactory,aStation,cName,cAddress,,vlan,ip,ipB,cPerson,cPhone";
			$field = $colName;
		} else if ($zxType == "卫生网") {
			$colHeader = "申请时间,产品实例标识,网元厂家,A端基站,客户名称,单位详细地址,VLAN,互联IP,业务IP,联系人姓名(客户侧),联系电话(客户侧)";
			$colName = "create_time,instanceId,neFactory,aStation,cName,cAddress,vlan,ip,ipB,cPerson,cPhone";
			$field = $colName;
		} else if ($zxType == "平安校园") {
			$colHeader = "申请时间,产品实例标识,客户名称,单位详细地址,VLAN,监控IP";
			$colName = "create_time,instanceId,cName,cAddress,vlan,ip";
			$field = $colName;
		}
		if (session ( "user.role" ) == "manage") {
			$data = collection ( Infotables::field ( $field )->where ( "zxType", $zxType )->order ( "ip" )->select () )->toArray ();
		} else {
			$data = collection ( Infotables::field ( $field )->where ( "aPerson", session ( "user.name" ) )->order ( "ip" )->select () )->toArray ();
		}
		$v = [ 
				"dataNum" => count ( $data ),
				"zxType" => $zxType,
				"username" => session ( "user.name" ),
				"email" => session ( "user.email" ) 
		];
		$this->log ( "导出全量数据", $v );
		return [ 
				"data" => $data,
				"colHeader" => $colHeader,
				"colName" => $colName 
		];
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
			return $this->error ( "实例标识重复，请重试", null, "该实例标识对应客户名为：<br>" . $info ["cName"] . "<br>申请人：" . $info ["aPerson"] );
		}
	}
	/**
	 * 给管理员发送通知
	 *
	 * @param string $subject        	
	 * @param string $body        	
	 */
	protected function sendManageNotice($subject = '', $body = '') {
		$address = config ( "manageEmails" );
		foreach ( $address as $k => $v ) {
			$address [$k] = $v . "@ln.chinamobile.com";
		}
		$this->sendEmail ( $address, $subject, $body );
	}
}
