<?php

namespace app\zx_apply\model;

use think\Model;

class Vlantables extends Model {
	public static function createVlan($aStation = "", $vlan = "", $cName = "") {
		$devices = json_decode ( config ( "device9312" ), true );
		
		$date = [ 
				"deviceName" => $devices [config ( "aStation" ) [$aStation]],
				"vlan" => $vlan,
				"description" => $cName 
		];
		$this->isUpdate ( false )->save ( $date );
	}
	public function generateVlan($aStation = "", $cName = "") {
	}
}