<?php

namespace app\kxeams\controller;

use think\Db;

class User extends C {
	/**
	 * 管理条例
	 *
	 * {@inheritdoc}
	 *
	 * @see \app\controller\C::index()
	 */
	public function index() {
		$this->assign ( [ 
				"laws" => $this->getSysInfo ( "laws" ) 
		] );
		return $this->fetch ();
	}
	/**
	 * 资产概况
	 */
	public function main() {
		return $this->fetch ();
	}
	/**
	 * 领用申请
	 */
	public function apply() {
		if (request ()->isGet ()) {
			return $this->fetch ( "apply" );
		} else {
			// 处理提交的数据
			 return dump ( input ());
			$applyFormData = input ( "apply/a" );
			$formAccount = $applyFormData [0] ['formAccount'];
			$applyFormData [0] ['progress'] = config ( "progress.0" );
			$main = model ( "Main" );
			if ($main->allowField ( true )->save ( $applyFormData [0] )) {
				// 保存 main (流水账) 返回的 id 用来保存到 detail 表
				$main_id = $main->id;
				$formName = [ 
						"item_id",
						"count",
						"dusage",
						"dremarks" 
				];
				$list = [ ];
				for($id = 1; $id <= $formAccount; $id ++) {
					// 遍历 领用物品的 Form
					$list [$id - 1] ["main_id"] = $main_id;
					for($i = 0; $i < count ( $formName ); $i ++)
						// 遍历 FormName 取值
						$list [$id - 1] [$formName [$i]] = $applyFormData [$id] [$formName [$i]];
				}
				$detail = model ( "detail" );
				if ($detail->allowField ( true )->saveAll ( $list )) {
					return [ 
							"state" => 1,
							"text" => "提交成功" 
					];
				} else {
					// 保存 detail 数据表出错
					return [ 
							"state" => 0,
							"title" => "保存领用明细数据表出错",
							"type" => "alert-error",
							"text" => "请联系开发者处理。" 
					];
				}
			} else {
				// 保存 main 数据表出错
				return [ 
						"state" => 0,
						"title" => "保存领用表单数据表出错",
						"type" => "alert-error",
						"text" => "请联系开发者处理。" 
				];
			}
		}
	}
	public function todo() {
		if (request ()->isGet ()) {
			$this->assign ( "list", $this->refleshTodoList () );
			return $this->fetch ();
		} else {
			$req = input ( "post.req" );
			$main_id = input ( "post.main_id" );
			if ($req == "getDetail") {
				return $this->todoDetail ( $main_id );
			}
			$form_type = mb_substr ( input ( "post.form_type" ), 2 );
			$from_store = input ( "post.from_store" );
			$mlogs = strlen ( input ( "post.mlogs" ) ) == 0 ? "" : $mlogs . "\n";
			if ($req == "yes") {
				Db::name ( "main" )->where ( "id", $main_id )->update ( [ 
						"from_store" => $from_store,
						"progress" => config ( $form_type . "进度.2" ),
						"mlogs" => $mlogs . config ( $form_type . "进度.2" ) . "，" . session ( ('user.name') ) . "，" . date ( 'Y-m-d H:i:s' ) 
				] );
				return $this->refleshTodoList ();
			} else if ($req == "no") {
				Db::name ( "main" )->where ( "id", $main_id )->update ( [ 
						"from_store" => $from_store,
						"progress" => config ( $form_type . "进度.1" ),
						"mlogs" => $mlogs . config ( $form_type . "进度.1" ) . "，" . session ( ('user.name') ) . "，" . date ( 'Y-m-d H:i:s' ) 
				] );
				return $this->refleshTodoList ();
			} else {
				return $this->error ( "无法执行您的请求。" );
			}
		}
	}
	/**
	 * 刷新 todoList 数据
	 *
	 * @return string
	 */
	protected function refleshTodoList() {
		$where1 = "`progress` = '" . config ( "领用进度.0" ) . "' and `form_type` = '维材领用' and `from_dept` = '" . session ( "user.dept" ) . "'";
		$where2 = "`progress` = '" . config ( "发放进度.0" ) . "' and `form_type` like '%发放' and `from_dept` = '" . session ( "user.dept" ) . "'";
		$data = Db::name ( "main" )->where ( $where1 )->whereOr ( $where2 )->order ( "mcreate_time desc" )->select (  );
		return json_encode ( $data, 256 );
	}
	public function history() {
		$where = [ 
				'to_dept' => session ( "user.dept" ) 
		];
		$list = $this->getMain ( $where );
		$this->assign ( "list", $list );
		return $this->fetch ();
	}
	protected function getMain($where = []) {
		$data = Db::name ( "main" )->where ( $where )->order ( "mcreate_time desc" )->select ();
		return json_encode ( $data, 256 );
	}
	/**
	 * 根据条件查询数据(无用)
	 *
	 * @param string $table        	
	 * @param string $field        	
	 * @param array $where        	
	 */
	public function match_item($field = '', $where = []) {
		if ($field == '')
			$field = input ( "post.field" );
		if ($where == '')
			$where = input ( "post.where/a" );
		return Db::name ( "item" )->where ( $where )->field ( $field )->select ();
	}
	public function t() {
		$a = [ ];
		$a [0] ["name"] = "name0";
		$a [0] ["type"] = "type0";
		$a [1] ["name"] = "name1";
		$a [1] ["type"] = "type1";
		$b = [ 
				[ 
						"name" => "name0",
						"type" => "type0" 
				],
				[ 
						"name" => "name1",
						"type" => "type1" 
				] 
		];
		echo "a<br>" . dump ( $a );
		echo "b<br>" . dump ( $b );
		die ();
		// return dump(json_encode($b));
		
		$field = "name,type,maker,units";
		$where ["class"] = "备品";
		$where ["units"] = "箱";
		return dump ( $this->match_item ( $field, $where ) );
	}
}