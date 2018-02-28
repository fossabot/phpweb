<?php

namespace app\zx_apply\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\zx_apply\model\Infotables;
use app\zx_apply\model\Iptables;

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
		$infotables = new Infotables();
		//return dump($infotables->get(3)->getData());
		$ip = Iptables::ip_parse ( "10.10.10.28" );
		return dump($ip[1]);
		$ip [1] == - 1 && $ip = Iptables::ip_parse ( Iptables::ip_export ( $ip [0], - 8 ) );
		$ip = Iptables::check( "互联网","10.2.2.21" );
		return dump ( $ip );
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
			$extraHeader = config ( "extraInfo" );
			foreach ( $extraHeader as $k => $v ) {
				$data ["extra"] [$v] = $data [$v];
				unset ( $data [$v] );
			}
			$result = Infotables::createInfo ( $data, "apply" );
			return $this->result ( null, $result );
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
