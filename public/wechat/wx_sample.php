<?php
/**
 * wechat php test
 */
// define your token
define ( "TOKEN", "wx_xianda" );
define ( 'HELPSTR', "感谢您de关注。\n你说啥我回啥。发 help 回复您此条。" );
$wechatObj = new wechatCallbackapi ();
$wechatObj->responseMsg ();
// $wechatObj->valid ();
class wechatCallbackapi {
	public function valid() {
		$echoStr = $_GET ["echostr"];
		// valid signature , option
		if ($this->checkSignature ()) {
			echo $echoStr;
			exit ();
		}
	}
	public function responseMsg() {
		// get post data, May be due to the different environments
		// $postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
		$postStr = isset ( $GLOBALS ['HTTP_RAW_POST_DATA'] ) ? $GLOBALS ['HTTP_RAW_POST_DATA'] : file_get_contents ( "php://input" );
		// extract post data
		if (! empty ( $postStr )) {
			
			$postObj = simplexml_load_string ( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
			$RX_TYPE = trim ( $postObj->MsgType );
			
			switch ($RX_TYPE) {
				case "text" :
					$resultStr = $this->handleText ( $postObj );
					break;
				case "event" :
					$resultStr = $this->handleEvent ( $postObj );
					break;
				
				default :
					$resultStr = "Unknow msg type: " . $RX_TYPE;
					break;
			}
			echo $resultStr;
		} else {
			echo "There's nothing.";
			exit ();
		}
	}
	private function handleText($postObj) {
		$fromUsername = $postObj->FromUserName;
		$toUsername = $postObj->ToUserName;
		$keyword = trim ( $postObj->Content );
		$time = time ();
		$textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
		if (! empty ( $keyword )) {
			$msgType = "text";
			$contentStr = $keyword;
			if ($keyword == "help") {
				$contentStr = HELPSTR;
			}
			$resultStr = sprintf ( $textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr );
			echo $resultStr;
		} else {
			echo "Input something...";
		}
	}
	private function handleEvent($object) {
		$contentStr = "";
		switch ($object->Event) {
			case "subscribe" :
				$contentStr = HELPSTR;
				break;
			default :
				$contentStr = "Unknow Event: " . $object->Event;
				break;
		}
		$resultStr = $this->responseText ( $object, $contentStr );
		return $resultStr;
	}
	private function responseText($object, $content, $flag = 0) {
		$textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
		$resultStr = sprintf ( $textTpl, $object->FromUserName, $object->ToUserName, time (), $content, $flag );
		return $resultStr;
	}
	private function checkSignature() {
		// you must define TOKEN by yourself
		if (! defined ( "TOKEN" )) {
			throw new Exception ( 'TOKEN is not defined!' );
		}
		$signature = $_GET ["signature"];
		$timestamp = $_GET ["timestamp"];
		$nonce = $_GET ["nonce"];
		
		$token = TOKEN;
		$tmpArr = array (
				$token,
				$timestamp,
				$nonce 
		);
		// use SORT_STRING rule
		sort ( $tmpArr, SORT_STRING );
		$tmpStr = implode ( $tmpArr );
		$tmpStr = sha1 ( $tmpStr );
		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}
}
?>