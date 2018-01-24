<?php

namespace app\common\controller;

use think\Controller;
use think\Request;
use think\Config;
use think\Db;

class Common extends Controller {
	
	/**
	 * 判断是否已登录--(初始化函数 _initialize 优先于 $beforeActionList 配置)
	 */
	public function _initialize() {
		$request = Request::instance ();
		if ($request->controller () == 'common') {
			return $this->error ( "非法访问！你很6啊。然而我会带你回去。" );
		}
		// url : index.php/【MODULE】/【CONTROLLER】/【ACTION】.html
		$permitModule = [ 
				"esserver" 
		];
		$permitController = [ 
				"Tool" 
		];
		$permitActions = [ 
				"index",
				"main",
				"getVcode" 
		];
		// 判断是否从localhost 访问，若 url 不允许 未登录访问，则跳转
		if ((! substr ( $request->domain (), - 9 ) == "localhost") && (! in_array ( $request->module (), $permitModule )) && (! in_array ( $request->controller (), $permitController )) && (! in_array ( $request->action (), $permitActions )) && (! input ( 'session.user/a' ))) {
			// $this->assign ( "version", $request->controller () );
			// $this->assign ( "version", "登陆超时" );
			return $this->error ( '您未登录或登录超时，请先登录！', 'index/index' );
		} else {
			$this->assign ( "version", config ( "version" ) );
		}
	}
	public function index() {
		return "当前执行的是：common/index";
	}
	/**
	 * php 数组 转成 Grid 组件需要的 json 格式
	 *
	 * @param array $array        	
	 * @param string $header        	
	 * @return string
	 */
	public static function array_to_json($array = [], $header = '') {
		$data_arr = [ ];
		if ($header == '') {
			foreach ( $array as $val ) {
				$id = null;
				$theData = [ ];
				foreach ( $val as $k => $v ) {
					if ($id == null || $k == 'id')
						// 让 id 为 第一个值，或者为id值
						$id = $v;
					$theData [] = $v;
				}
				$data_arr [] = [ 
						"id" => $theData [0],
						"data" => $theData 
				];
			}
		} else {
		}
		return json_encode ( [ 
				"rows" => $data_arr 
		], JSON_UNESCAPED_UNICODE );
	}
	/**
	 * php 数组根据 header 转成 csv 字符串
	 *
	 * @param array $array        	
	 * @param string $header        	
	 * @return string
	 */
	public static function array_to_csv($array = [], $header = '') {
		$csvstr = '';
		if ($header == '') {
			foreach ( $array as $val ) {
				$i = 1;
				foreach ( $val as $v ) {
					if ($i < count ( $val ))
						$csvstr .= $v . ',';
					else
						$csvstr .= $v . "\n";
					$i ++;
				}
			}
		} else {
			$headers = explode ( ",", $header );
			
			foreach ( $array as $val ) {
				for($i = 0; $i < count ( $headers ); $i ++) {
					if ($i < count ( $headers ))
						$csvstr .= $val [$headers [$i]] . ',';
					else
						$csvstr .= $val [$headers [$i]] . "\n";
				}
			}
		}
		$csvstr = substr ( $csvstr, 0, strlen ( $csvstr ) - 1 );
		return $csvstr;
	}
	function zz() {
		return dump ( $this->csv_to_array ( [ 
				"a",
				"b",
				"c" 
		], "1,2,3" ) );
	}
	
	/**
	 * csv 转 php数组
	 *
	 * @param array $header        	
	 * @param string $csvstr        	
	 * @return array
	 */
	public static function csv_to_array($header = [], $csvstr = '') {
		trim ( $csvstr );
		$data = explode ( "\n", $csvstr );
		$result = [ ]; // 初始化结果数据
		for($i = 0; $i < count ( $data ); $i ++) { // 将多条原始数据分别分割为数组
			$result [] = array_combine ( $header, explode ( ",", trim ( $data [$i] ) ) );
		}
		return $result;
	}
	/**
	 * 根据列名、表名查询非重复数据。
	 *
	 * @param string $column        	
	 * @param string $table        	
	 */
	public static function get_combo_options($table = '', $field = [], $where = [], $distinct = true, $order = "id") {
		$table = input ( "param.table" );
		$field = input ( "param.field/a" );
		$where = input ( "param.where/a" );
		$distinct = input ( "?param.distinct" ) ? input ( "param.distinct/b" ) : true;
		$order = input ( "param.order" );
		if ($table == '')
			return "传值为空";
		foreach ( $field as $k => $f ) {
			$field_arr [] = $f . " as " . $k;
		}
		$field_str = implode ( ",", $field_arr );
		$result = Db::name ( $table )->distinct ( $distinct )->where ( $where )->field ( $field_str )->order ( $order )->select ();
		return $result; // 自动处理成 json()
			                // return json_encode($result, 256);//Content-Type:text/html
			                // return json ( $result ); // 返回 Content-Type : application/json
	}
	public static function get_combo_options2($column = '', $table = '') {
		$table = input ( "param.table" );
		$column = input ( "param.column" );
		if ($table == '' || $column == '')
			return "传值为空,需要_table和_column参数";
		$result = Db::name ( $table )->distinct ( true )->field ( $column . " as value" )->select ();
		for($i = 0; $i < count ( $result ); $i ++) {
			$result [$i] ['text'] = $result [$i] ['value'];
		}
		$result = [ 
				'options' => $result 
		];
		return $result;
	}
	/**
	 * 获取邮件验证码
	 *
	 * @param string $e        	
	 * @param number $ttl        	
	 */
	public function getVcode($e = '', $ttl = '120') {
		if (preg_match ( '/[^A-Za-z._]+/', $e )) {
			return $this->error ( '非法邮箱地址哦' );
		} else {
			$e = strtolower ( $e );
		}
		
		// 限制系统每小时最多发送6封邮件。
		$data = Db::table ( "phpweb_check" )->whereTime ( 'time', '-2 hours' )->select ();
		if (count ( $data ) > 12) {
			return $this->success ( "单位时间发送邮件数超限。请根据页面下方与管理员联系。" );
		}
		// 检查$ttl分钟内是否已申请
		// $data = Db::table ( "phpweb_check" )->whereTime ( 'time', '-31 min' )->where ( "loginName", $e )->order ( "time desc" )->find ();
		$vcode = rand ( 0, 9999 );
		$codes = [ ];
		foreach ( $data as $v ) {
			if ($v ['loginName'] == $e) {
				if (time () - strtotime ( $v ['time'] ) < $ttl * 60) {
					return $this->success ( "距离上一次申请间隔小于" . $ttl . "分钟，请勿重复操作。" );
				}
			}
			$codes [] = $v ['code'];
		}
		// 检查是否与生效中的其他用户的相同
		while ( in_array ( $vcode, $codes ) ) {
			$vcode = rand ( 0, 9999 );
		}
		// 存入数据库
		$insertData = [ 
				'code' => $vcode,
				'loginName' => $e 
		];
		Db::table ( "phpweb_check" )->insert ( $insertData );
		
		$address = $e;
		$subject = '【ESWeb】您的登录验证码为：' . sprintf ( "%04s", $vcode ) . '，请在30分钟内使用。';
		$body = '<p>您申请了邮箱登录的验证码，若非本人操作，请忽略本邮件</p><hr><br>
				<p style="text-align:right;">Powered by <a href="https://github.com/yuxianda/")">Xianda</a></p>';
		// $sendEmail = $this->sendEmail ( $address, $subject, $body );
		$sendEmail = true; // 测试用例
		if (is_bool ( $sendEmail )) {
			$msg = "验证码已通过邮件发送，请到邮箱内查收主题包含<b>【ESWeb】</b>的邮件。";
			return $this->success ( $msg, null, 2 * $vcode );
		} else {
			Db::table ( "phpweb_check" )->where ( 'code', $vcode )->delete ();
			return $this->error ( '邮件发送未成功：' . $sendEmail );
		}
	}
	/**
	 * 获取参数
	 *
	 * @param string $table        	
	 * @param array $where        	
	 * @return string 参数值
	 */
	public static function getSysInfo($label = '') {
		return Db::name ( "sysinfo" )->where ( "label", $label )->value ( "value" );
	}
	public function bugReport() {
		if (Request::instance ()->isGet ()) {
			return $this->display ( "<h3>??????</h3>" );
		} else {
			$data = input ( "post." );
			// return dump($data);
			return Db::name ( 'bugreport' )->insert ( $data );
			// return $this->success("");
		}
	}
	public function _empty() {
		$request = Request::instance ();
		$dir = APP_PATH . $request->module () . DS . "view" . DS . $request->controller () . DS . $request->action () . "." . config ( 'template.view_suffix' );
		if (file_exists ( $dir ))
			return $this->fetch ( $request->action () );
		else {
			return $this->error ( "请求未找到，将返回上一页...(common/controller/Common.php->_empty())" );
		}
	}
}
