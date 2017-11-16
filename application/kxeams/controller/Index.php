<?php

namespace app\kxeams\controller;

use think\Controller;
use think\Session;
use think\Db;
use think\Request;

class Index extends Controller {
	protected function _initialize() {
		// $this->assign("version" , C::getSysInfo ( "version" ));
		$this->assign ( "version", config ( "version" ) );
	}
	public function index() {
		return $this->redirect ( 'default' );
	}
	public function t() {
		return $this->replace ( "aaa'aa" );
		// return $this->fetch("t",[],Config::get('view_replace_str'));
		// return $this->fetch ( "t" );
	}
	public function login() {
		$info ["txtUser"] = input ( "post.txtUser" );
		$info ["txtPwd"] = input ( "post.txtPwd" );
		$info ["txtUser"] = $this->replace ( $info ["txtUser"] );
		$info ["txtPwd"] = $this->replace ( $info ["txtPwd"] );
		if ($info ['txtUser'] == null || $info ['txtPwd'] == null) {
			return $this->redirect ( 'default' );
		}
		// 首先验证kxeam用户
		$user = Db::query ( "select `name`,`role`,`dept`,`office`,`tel`,`v_no` from asset_user where `loginname`=? and password=?", [ 
				$info ["txtUser"],
				$info ["txtPwd"] 
		] );
		if (null == $user) {
			// 若kxeam验证失败，验证ESserver用户
			$sql = "select UserLogin,UserName from [ESApp1].[dbo].[ES_User] where UserLogin = '" . $info ["txtUser"] . "' and UserPwd = '" . $info ["txtPwd"] . "'";
			$ESuser = $this->query ( $sql );
			if (null == $ESuser) {
				return $this->error ( "账号或密码错误，请重试！", "index", "", 10 );
			} else {
				$user = Db::query ( "select `name`,`role`,`dept`,`office`,`tel`,`v_no` from asset_user where `loginname`=?", [ 
						$ESuser [0] ['UserLogin'] 
				] );
			}
		}
		if ($user != [ ]) {
			$user = $user [0]; 
			// 同步一下密码
			db ( "user" )->where ( 'loginname', $info ["txtUser"] )->setField ( 'password', $info ["txtPwd"] );
			foreach ( $user as $k => $u ) {
				Session::set ( "user." . $k, $u );
			}
			if (strpos ( $user ['role'], "管理员" ) !== false) {
				return $this->success ( "登录成功，" . $user ['name'] . "，正在跳转", "Manage/index", "", 1 );
			} else if (strpos ( $user ['role'], "用户" ) !== false) {
				return $this->success ( "登录成功，" . $user ['name'] . "，正在跳转", "User/index", "", 1 );
			} else {
				// return dump ( $user );
				return $this->error ( "权限获取异常，请联系管理员！", "", "", 10 );
			}
			// return dump ( Session::get ( 'user' ) );
		} else {
			return $this->error ( "您无权登陆维材管理，请联系管理员！", "index", "", 10 );
		}
		
		// 默认 密码 _eams2016 md5: 65c33454e3e3051651cb77fe07634db2
		// 123456 md5: e10adc3949ba59abbe56e057f20f883e
		// $this->success ( "登录成功，正在跳转...", "Manage/index" );
	}
	/**
	 * 执行Sql语句，$encoding 为 true，将执行结果的数组key转换为UTF-8（处理sqlServer表列名为中文时乱码的情况）
	 *
	 * @param unknown $sql        	
	 * @param string $encoding        	
	 * @return mixed|NULL
	 */
	protected function query($sql, $encoding = false) {
		$res = Db::connect ( "db_esserver" )->query ( $sql );
		if ($encoding) {
			$ans = [ ];
			for($i = 0; $i < count ( $res ); $i ++) {
				foreach ( $res [$i] as $k => $v ) {
					$ans [$i] [iconv ( "GB2312", "UTF-8//IGNORE", $k )] = $v;
				}
			}
			$res = null;
			return $ans;
		} else {
			return $res;
		}
	}
	protected function replace($str) {
		return preg_replace ( "/\'/", "", $str );
	}
	public function _empty() {
		$request = Request::instance ();
		$dir = APP_PATH . $request->module () . DS . "view" . DS . $request->controller () . DS . $request->action () . "." . config ( 'template.view_suffix' );
		if (file_exists ( $dir ))
			return $this->fetch ( $request->action () );
		else {
			return $this->error ( "请求未找到，将返回上一页...(kxeams/controller/Index.php->_empty())" );
		}
	}
}
