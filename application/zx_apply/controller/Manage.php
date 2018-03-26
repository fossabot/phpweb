<?php

namespace app\zx_apply\controller;

use think\Controller;
use app\zx_apply\model\Vlantables;
use app\zx_apply\model\Infotables;
use app\zx_apply\model\Iptables;
use Overtrue\Pinyin\Pinyin;

class Manage extends Index {
	protected $beforeActionList = [ 
			'checkAuth' 
	];
	protected function checkAuth() {
		if (session ( "user.role" ) != "manage") {
			return $this->redirect ( "index/query" );
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
			// $infotables = new Infotables ();
			$info = Infotables::get ( input ( "post.id" ) ); // 获取器数据
			$detail = $info->getData (); // 原始数据
			$extra = json_decode ( $detail ["extra"], true );
			foreach ( $extra as $k => $v ) {
				$detail [$k] = $v;
			}
			$detail ["ip"] = $info ["ip"]; // 更正ip
			$detail ["ipB"] = $info ["ipB"]; // 更正ipB
			unset ( $detail ["extra"] );
			if ($req == "getDetail") {
				return json ( $detail ); // 返回单条数据
			}
			if ($req == "distribution") {
				$data = input ( "post." );
				$this->checkInstanceID ( $info, $data );
				$data = $this->checkAndSetIp ( $info, $data );
				$this->checkVlan ( $data );
				// $data ["status"] = 1;
				// return dump ( $data );
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
	 * 信息查询
	 */
	public function query() {
		if (request ()->isGet ()) {
			// 访问
			$aStation = array_keys ( config ( "aStation" ) );
			$zxTitle = [ 
					"label" => "zx_apply-new-rb",
					"order" => "1,2,3,4,5,6,9,10,11,18,19,22,23,24,29,30,31,32,33,34,35,36,37" 
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
			input ( "post.r" ) == "detail" && $data = Infotables::get ( input ( "post.id" ) )->toJson ();
			input ( "post.r" ) == "search" && $data = collection ( Infotables::where ( input ( "post.where/a" ) [0], "like", "%" . input ( "post.where/a" ) [2] . "%" )->order ( "id desc" )->select () )->toArray ();
			return $data;
		}
		if (request ()->isPut ()) {
			// 相关操作
			input ( "post.r" ) == "script" && $data = $this->generateScript ( input ( "post.id/a" ) [0] );
			input ( "post.r" ) == "zgWorkflow" && $data = $this->generateZgWorkflow ( input ( "post.id/a" ) );
			input ( "post.r" ) == "jtIp" && $data = $this->generateJtIp ( input ( "post.id/a" ) );
			input ( "post.r" ) == "bxbIp" && $data = $this->generateGxbIp ( input ( "post.id/a" ) );
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
		return collection ( Infotables::order ( "id" )->limit ( $limit )->select () );
	}
	private function generateScript($id = null) {
		$data = Infotables::get ( $id );
		if ($data ["zxType"] == "互联网") {
			return $this->generateScriptNet ( $data );
		}
		if ($data ["zxType"] == "卫生网") {
			return $this->generateScriptWsw ( $data );
		}
	}
	/**
	 * 数据制作脚本生成-互联网
	 *
	 * @param unknown $data        	
	 * @return string|NULL[]|string[]
	 */
	private function generateScriptNet($data) {
		$data ["domain"] = 'tlyd-rb'; // 1. domain
		$aStation = config ( "aStation" );
		$data ["sw93"] = $aStation [$data ["aStation"]]; // 2. 9312名
		$pinyin = new Pinyin ();
		$desc = substr ( $data ["sw93"], 0, stripos ( $data ["sw93"], "-" ) + 1 );
		$desc = str_replace ( "CHJ", "TL", $desc );
		$_desc = $pinyin->convert ( preg_replace ( "/[^\x{4e00}-\x{9fa5}A-Za-z-]/u", "", $data ["cName"] ) );
		foreach ( $_desc as $v ) {
			$desc .= ucfirst ( $v );
		}
		$data ["desc"] = $desc; // 3. 描述
		$device9312 = json_decode ( config ( "device9312" ), true ) [$data ["sw93"]];
		function bas($bas, $device9312, $data) {
			$trunk = $device9312 ['bas01_down_port'];
			$rbp = $device9312 ['rbp_name'];
			$_bas_name = [ 
					"01" => "01-CHJ",
					"02" => "02-YZL" 
			];
			if (strlen ( $data ["domain"] ) > 4) { // tlyd-rb
				$rbp = "\n remote-backup-profile " . $rbp;
			} else {
				$rbp = null;
			}
			$script = "interface " . $trunk . "." . $data ["vlan"];
			$script .= "\ndis th\n ";
			$script .= "\n description [LNTIL-MA-CMNET-BAS" . $_bas_name [$bas] . "ME60X]" . $trunk . "." . $data ["vlan"] . "-[" . $data ["desc"] . "]";
			$script .= "\n user-vlan " . $data ["vlan"] . $rbp;
			$script .= "\n bas\n #\n access-type layer2-subscriber default-domain authentication " . $data ["domain"];
			$script .= "\n authentication-method bind\n";
			$script .= "static-user " . $data ["ip"] . " " . $data ["ip"] . " gateway " . substr ( $data ["ip"], 0, strripos ( $data ["ip"], "." ) + 1 ) . "1 " . "interface " . $trunk . "." . $data ["vlan"] . " vlan " . $data ["vlan"] . " domain-name " . $data ["domain"] . " detect\r\n";
			return $script;
		}
		function the93($device9312, $data) {
			if ($data ["neFactory"]) {
				$data ["neFactory"] === '华为' && $down = "port_hw";
				$data ["neFactory"] === '中兴' && $down = "port_zte";
			} else {
				return "网元厂家未定义";
			}
			$script = "vlan " . $data ["vlan"] . "\ndis th\n \n";
			$script .= "description to-[" . $data ["desc"] . "]\nq";
			$script .= "\ninterface " . $device9312 ["up_port_yz"]; // 上行银州 bas02
			$script .= "\nport trunk allow-pass vlan " . $data ["vlan"];
			if (strlen ( $data ["domain"] ) > 4) { // 上行柴河 bas01
				$script .= "\ninterface " . $device9312 ["up_port_ch"] . "\nport trunk allow-pass vlan " . $data ["vlan"];
			}
			$script .= "\ninterface " . $device9312 [$down]; // 下行
			$script .= "\nport trunk allow-pass vlan " . $data ["vlan"];
			$script .= "\nq\r\n";
			return $script;
		}
		$result = [ 
				"bas01" => bas ( "01", $device9312, $data ),
				"bas02" => bas ( "02", $device9312, $data ),
				"the93" => [ 
						$data ["sw93"],
						the93 ( $device9312, $data ) 
				] 
		];
		return $result;
	}
	/**
	 * 数据制作脚本生成-卫生网
	 *
	 * @param unknown $data        	
	 * @return string|NULL[]|string[]
	 */
	private function generateScriptWsw($data) {
		$aStation = config ( "aStation" );
		$data ["sw93"] = $aStation [$data ["aStation"]]; // 2. 9312名
		$pinyin = new Pinyin ();
		$desc = substr ( $data ["sw93"], 0, stripos ( $data ["sw93"], "-" ) + 1 ); // 3. 描述
		$desc = str_replace ( "CHJ", "TL", $desc );
		$_desc = $pinyin->convert ( preg_replace ( "/[^\x{4e00}-\x{9fa5}A-Za-z-]/u", "", $data ["cName"] ) );
		foreach ( $_desc as $v ) {
			$desc .= ucfirst ( $v );
		}
		$device9312 = json_decode ( config ( "device9312" ), true ) [$data ["sw93"]];
		$trunk = $device9312 ['bas02_down_port'];
		$ip = Iptables::ip_parse ( $data ["ip"] );
		$ipB = Iptables::ip_parse ( $data ["ipB"] );
		$script = "interface " . $trunk . "." . $data ["vlan"] . "\ndis th\n";
		$script .= "\n vlan-type dot1q " . $data ["vlan"];
		$script .= "\n description [LNTIL-MA-CMNET-BAS02-YZLME60X]" . $trunk . "." . $data ["vlan"] . "-[" . $desc . "]";
		$script .= "\n ip binding vpn-instance tlwsw";
		$script .= "\n ip address " . long2ip ( $ipB [2] + 1 ) . " " . long2ip ( $ipB [1] );
		$script .= "\n traffic-policy remarkdscp inbound";
		$script .= "\n statistic enable";
		$script .= "\nip route-static vpn-instance tlwsw " . long2ip ( $ip [2] ) . " " . long2ip ( $ip [1] ) . " " . long2ip ( $ipB [2] + 2 ) . "\r\n";
		/* the9312 */
		if ($data ["neFactory"]) {
			$data ["neFactory"] === '华为' && $down = "port_hw";
			$data ["neFactory"] === '中兴' && $down = "port_zte";
		} else {
			return [ 
					"the93" => [ 
							"网元厂家未定义",
							"" 
					] 
			];
		}
		$the9312 = "vlan " . $data ["vlan"] . "\ndis th\n\n";
		$the9312 .= "description to-[" . $desc . "]\nq";
		$the9312 .= "\ninterface " . $device9312 ["up_port_yz"] . "\nport trunk allow-pass vlan " . $data ["vlan"];
		$the9312 .= "\ninterface " . $device9312 [$down] . "\nport trunk allow-pass vlan " . $data ["vlan"] . "\nq\r\n";
		return [ 
				"bas02" => $script,
				"the93" => [ 
						$data ["sw93"],
						$the9312 
				] 
		];
	}
	private function generateZgWorkflow($id = null) {
		$data = Infotables::get ( $id );
		$script = 1;
		return $data;
	}
	public function tt() {
		function browser_export($filename, $type = "Excel5") {
			if ($type == "Excel5") {
				header ( 'Content-Type: application/vnd.ms-excel' ); // 告诉浏览器将要输出excel03文件
			} else {
				header ( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ); // 告诉浏览器数据excel07文件
			}
			header ( 'Content-Disposition: attachment;filename="' . $filename . '"' ); // 告诉浏览器将输出文件的名称
			header ( 'Cache-Control: max-age=0' ); // 禁止缓存
		}
		$pFilename= './sampleData/ip_jt.xlsx';
		//$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load ( $pFilename);
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		$spreadsheet = $reader->load($pFilename);
		//return dump($spreadsheet->getProperties());
		$xlsx = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx ( $spreadsheet );
		//$spreadsheet->getProperties()->setCreator("Xianda");
		//$spreadsheet->getProperties()->setLastModifiedBy("Xianda");
		//$spreadsheet->getProperties()->setTitle("Import template");
		//$spreadsheet->getProperties()->setSubject("");
		//$spreadsheet->getProperties()->setDescription("document for Office 2007 XLSX, generated by Xianda's scripts.");
		//$spreadsheet->getProperties()->setKeywords("ip infomations");
		//$spreadsheet->getProperties()->setCategory("Test result file");
		browser_export ( 'export_excel.xls' ); // 输出到浏览器
		$xlsx->save ( "php://output" );
		$spreadsheet->disconnectWorksheets ();
		unset ( $spreadsheet );
		
		return dump ( Infotables::get ( 2 )->toArray () );
	}
	private function generateJtIp($id = null) {
		$data = Infotables::get ( $id );
		$script = 1;
		return $data;
	}
	private function generateGxbIp($id = null) {
		$data = Infotables::get ( $id );
		$script = 1;
		return $data;
	}
	public function _getDevice9312Info() {
		return config ( "device9312" );
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
		$zxInfoTitle = [ 
				"label" => "zx_apply-new-rb",
				"order" => "0,1,2,3,4,5,6,7,8,9,10,12,13,14,15,16,17,18,19,29,30,31,32,33,34,35,36,37" 
		];
		if (request ()->isPost ()) {
			$postData = input ( "post.data" );
			$type = input ( "post.type" );
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
					unset ( $data [$k] [$vv] );
				}
				if ($v ["aStation"] == "柴河局") {
					$data [$k] ["aStation"] .= "-" . $data [$k] ["neFactory"];
				}
				if ($this->checkDateIsValid ( $data [$k] ["aDate"] )) {
					// 申请时间转存到 create_time
					$data [$k] ["create_time"] = strtotime ( $data [$k] ["aDate"] );
				}
			}
			// 若导入，ip/vlan信息要单独存储。
			$result = Infotables::createInfo ( $data, $type );
			// $result = $info->save($data[0]);
			return dump ( $result );
		}
		if (request ()->isGet ()) {
			if (input ( '?get.t' )) {
				$aStation = array_keys ( config ( "aStation" ) );
				$this->assign ( [ 
						"aStation" => implode ( ",", $aStation ),
						'colHeaderData' => $this->getHeader ( $zxInfoTitle ["label"], $zxInfoTitle ["order"] ),
						"colWidthsData" => $this->getColWidths ( $zxInfoTitle ["order"] ) 
				] );
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
	/**
	 * 检查获取的数据与数据库中的ip/ipB是否重复
	 *
	 * @param unknown $info        	
	 * @param unknown $data        	
	 */
	protected function checkAndSetIp($info, $data) {
		if ($info ["ip"] != $data ["ip"]) { // 获取的ip有变化，则检查是否冲突
			$ip = Iptables::check ( $data ["zxType"], $data ["ip"] );
			if ($ip)
				return $this->error ( "互联ip冲突，", null, $ip ["cName"] );
		}
		// 设置ipMask
		$ip_array = Iptables::ip_parse ( $data ["ip"] );
		$data ["ip"] = $ip_array [2];
		$data ["ipMask"] = $ip_array [1];
		if ($data ["ipB"] == "") {
			// 设置 ipB为null
			$data ["ipB"] = null;
			$data ["ipBMask"] = null;
			return $data;
		}
		if ($info ["ipB"] != $data ["ipB"]) {
			$ipB = Iptables::check ( $data ["zxType"], $data ["ipB"], "ipB" );
			if ($ipB)
				return $this->error ( "业务ip冲突，", null, $ipB ["cName"] );
		}
		// 设置ipBMask
		$ipB_array = Iptables::ip_parse ( $data ["ipB"] );
		/* 若为提供ipBMask，默认强制设置ipBMask为-8 */
		$ipB_array [1] == - 1 && $ipB_array = Iptables::ip_parse ( Iptables::ip_export ( $ipB_array [0], - 8 ) );
		/* 修正ip为ip_start */
		$data ["ipB"] = $ipB_array [2];
		$data ["ipBMask"] = $ipB_array [1];
		return $data;
	}
	/**
	 * 检查获取的vlan是否已分配
	 *
	 * @param unknown $data        	
	 */
	protected function checkVlan($data) {
		$vlan = Vlantables::check ( $data ["zxType"], $data ["aStation"], $data ["vlan"] );
		if ($vlan && $vlan ["id"] != $data ["id"]) { // 找到vlan且vlan的id与自己的id不同
			return $this->error ( "vlan冲突，", null, $vlan ["cName"] );
		}
	}
	/**
	 * 校验日期格式是否正确
	 *
	 * @param string $date
	 *        	日期
	 * @param string $formats
	 *        	需要检验的格式数组
	 * @return boolean
	 */
	protected function checkDateIsValid($date, $formats = array("Y-m-d", "Y/m/d")) {
		$unixTime = strtotime ( $date );
		if (! $unixTime) { // strtotime转换不对，日期格式显然不对。
			return false;
		}
		// 校验日期的有效性，只要满足其中一个格式就OK
		foreach ( $formats as $format ) {
			if (date ( $format, $unixTime ) == $date) {
				return true;
			}
		}
		
		return false;
	}
	private function cacheSettings() {
		$client = new \Redis ();
		$client->connect ( '127.0.0.1', 6379 );
		$pool = new \Cache\Adapter\Redis\RedisCachePool ( $client );
		$simpleCache = new \Cache\Bridge\SimpleCache\SimpleCacheBridge ( $pool );
		\PhpOffice\PhpSpreadsheet\Settings::setCache ( $simpleCache );
	}
}