<?php

namespace app\trouble\model;

use think\Model;

class Forms extends Model {
	protected $type = [ 
			'id' => 'integer',
			'createTime' => 'datetime',
			'applicationApprovalTime' => 'datetime',
			'approvalTime' => 'datetime',
			'dispatchTime' => 'datetime',
			'acceptanceTime' => 'datetime',
			'marks' => 'integer',
			'status' => 'integer' 
	];
	public static function beautify($data, $single = false) {
		$status = [ 
				0 => '等待部门审批',
				1 => '部门不同意',
				2 => '等待处理',
				3 => '客响不同意',
				4 => '等待处理',
				5 => '已撤销',
				6 => '已派单，待受理',
				7 => '处理中',
				8 => '待申请人确认',
				9 => '待派单人归档',
				- 1 => '已归档' 
		];
		if (empty ( $data )) {
			return null;
		} else {
			if ($single) {
				$data ['statusText'] = $status [$data ['status']];
				$data ['logs'] = strtr ( $data ['logs'], [ 
						"；" => "；<br>" 
				] );
			} else {
				foreach ( $data as $k => $v ) {
					$data [$k] ['statusText'] = $status [$v ['status']];
				}
			}
			return $data;
		}
	}
}