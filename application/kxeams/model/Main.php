<?php

namespace app\kxeams\model;

use think\Model;
use traits\model\SoftDelete;

class Main extends Model {
	
	use SoftDelete;
	protected $deleteTime = 'mdelete_time';
	protected $autoWriteTimestamp = 'datetime';
	protected $createTime = 'mcreate_time';
	protected $updateTime = false;
	protected $type = [ 
			'id' => 'integer',
			'mcreate_time' => 'datetime',
			'mdelete_time' => 'datetime' 
	];
	public function item() {
		return $this->belongsTo ( 'Item' );
	}
}