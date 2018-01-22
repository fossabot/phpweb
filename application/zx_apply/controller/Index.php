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
		$str = " vlan batch 2 to 3 8 to 10 12 to 16 20 100 to 101 103 200 to 201 1023 1235 to 1357 1535 to 1538\r\n vlan batch 1798 2000 to 2073 2075 to 2298 2387 to 2390 2560 to 2576 2723 to 2738 3010 3015 to 3021 3030 3071\r\n vlan batch 3073 to 3076 3079 to 3080 3083 3087 3089 to 3094 3105 to 3106 3120 to 3123 3200 to 3208 3220 3244\r\n vlan batch 3500 to 3502 3514 to 3515 3528 to 3529 3562 to 3563 3576 to 3583 3798 3901 to 3946 3991 to 3992 4000 4004\r\n vlan batch 4080 to 4083 4088 4090 to 4094";
		$ip = "95.100.109.1";
		$long = ip2long ( $ip );
		//$ip = long2ip($long);
		return dump (910011013210);
	}
	
	/**
	 * 首页
	 *
	 * @return mixed|string|void
	 */
	public function index() {
		// 暂未配置登录页面
		return $this->fetch ();
		return $this->redirect ( 'apply' );
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
			$type = input ( "post.type" );
			$zxInfoTitle = input ( "post.zxInfoTitle", null, null );
			$zxInfoTitle = json_decode ( $zxInfoTitle, JSON_UNESCAPED_UNICODE );
			$dataHeader = $this->getHeader ( $zxInfoTitle ["label"], $zxInfoTitle ["order"], true );
			// 获取数据库的列名
			$dataHeader = explode ( ",", $dataHeader );
			// 根据列名和数据转成php数组
			// $postData = substr ( $postData, 3 ); // 莫名奇妙的前三个字节是垃圾数据。3天才研究出来，只能这样解决！！！
			$data = $this->csv_to_array ( $dataHeader, $postData );
			// 获取额外的字段
			$extraHeader = config ( "extraInfo" );
			foreach ( $data as $k => $v ) {
				$temp = [ ];
				foreach ( $extraHeader as $eH ) {
					$temp [$eh] = $data [$k] [$eH];
				}
				$data [$k] ["extra"] = json_encode ( $temp );
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
				"order" => "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18" 
		];
		$this->assign ( 'zxInfoTitle', json_encode ( $zxInfoTitle, 256 ) );
		return $this->fetch ();
	}
	
	/**
	 * 根据label、order 获取表格的 header
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
	 * 专线制作数据合成 script.html
	 */
	// public function script()
	
	/**
	 * 空方法 _empty 直接 fetch 对应的view
	 *
	 * @return mixed|string|void
	 */
	public function _empty() {
		$request = Request::instance ();
		$dir = APP_PATH . $request->module () . DS . "view" . DS . $request->controller () . DS . $request->action () . "." . config ( 'template.view_suffix' );
		if (file_exists ( $dir ))
			return $this->fetch ( $request->action () );
		else {
			return $this->error ( "请求未找到，将返回上一页...<b>(zx_apply/controller/index.php->_empty())</b>" );
		}
	}
}
