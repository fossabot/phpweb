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
			$this->checkInstanceID ( null, $data ); // 检查instanceId
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
		return $this->fetch ();
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
			return $this->error ( "实例标识重复，请重试", null, "该实例标识对应客户名为：<br>" . $info ["cName"] );
		}
	}
}
