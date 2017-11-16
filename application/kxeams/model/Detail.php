<?php

namespace app\kxeams\model;

use think\Model;

class Detail extends Model {
	protected $type = [
			'id' => 'integer',
			'main_id' => 'integer',
			'item_id' => 'integer',
			'count' => 'integer',
	];
}