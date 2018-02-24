<?php

namespace app\zx_apply\controller;

use think\Controller;
use app\zx_apply\model\Vlantables;
use app\zx_apply\model\Infotables;

class Manage extends Index {
	protected $beforeActionList = [ 
			'checkAuth' 
	];
	protected function checkAuth() {
		if (session ( "user.role" ) != "manage") {
			return $this->error ( "您无权限哦^_^" );
		}
	}
	public function index() {
		return $this->redirect ( "todo" );
	}
	/**
	 * 待办
	 *
	 * @return mixed|string
	 */
	public function todo() {
		if (request ()->isGet ()) {
			$this->assign ( "list", $this->refleshTodoList () );
			return $this->fetch ();
		}
		if (request ()->isPost ()) {
			$req = input ( "get.req" );
			// $input = input ( "post." );
			if ($req == "getDetail") {
				$infotables = new Infotables ();
				$data = $infotables->get ( input ( "post.id" ) ); // 获取器数据
				$detail = $data->getData (); // 原始数据
				$extra = json_decode ( $detail ["extra"], true );
				foreach ( $extra as $k => $v ) {
					$detail [$k] = $v;
				}
				$detail ["ip"] = $data ["ip"]; // 更正ip
				$detail ["ipB"] = $data ["ipB"]; // 更正ipB
				unset ( $detail ["extra"] );
				return json ( $detail ); // 返回单条数据
			}
			if ($req == "distribution") {
				$data = input ( "post." );
				////////////////////////
				////////////////////
				
				$data ["status"] = 1;
				$result = Infotables::updateInfo ( $data );
				if ($result) {
					return $this->success ( "操作成功", null, $this->refleshTodoList () );
				} else {
					return $this->error ( "操作异常。请刷新重试", null, $this->refleshTodoList () );
				}
			}
		}
	}
	protected function refleshTodoList() {
		$where = [ 
				"status" => 0 
		];
		$field = "id,cName,create_time,aPerson,instanceId,zxType,aStation";
		// $info = new Infotables();
		$data = Infotables::where ( $where )->field ( $field )->select (); // explode(",", $field)
		return json_encode ( $data, 256 );
	}
	/**
	 * get:加载数据到handsontable并验证,
	 * post:上传,后台处理入库
	 * 1.默认post为新申请,移除ip、vlan信息再保存,
	 * 严格验证，不合规不许提交，标记status：0
	 * 2.带post参数type=import,视为旧信息导入,
	 * 生成ip表（全）和vlan表信息（不全）。直接入库，并标记tags:导入
	 * 3.为了新增字段不修改数据库，将新增字段用json保存到一列。
	 * 在csv_to_array时，需要获取额外的字段
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
					$data [$k] ["extra"] [$vv] = $v [$vv];
				}
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
	public function import() {
		// 前端根据参数自动获取title并组织好数据显示。
		// 发送_ht_apply给服务器cvs格式，php根据title和csv处理成可以如数据库的array格式。
		$zxInfoTitle = [ 
				"label" => "zx_apply-new-rb",
				"order" => "0,1,2,3,4,5,6,7,8,9,10,12,13,14,15,16,17,18,19,29,30,31,32,33,34,35,36,37" 
		];
		$this->assign ( 'zxInfoTitle', json_encode ( $zxInfoTitle, 256 ) );
		return $this->fetch ();
	}
	/**
	 * 系统设置
	 *
	 * @return mixed|string
	 */
	public function settings() {
		if (request ()->isGet ()) {
			if (! strpos ( request ()->header ( "referer" ), request ()->action () )) {
				session ( "settings_back_url", request ()->header ( "referer" ) );
			}
			return $this->fetch ();
		} else if (request ()->isPost ()) {
			if (input ( "post.exec" ) == "ok_ip") {
			}
			if (input ( "post.exec" ) == "ok_vlan") {
				return Vlantables::importUsedVlan ( input ( "post.device" ), input ( "post.vlanImport" ) );
			}
		}
	}
}