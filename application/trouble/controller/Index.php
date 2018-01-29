<?php

namespace app\trouble\controller;

use app\trouble\controller\Common;
use think\Db;
use app\trouble\model\Forms;
use think\Validate;
use think\Session;

class Index extends Common {
	public function t() {
		return base64_decode ( input ( "t" ) );
	}
	/**
	 * 登陆
	 *
	 * @return mixed|string
	 */
	public function index() {
		if (input ( "?param.u" ) && input ( "?param.p" )) {
			return $this->loginIn ( input ( "param.u" ), input ( "param.p" ), input ( "param.e/d" ), input ( "param.m/d" ) );
		} else {
			if (request ()->isMobile ()) {
				return $this->redirect ( "main" );
			} else {
				return $this->fetch ( "main_pc" );
			}
		}
	}
	private function loginIn($userLogin = null, $userPwd = null, $encrypt, $email = false) {
		if (preg_match ( "/[^A-Za-z0-9=]+/", $userLogin )) {
			return $this->error ( "参数格式错误" );
		}
		// 获取用户名
		$u = base64_decode ( $userLogin );
		if (! Validate::max ( $u, 20 )) {
			return $this->error ( "输入参数异常：Too many..." );
		}
		
		// 根据登录方式登录
		if ($email) {
			return $this->loginEmail ( $u, base64_decode ( $userPwd ) );
		} else {
			// 根据密码格式获取密码
			if ($encrypt) {
				// $encrypt大于 1 表示账号登录提交的base64_decode『基于window.btoa的 utf8_to_b64()』。
				// $encrypt等于 1 表示明文提交（测试用）
				// $encrypt等于 false 表示提交的MD5(书签访问)
				$userPwd = $encrypt > 1 ? base64_decode ( $userPwd ) : $this->pwd ( $userPwd );
			}
			return $this->loginES ( $u, $userPwd );
		}
	}
	private function pwd($p = '') {
		return strtoupper ( md5 ( $p ) );
	}
	private function loginEmail($email, $vcode) {
		$info = Db::name ( "check" )->where ( "loginName", $email )->order ( "time desc" )->limit ( 1 )->find ();
		if ($info) {
			if (sprintf ( "%04s", $info ['code'] ) == $vcode) {
				if (time () - strtotime ( $info ['time'] ) < 1800) { // 30分钟之内
					$user = $this->getLocalinfo ( null, $email );
					foreach ( $user as $k => $u ) {
						Session::set ( "user." . $k, $u );
					}
					return $this->success ( "登录成功", "view", null, 1 );
				} else {
					return $this->error ( "验证码已过期，请重新获取" );
				}
			} else {
				return $this->error ( "验证码错误" );
			}
		} else {
			return $this->error ( "请先获取验证码" );
		}
	}
	private function loginES($u, $userPwd) {
		$field = 'UserLogin,UserName,UserPwd,Email,MobilePhone';
		$user = Db::connect ( config ( "appDatabase" ) )->name ( "User" )->field ( $field )->where ( "UserLogin", $u )->find ();
		// $sql = "select * from [ESApp1].[dbo].[ES_User] where UserLogin = ?";
		// $user = Db::connect ( config ( "appDatabase" ) )->query ( $sql, [ $u ] );
		if (empty ( $user )) {
			return $this->error ( '用户[' . $u . ']不存在<br>请重试' );
		}
		if ($user ['UserPwd'] == $userPwd) {
			$user = $this->getLocalinfo ( $user );
			foreach ( $user as $k => $u ) {
				if ($k != 'UserPwd') {
					Session::set ( "user." . $k, $u );
				}
			}
			if (! request ()->isMobile () && ! request ()->isAjax ()) {
				return $this->display ( "<h1>登陆成功，<a href='view.html'>跳转</a></h1>" );
			}
			return $this->success ( "登录成功", "view", null, 1 );
		} else {
			return $this->error ( "密码错误" );
		}
	}
	private function getLocalinfo($u, $e = null) {
		$field = 'UserName,UserDept,UserDept2,UserRole';
		if ($u) {
			$where = [ 
					"UserLogin" => $u ['UserLogin'] 
			];
		} else {
			$u = [ 
					'Email' => $e 
			];
			$where = $u;
		}
		$info = Db::name ( "user" )->field ( $field )->where ( $where )->find ();
		if ($info) {
			foreach ( $info as $k => $i ) {
				$u [$k] = $i;
			}
		}
		return $u;
	}
	protected function sendEmail($address = '', $subject = '', $body = '', $url = "") {
		$mail = new \PHPMailer ();
		$mail->isSMTP (); // Set mailer to use SMTP
		$mail->CharSet = "utf-8";
		$mail->SetLanguage ( 'zh_cn' );
		// $mail->SMTPDebug = 1;
		$mail->Host = 'smtp.139.com'; // Specify main and backup SMTP servers
		$mail->SMTPAuth = true; // Enable SMTP authentication
		$mail->Username = "tl_excelserver@139.com"; // SMTP username
		$mail->Password = 'HUYUE6868816'; // SMTP password
		                                  // $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 25; // TCP port to connect to
		$mail->setFrom ( 'tl_excelserver@139.com', '终端故障报修' );
		$mail->addAddress ( $address ); // Name is optional
		                                // $mail->addReplyTo ( 'info@example.com', 'Information' );
		                                // $mail->addCC ( 'cc@example.com' );
		                                // $mail->addBCC ( 'bcc@example.com' );
		                                // $mail->addAttachment ( '/var/tmp/file.tar.gz' ); // Add attachments
		                                // $mail->addAttachment ( '/aa.jpg', '附件new.jpg' ); // Optional name
		                                // 绝对路径从磁盘根目录算起，相对路径从public/idnex.php算起。
		$mail->isHTML ( true ); // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->AltBody = '您的邮件客户端不支持查看HTML格式的邮件正文。请换个方式查看此邮件';
		
		if (! $mail->send ()) {
			return $mail->ErrorInfo;
		} else {
			return true;
		}
	}
	/**
	 * 获取邮件验证码
	 *
	 * @param string $e        	
	 * @return void|string
	 */
	public function getVcode($e = '', $ttl = 120) {
		if (preg_match ( '/[^A-Za-z._]+/', $e )) {
			return $this->error ( '非法邮箱地址哦' );
		} else {
			$e = strtolower ( $e );
		}
		// 检查30分钟内是否已申请
		$data = Db::name ( "check" )->whereTime ( 'time', '-31 min' )->where ( "loginName", $e )->order ( "time desc" )->find ();
		if ($data) {
			// if (time () - strtotime ( $data ['time'] ) < 1800) {
			return $this->success ( "距离上一次申请间隔小于30分钟，请勿重复操作。" );
			// }
		} else {
			// 检查生成的验证码是否与生效中的其他用户的相同
			$data = Db::name ( "check" )->field ( "code" )->whereTime ( 'time', '-31 min' )->select ();
			$codes = [ ];
			foreach ( $data as $d ) {
				$codes [] .= $d ['code'];
			}
			$vcode = rand ( 0, 9999 );
			while ( in_array ( $vcode, $codes ) ) {
				$vcode = rand ( 0, 9999 );
			}
			// 存入数据库
			$insertData = [ 
					'code' => $vcode,
					'loginName' => $e 
			];
			Db::name ( "check" )->insert ( $insertData );
		}
		$address = $e . '@ln.chinamobile.com';
		$subject = '-终端故障报修-您的登录验证码为：【' . sprintf ( "%04s", $vcode ) . '】请在30分钟内使用。';
		$body = '<p>您在使用铁岭移动终端报修系统时申请了邮箱登录的验证码，若本次非本人操作，请忽略本邮件</p><hr><br>
				<p style="text-align:right;">Powered by <a href="")">Xianda</a></p>';
		// $sendEmail = $this->sendEmail ( $address, $subject, $body );
		$sendEmail = true;
		if (is_bool ( $sendEmail )) {
			$msg = "验证码已通过邮件发送，请到邮箱内查收来自<b>tl_excelserver@139.com</b>的邮件。";
			return $this->success ( $msg, null, 2 * $vcode );
		} else {
			Db::name ( "check" )->where ( 'code', $vcode )->delete ();
			return $this->error ( '邮件发送未成功：' . $sendEmail );
		}
	}
	/**
	 * 首页 查看所有历史申请单
	 *
	 * @param number $page        	
	 * @return mixed|string
	 */
	public function view() {
		return $this->fetch ();
	}
	public function getView($page = 1, $listRows = 3) {
		$field = "id,applicant,createTime,troubleDescrition,status";
		$data = Db::name ( "forms" )->field ( $field )->order ( 'createTime desc' )->page ( $page, $listRows )->select ();
		$data = Forms::beautify ( $data );
		return json ( $data );
	}
	public function detail($id = 0) {
		$data = Db::name ( "forms" )->find ( $id );
		$data = Forms::beautify ( $data, true );
		$this->assign ( "data", $data );
		return $this->fetch ();
	}
	public function more($id = 0) {
		return $this->detail ( $id );
	}
	public function sendList() {
		$list = db ( "user" )->field ( [ 
				'concat(UserName," ",MobilePhone)' => "UserName" 
		] )->where ( "UserRole", 4 )->select ();
		return json ( $list );
	}
	public function getDetail($id = 0) {
		// $fieldMode = [
		// // 流水查看
		// 'flow' => "id,applicant,createTime,troubleDescrition,status",
		// // 部门审批详细页
		// 'deptA' => "id,applicant,emailAddr,createTime,dept2,dept,troubleType,troubleDescrition,logs,status",
		// // 客响审批详细页
		// 'kxA' => "id,applicant,emailAddr,createTime,dept2,dept,troubleType,troubleDescrition,applicationApproval,applicationApprovalTime,logs,status",
		// // 受理派单详细页
		// 'receive' => "id,applicant,emailAddr,createTime,dept2,dept,troubleType,troubleDescrition,applicationApproval,applicationApprovalTime,approvalOpinion,approvalTime,logs,status",
		// // 代维受理详细页
		// 'accept' => "id,applicant,emailAddr,createTime,dept2,dept,troubleType,troubleDescrition,applicationApproval,applicationApprovalTime,approvalOpinion,approvalTime,receiver,dispatchTime,logs,status",
		// // 申请人确认详细页
		// 'confirm' => "id,applicant,emailAddr,createTime,dept2,dept,troubleType,troubleDescrition,applicationApproval,applicationApprovalTime,approvalOpinion,approvalTime,receiver,dispatchTime,acceptanceTime,results,logs,status"
		// ];
		$field = "";
		$data = Db::name ( "forms" )->field ( $field )->find ( $id );
		$data = Forms::beautify ( $data, true );
		return json ( $data );
		session ( "user.UserRole" );
	}
	// 修改报修单信息(无安全验证)
	public function handle() {
		$data = input ();
		if (! empty ( $data ) && ! empty ( $data ['id'] )) {
			Forms::update ( $data, [ 
					"id" => $data ['id'] 
			], true );
			return $this->getDetail ( $data ['id'] );
		} else {
			return $this->display ( "<h1>传入为空</h1>" );
		}
	}
	// 修改报修单信息(验证权限)
	public function handlec() {
		$field = 'logs,status';
		$fieldAttr = [ 
				0 => 'marks',
				1 => 'applicationApproval,applicationApprovalTime',
				2 => 'approvalOpinion,approvalTime',
				3 => 'receiver,dispatchTime,managerConfirm',
				4 => 'acceptanceTime,results' 
		];
		$userRole = session ( "user.UserRole" ) ? session ( "user.UserRole" ) : 0;
		$field .= $fieldAttr [$userRole];
		$data = input ();
		if (! empty ( $data ) && ! empty ( $data ['id'] )) {
			Forms::update ( $data, [ 
					"id" => $data ['id'] 
			], $field );
			return $this->getDetail ( $data ['id'] );
		} else {
			return $this->display ( "<h1>传入为空</h1>" );
		}
	}
	public function todo() {
		$d = Db::name ( "check" )->find ( [ 
				"code" => "0123" 
		] );
		return dump ( time () - strtotime ( $d ['time'] ) );
	}
	public function getUser() {
	}
	/**
	 * 填写申请单
	 *
	 * return mixed|string
	 */
	public function apply() {
		return $this->fetch ();
	}
	/**
	 * 我的
	 *
	 * @return mixed|string
	 */
	public function my() {
		return $this->display ( implode ( session ( "user" ), "," ) );
	}
	/**
	 * 我的申请单
	 *
	 * @return mixed|string
	 */
	public function mine() {
		return $this->fetch ();
	}
	/**
	 * 测试函数、、发布前删除
	 */
	public function tt() {
		$u = "tt";
		$p = 'Aa123123';
		$n = Forms::update ( [ 
				'logs' => 'djdj' 
		], [ 
				'id' => 7 
		] );
		return dump ( $n );
	}
}
