<?php

namespace app\kxeams\model;

use think\Model;
use traits\model\SoftDelete;

class Item extends Model {
	use SoftDelete;
	protected $deleteTime = 'idelete_time';
	protected $autoWriteTimestamp = 'datetime';
	protected $createTime = 'icreate_time';
	protected $updateTime = false;
	protected $type = [
			'id' => 'integer',
			'icreate_time' => 'datetime',
			'idelete_time' => 'datetime'
	];
}