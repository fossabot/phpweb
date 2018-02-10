<?php

namespace app\zx_apply\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\zx_apply\model\Infotables;
use app\zx_apply\model\Vlantables;

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
		return $this->noticeAdmin("哈哈???","我是测试啊啊");
		return dump ( Vlantables::generateVlan ( "XF-10", "ttttest" ) );
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
				"msg" => strip_tags($msg) 
		] );
	}
	
	/**
	 * 数据专线申请开通
	 */
	public function apply() {
		/*
		 * $zxInfoTitle = [
		 * "label" => "zx_apply-new-rb",
		 * "order" => "1,2,3,4,5,6,7,8,12,13,14,15,16,17,18,20,21,30,31,32,33,34,35,36,37,38"
		 * ];
		 * $this->assign ( 'zxInfoTitle', json_encode ( $zxInfoTitle, 256 ) );
		 */
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
		return $this->fetch ();
	}
	/**
	 * 更新信息
	 */
	public function update() {
		return $this->fetch ( "index/update" );
	}
	
}
