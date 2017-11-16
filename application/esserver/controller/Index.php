<?php

namespace app\esserver\controller;

use think\Db;
use app\common\controller\Common;

class Index extends Common {
	public function index() {
		return $this->redirect ( "main" );
	}
	public function main() {
		if (request ()->isGet ()) {
			return $this->fetch ();
		} else {
			// 密码重置申请
			$input = input ( "post." ) ['data'];
			// return dump($input);
			$sql = "select * from [ESApp1].[dbo].[ES_User] where UserLogin = '" . $input ["UserLogin"] . "'";
			$res = $this->query ( $sql );
			if ($res == null) {
				// 账户不存在
				return [ 
						"auth" => [ 
								"vUserLogin" => false 
						] 
				];
			} else {
				// 登录名存在
				$res = $res [0];
				// return dump ( strpos ( "yuxianda.tl@sadg","yuxianda") !== false );
				$auth = [ 
						"vUserLogin" => true,
						"vUserName" => $res ['UserName'] == $input ['UserName'],
						"vMobilePhone" => $res ['MobilePhone'] == $input ['MobilePhone'] 
					// "newEmail" => false
				];
				$auth ["EmptyMsg"] = '';
				if ($res ['MobilePhone'] == '') { // 预留电话为空
					$auth ["vMobilePhone"] = false;
					$auth ["EmptyMsg"] .= "预留MobilePhone为空<br>";
				}
				$mail = $input ['Email'] . $input ['Email2'];
				if ($res ['Email'] == '') {
					// Email 为空
					$auth ["vEmail"] = false;
					$auth ["EmptyMsg"] .= "预留Email为空";
				} else if ($res ['Email'] == $mail) {
					// Email 相等
					$auth ["vEmail"] = true;
					$auth ["newEmail"] = "完全匹配";
				} else if (strpos ( $mail, $input ['UserLogin'] ) !== false) {
					// Email 包含 UserLogin 字段
					$auth ["vEmail"] = true;
					$auth ["newEmail"] = "与登陆名符合";
				} else {
					$auth ["vEmail"] = false;
				}
				$flag = '';
				$e = '';
				$num = 0;
				$aInfo = null;
				if ($auth ["vEmail"] == true) {
					$num ++;
					$flag = 'email';
					$e = $res ['Email'];
				}
				if ($auth ["vMobilePhone"] == true)
					$num ++;
				if ($flag == '')
					$e = $res ['MobilePhone'] . "@139.com";
				
				if ($num > 0) {
					$aInfo = base64_encode ( json_encode ( [ 
							"u" => $res ['UserLogin'],
							"p" => $res ['UserPwd'],
							"e" => $e 
					] ) );
				}
				return [ 
						"auth" => $auth,
						"e" => $e,
						"num" => $num,
						"aInfo" => $aInfo 
				];
			}
		}
	}
	public function resetpwd() {
		// 通过邮件链接访问
		if (request ()->isGet ()) {
			$r = input ( "param.r" );
			if ($r == null) {
				return $this->error ( "访问无效。", "main" );
			}
			$r = json_decode ( base64_decode ( $r ), true );
			// 若获取的 base64 编码不合法，则解析为null，计算的时间差为1900年到发信时间的时间差。
			if (time () - $r ["t"] > 86400) {
				return $this->error ( "此链接已过期，您可重新申请找回。", "main", '', 10 );
			}
			$sql = "select UserLogin,UserName from [ESApp1].[dbo].[ES_User] where UserLogin = '" . $r ["u"] . "' and UserPwd = '" . $r ["p"] . "'";
			$res = $this->query ( $sql );
			if ($res != null) {
				$this->assign ( [ 
						'UserLogin' => $r ["u"],
						'UserPwd' => base64_encode ( $r ["p"] ) 
				] );
				return $this->fetch ();
			} else {
				return $this->error ( "参数不对，请检查是否已通过其他途径修改过密码，并重新申请找回。", "main" );
			}
		}
		// 申请找回页面点击发送邮件按钮
		if (request ()->isPost ()) {
			$input = input ( 'post.excel_data_for_resetpwd' );
			// return dump ( $input == null);
			if ($input == null) {
				// return dump($this->sendEmail ( "yuxianda.tl@ln.chinamobile.com", "测试主题" . time (), "正文" . time (), 'url' ));
				// 若没有传值，则直接访问。
				return $this->fetch ();
			}
			$info = json_decode ( base64_decode ( $input ), true );
			$info ['t'] = time ();
			$r = base64_encode ( json_encode ( $info ) );
			// return dump ( $r );
			$subject = "您刚刚申请Excel服务器账号" . $info ['u'] . "找回密码服务，请按正文操作。";
			$url ['out'] = "http://223.100.98.60/esserver/index/resetpwd.html?r=" . $r;
			$url ['in'] = "http://10.65.187.202/esserver/index/resetpwd.html?r=" . $r;
			$body = "<p>如果您未自己进行申请重置，请忽略此邮件。</p><hr>";
			$body .= "<p style='color: blue;'>请访问下面的链接进行重置密码（此链接有效期24小时）：</p>";
			$body .= "<p>内网>>><br><span style='font-size:10px;'><a href='" . $url ['in'] . "'>" . $url ['in'] . "</a>" . "</span></p>";
			$body .= "<p>外网>>><br><span style='font-size:10px;'><a href='" . $url ['out'] . "'>" . $url ['out'] . "</a></span></p>";
			
			$sendEmail = $this->sendEmail ( $info ['e'], $subject, $body, $url );
			if (is_bool ( $sendEmail )) {
				$re = [ 
						'status' => true,
						'msg' => '相关邮件已发送，请到邮箱内查收来自<b>tl_excelserver@139.com</b>的邮件。' 
				];
			} else {
				$re = [ 
						'status' => true,
						'msg' => '邮件发送未成功：' . $sendEmail 
				];
			}
			
			return $re;
		}
		// 重置密码操作，保存新密码
		if (request ()->isPut ()) {
			$input = input ( "post." );
			if ($input ["UserPwd"] != $input ["UserPwd2"]) {
				return $this->error ( "两次密码不一致" );
			} else {
				$cipherPwd = strtoupper ( md5 ( $input ["UserPwd"] ) );
				if (! $this->validPwd ( $input ["UserPwd"] )) {
					return $this->error ( "必须包含大写字母、小写字母、数字三种组合在一起，且长度最少为8。请重试。", null, "密码复杂度不符合要求" );
				} else if ($this->validPwdHis ( $input ["UserLogin"], $cipherPwd )) {
					return $this->error ( "密码不能与最近2次历史密码重复，请重试。", null, "新密码不符合要求" );
				} else {
					// 更新密码操作。
					$p = base64_decode ( $input ["p"] );
					$sql = "update [ESApp1].[dbo].[ES_User] set UserPwd = '" . $cipherPwd . "',LstLoginTime = GETDATE(),PwdDate = GETDATE(),AccState = 0 where UserLogin = '" . $input ["UserLogin"] . "' and UserPwd = '" . $p . "'";
					$res = Db::execute ( $sql );
					$sql2 = "insert into [ESApp1].[dbo].[ES_UserPwdHis] values ((select UserId from [ESApp1].[dbo].[ES_User] where UserLogin = '" . $input ["UserLogin"] . "'), GETDATE(), '" . $cipherPwd . "')";
					Db::execute ( $sql2 );
					// $sql = "select * from [ESApp1].[dbo].[ES_User] where UserLogin = '" . $input ["UserLogin"]. "' and UserPwd = '" . $p. "'";
					// $res = $this->query ( $sql );
					// return dump($sql);
					if ($res > 0) {
						$this->success ( "重置密码成功，请使用新密码登陆。", "main" );
					} else {
						$this->error ( "请检查是否已通过其他途径修改过密码或重试。", null, "未能重置密码" );
					}
				}
			}
		}
	}
	protected function sendEmail($address = '', $subject = '', $body = '', $url = []) {
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
		$mail->setFrom ( 'tl_excelserver@139.com', 'Excel服务器' );
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
		$mail->AltBody = '您的邮件客户端不支持查看HTML格式的邮件正文。请复制下面的地址到浏览器访问操作：' . implode ( "，", $url );
		
		if (! $mail->send ()) {
			return $mail->ErrorInfo;
		} else {
			return true;
		}
	}
	protected function query($sql, $encoding = false) {
		$res = Db::query ( $sql );
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
	protected function validPwd($pwd) {
		if (preg_match_all ( '/[A-Z]/', $pwd ) < 1)
			return false;
		else if (preg_match_all ( '/[a-z]/', $pwd ) < 1)
			return false;
		else if (preg_match_all ( '/[0-9]/', $pwd ) < 1)
			return false;
		else if (strlen ( $pwd ) < 7)
			return false;
		else
			return true;
	}
	/**
	 * 检查是否与近N此密码相同。
	 *
	 * @param unknown $u        	
	 * @param unknown $p        	
	 * @return boolean
	 */
	public function validPwdHis($u, $p, $t = 2) {
		$sql = "select top " . $t . " Pwd from [ESApp1].[dbo].[ES_UserPwdHis] where UserId = (select UserId from [ESApp1].[dbo].[ES_User] where UserLogin = '" . $u . "') order by CreTime desc";
		$PwdHis = Db::query ( $sql );
		$PwdHisArray = [ ];
		foreach ( $PwdHis as $v ) {
			$PwdHisArray [] = $v ['Pwd'];
		}
		return in_array ( $p, $PwdHisArray );
	}
	/**
	 * 反馈-密码找回
	 */
	public function reportLog() {
		if (request ()->isPost ()) {
			// return dump(input("param.UserLogin"));
			if (! input ( "?param.UserLogin" ) || ! input ( "?param.str" )) {
				return $this->error ( "参数无效。" );
			}
			$logData = [ 
					'k' => "找回密码一键反馈-" . input ( "param.UserLogin" ),
					'v' => input ( "param.str" ) 
				/*
			 * 'v' => json_encode ( [
			 * 'str' => input ( "param.str" ),
			 * 'time' => input ( "param.time" )
			 * ], JSON_UNESCAPED_UNICODE )
			 */
			];
			$log = db ( "log", 'logDatabase', false )->where ( "k", $logData ['k'] )->find ();
			if ($log == null) {
				// 不存在记录（从未反馈过）
				$id = db ( "log", 'logDatabase', false )->insertGetId ( $logData );
				$log = db ( "log", 'logDatabase', false )->find ( $id );
				
				$sendEmail = $this->sendEmail ( "13700101911@139.com", "Excel服务器账户重置密码反馈-" . input ( "param.UserLogin" ), "提交信息如下：<br><pre>" . input ( "param.str" ) . "</pre>" );
				if (is_bool ( $sendEmail )) {
					$re = [ 
							'title' => '已成功反馈',
							'msg' => '有进展会通过邮件回复你。感谢配合。' 
					];
				} else {
					$re = [ 
							'title' => '反馈操作异常，请通过其他方式反馈',
							'msg' => '通知邮件自动发送未成功：' . $sendEmail 
					];
				}
			} else {
				// 已有记录（点击过一键反馈按钮）
				$logArray = explode ( "\n", $log ['v'] );
				$time = $logArray [count ( $logArray ) - 1];
				// $time = json_decode ( $log ['v'], true ) ['time'];
				$re = [ 
						'title' => '不要重复反馈',
						'msg' => '之前反馈过下面账户的找回密码的情况<br><b>' . input ( "param.UserLogin" ) . "</b><br>" . $time . '<br>请勿重复操作。' 
				];
			}
			return $re;
		} else { // 默认get请求，测试用
			$logData = [ 
					'k' => "找回密码一键反馈-" . input ( "param.UserLogin" ),
					'v' => input ( "param.str" ) 
			];
			$log = db ( "log", 'logDatabase', false )->where ( "k", $logData ['k'] )->find ();
			
			$logArray = explode ( "\n", $log ['v'] );
			return dump ( $logArray );
			// return $this->error ( "不能如此访问" );
		}
	}
	public function a() {
		// return dump(Db::query('select * from ESApp1.dbo.ES_User where UserId=?',['154']));
		$sql = "SELECT top 10 * FROM [dbo].[网络费用起草流程_主表]";
		$res = Db::query ( $sql );
		// $res = Db::query('SELECT top 10 * FROM ESApp1.dbo.ES_User');
		if ($res == null)
			return "fail";
		else {
			$ans;
			for($i = 0; $i < count ( $res ); $i ++) {
				foreach ( $res [$i] as $k => $v ) {
					$ans [$i] [iconv ( "GB2312", "UTF-8//IGNORE", $k )] = $v;
				}
			}
			$res = null;
			return dump ( $ans );
		}
	}
	/**
	 * $md5 为 true 则不转为MD5（默认为false）
	 *
	 * @param string $name        	
	 * @param string $pwd        	
	 * @param string $md5        	
	 * @return void|string
	 */
	protected function SignIn($name = '', $pwd = '', $md5 = false) {
		if (! $md5)
			$pwd = strtoupper ( md5 ( $pwd ) );
		$sql = "select * from [ESApp1].[dbo].[ES_User] where UserLogin = '" . $name . "' and UserPwd = '" . $pwd . "'";
		$res = $this->query ( $sql );
		return dump ( $res );
	}
	public function aa() {
		return $this->SignIn ( "yuxianda", "Aa1234566" );
	}
	/**
	 * 执行Sql语句，$encoding 为 true，将执行结果的数组key转换为UTF-8（处理sqlServer表列名为中文时乱码的情况）
	 *
	 * @param unknown $sql        	
	 * @param string $encoding        	
	 * @return mixed|NULL
	 */
	public function SignIn_t($loginin = '', $pwd = '') {
		$loginin = 'yuxianda';
		// return dump(strtoupper(md5("Aa1234566")));
		$pwd = strtoupper ( md5 ( "Aa1234566" ) );
		$sql = "select * from [dbo].[ES_User] where UserLogin = '" . $loginin . "' and UserPwd = '" . $pwd . "'";
		$user = Db::query ( $sql );
		if ($user == null) {
			return "fail";
		} else {
			echo '<h2><span style="color: blue;">' . $user [0] ['UserName'] . "</span>使用密码:" . $pwd . "登陆成功！</h2><pre>";
			echo var_export ( $user, true );
			echo '</pre>';
			// return dump($user[0]['UserId']);
		}
	}
}