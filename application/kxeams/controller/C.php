<?php

namespace app\kxeams\controller;

use app\common\controller\Common;
use think\Controller;
use think\Request;
use think\Db;

class C extends Common {
	
	/**
	 * 获取 todo main表的 detail 信息
	 *
	 * @param unknown $main_id        	
	 */
	protected function todoDetail($main_id) {
		return Db::name ( "detail" )->where ( "main_id", $main_id )->alias ( "dd" )->join ( "__ITEM__ ii", "ii.id=dd.item_id" )->field ( "name,type,maker,units,count,dusage,dremarks" )->select ();
	}
	protected function storeCounts($types = []) {
	}
}
