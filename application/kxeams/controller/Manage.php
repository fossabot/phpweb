<?php

namespace app\kxeams\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\kxeams\model\Main;
use app\kxeams\model\Item;
use app\kxeams\model\Detail;

class Manage extends User {
	protected $beforeActionList = [ 
			'checkAuth' 
	];
	public function checkAuth() {
		// if (strpos ( input ( 'session.user/a' )['role'], '管理员' ) === false) {
		if (strpos ( session ( 'user.role' ), '管理员' ) === false) {
			return $this->error ( "您的权限不足，无法访问。", "user/index" );
		}
	}
	public function index() {
		if (Request::instance ()->isGet ()) {
			return $this->indexGet ();
		} else if (Request::instance ()->isPost ()) {
			return $this->indexPost ();
		}
	}
	/**
	 * 物品总览
	 */
	public function indexGet() {
		$fields = "id,name,type,maker,units,class";
		$col_names = "序号,名称,类型,单位,物品大类";
		$items = db ( "item" )->field ( $fields )->select ();
		$result = [ ];
		for($i = 0; $i < count ( $items ); $i ++) {
			// 根据每一个id，分别查询main明细，并计算数量
			$data = db ( "detail" )->alias ( "dd" )->join ( '__MAIN__ mm', 'dd.main_id = mm.id' )->where ( [ 
					"item_id" => $items [$i] ['id'],
					"moffice" => session ( "user.office" ) 
			] )->field ( "count,form_type,progress,from_dept,from_store,to_dept,to_store" )->select ();
			$sum = 0;
			foreach ( $data as $d ) {
				if (in_array ( $d ["progress"], [ 
						config ( "领用进度.2" ),
						config ( "发放进度.2" ),
						"-" 
				] )) {
					if ($d ["to_dept"] == session ( "user.dept" ))
						$sum += $d ["count"];
					else if ($d ["from_dept"] == session ( "user.dept" )) {
						$sum -= $d ["count"];
					}
				}
			}
			$items [$i] ['sum'] = $sum; // 把sum属性加到每个item里
		}
		
		$items = $this->array_to_json ( $items );
		$this->assign ( [ 
				"Header" => $col_names . ",库存数量",
				"Widths" => "80,150,150,100,100,100,*",
				"ColSorting" => "int,str,str,str,str,str,int",
				"ColTypes" => "ro,ro,ro,ro,ro,ro,ro",
				/*"dd" => json_encode ( [ 
						"data" => $items 
				] ) */
				"dd" => $items 
		] );
		// return dump ( $items );
		return $this->fetch ();
	}
	public function indexPost() {
		$id = input ( "post.del_id" );
		// return dump ( $id );
		$data = Db::name ( "item" )->where ( "id", $id )->find ();
		return $data;
	}
	/**
	 * 出入库查询明细
	 */
	public function loglist() {
		$main = new Main ();
		$headers = "dd.id as ddid,name,type,maker,units,count,dusage,dremarks,mm.id as mmid,mcreate_time,form_type,username,usertel,location,owner,dept";
		$data = $main->alias ( "mm" )->join ( '__DETAIL__ dd', 'mm.id = dd.main_id' )->join ( '__ITEM__ ii', 'ii.id = dd.item_id' )->field ( $headers )->order ( "mmid" )->select ();
		$res = [ ];
		foreach ( $data as $k => $v ) {
			$res [] = $v->toArray ();
		}
		// return dump($res);
		// $csvstr = $this->array_to_json ( $res );
		// return $csvstr;
		$this->assign ( "dd", $this->array_to_json ( $res ) );
		return $this->fetch ();
	}
	public function loglist_v2() { // 测试用,不好用
		$main = new Main ();
		$headers = "name,type,maker,units,class,count,location,mcreate_time,owner,musage,dept,username,usertel,form_type,progress,mremarks";
		$data = $main->alias ( "mm" )->join ( '__DETAIL__ dd', 'mm.id = dd.main_id' )->join ( '__ITEM__ ii', 'ii.id = dd.item_id' )->field ( $headers )->order ( "name desc" )->select ();
		$res = [ ];
		foreach ( $data as $k => $v ) {
			$res [] = $v->toArray ();
		}
		$this->assign ( "dd", json_encode ( $res, 256 ) );
		return $this->fetch ();
	}
	/**
	 * 新到入库
	 */
	public function new_change() {
		if (Request::instance ()->isGet ()) {
			$attr = $this->getTableAttr ( 'asset_main_change' );
			$this->assign ( [ 
					'fields' => $attr ['fields'],
					'headers' => $attr ['headers'],
					'defaultval' => $attr ['defaultval'],
					'sortattr' => $attr ['sortattr'],
					'edattr' => $attr ['edattr'],
					'diswidth' => $attr ['diswidth'],
					'validation' => $attr ['validation'] 
			] );
			return $this->fetch ( "new_change" );
		} else {
			$dataa = input ( 'post.data/a' ); // 获取post数据
			$result = $this->csv_to_array ( explode ( ",", $dataa ['mygrid__ColNames'] ), $dataa ['data'] );
			// return dump ( $dataa['table'] )."<br>".dump ( $result );
			$main = new Main ();
			$main->allowField ( true )->data ( $dataa ['table'], true )->isUpdate ( false )->save ();
			$main_id = $main->id;
			$item = new Item ();
			$detail = new Detail ();
			// 多行数据逐行处理
			foreach ( $result as $data ) {
				$field = [ 
						'name' => $data ['name'],
						'type' => $data ['type'],
						'maker' => $data ['maker'] 
				];
				$iid = $item->where ( $field )->value ( 'id' );
				// item 不存在则新增
				if ($iid == null) {
					// $data ['icreate_time'] = date("Y-m-d H:i:s",time());
					$item->allowField ( true )->data ( $data, true )->isUpdate ( false )->save ();
					$data ['item_id'] = $item->id;
				} else {
					$data ['item_id'] = $iid;
				}
				$data ['main_id'] = $main_id;
				$detail->allowField ( true )->data ( $data, true )->isUpdate ( false )->save ();
			}
			
			return $this->success ( "", url ( 'manage/index' ) );
		}
	}
	public function setting() {
		return $this->fetch ();
	}
	
	/**
	 * 获取表属性
	 *
	 * @param string $table_name        	
	 * @param unknown $uid        	
	 */
	private function getTableAttr($uid) {
		return Db::name ( 'table_attr' )->where ( 'uid', $uid )->find ();
	}
}