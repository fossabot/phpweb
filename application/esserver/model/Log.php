<?php

namespace app\esserver\model;

use think\Model;

class Log extends Model {
	// 未使用
	protected $pk = 'id';
	protected $type = [
			'v' => 'array'
	];
	/*protected $auto = [
	 'value'
	 ];
	 public function setValueAttr($value) {
	 return json_encode ( $value, JSON_UNESCAPED_UNICODE );
	 }*/
}