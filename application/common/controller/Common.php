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
	
	
	function zz (){
		
		return dump($this->csv_to_array(["a","b","c"],"1,2,3"));
	}
	
	/**
	 * csv 转 php数组
	 *
	 * @param array $header        	
	 * @param string $csvstr        	
	 * @return array
	 */
	public static function csv_to_array($header = [], $csvstr = '') {
		if(strpos($csvstr,"\r\n")!==false){
			$data_ora = explode ( "\r\n", $csvstr ); // 原始数据（多条）
		}else{
			$data_ora = explode ( "\n", $csvstr );
		}
		$data_arr = [ ]; // 初始化数据数组
		$result = [ ]; // 初始化结果数据
		for($i = 0; $i < count ( $data_ora ); $i ++) { // 将多条原始数据分别分割为数组
			$data_arr [] = explode ( ",", $data_ora [$i] );
		}
		foreach ( $data_arr as $val ) {
			$result [] = array_combine ( $header, $val );
			// $data_ColNames 作为键名, $val 作为键值,组成新的数组(我们要的对象), 放进 $result 。
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
			return "传值为空,需要table和column参数";
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
