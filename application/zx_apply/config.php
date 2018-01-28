<?php
return [ 
		'version' => "V1.0129.zx",
		'db_esserver' => [ 
				'type' => 'Sqlsrv',
				// 服务器地址
				'hostname' => '223.100.98.60',
				// 数据库名
				'database' => 'ESapp1',
				// 用户名
				'username' => 'sa',
				// 密码
				'password' => 'Zz123456',
				// 端口
				'hostport' => '1433',
				// 数据库编码默认采用utf8
				'charset' => 'gbk2312',
				// 数据库表前缀
				'prefix' => 'dbo.ES_' 
		
		],
		// 'aStation' => '{"柴河局09":"CHJ-09","西丰新局":"XF-10","昌图新局":"CT-11","昌图老局":"CT-12","西丰老局":"XF-13","开原老局":"KY-14","铁岭县":"TLX-15","清河局":"QH-16","新台子":"XTZ-17","调兵山新局":"DBS-18","调兵山老局":"DBS-19","开原新局":"KY-20","柴河局-华为":"CHJ-21","柴河局-中兴":"CHJ-22","银州区":"YZL-23"}',
		'aStation' => [ 
				"柴河局09" => "CHJ-09",
				"西丰新局" => "XF-10",
				"昌图新局" => "CT-11",
				"昌图老局" => "CT-12",
				"西丰老局" => "XF-13",
				"开原老局" => "KY-14",
				"铁岭县" => "TLX-15",
				"清河局" => "QH-16",
				"新台子" => "XTZ-17",
				"调兵山新局" => "DBS-18",
				"调兵山老局" => "DBS-19",
				"开原新局" => "KY-20",
				"柴河局-华为" => "CHJ-21",
				"柴河局-中兴" => "CHJ-22",
				"银州区" => "YZL-23" 
		],
		'device9312' => '{"CHJ-09":{"up_port_yz":"Eth-Trunk12","up_port_ch":"Eth-Trunk9","port_hw":"Eth-Trunk36","port_zte":"Eth-Trunk26","bas02_down_port":"Eth-Trunk9","bas01_down_port":"Eth-Trunk9","rbp_name":"ch-yz-sw09-chj9312-jk"},"XF-10":{"up_port_yz":"Eth-Trunk114","up_port_ch":"Eth-Trunk14","port_hw":"Eth-Trunk2","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk51","bas01_down_port":"Eth-Trunk3","rbp_name":"ch-yz-sw10-xf9312-pppoe"},"CT-11":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","port_hw":"Eth-Trunk6","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk41","bas01_down_port":"Eth-Trunk41","rbp_name":"ch-yz-sw11-ct9312-pppoe"},"CT-12":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk42","port_zte":"Eth-Trunk43","bas02_down_port":"Eth-Trunk42","bas01_down_port":"Eth-Trunk42","rbp_name":"ch-yz-sw12-ct9312"},"XF-13":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_zte":"Eth-Trunk18","bas02_down_port":"Eth-Trunk46","bas01_down_port":"Eth-Trunk46","rbp_name":"ch-yz-sw13-xf9312"},"KY-14":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","port_hw":"Eth-Trunk23","port_zte":"Eth-Trunk24","bas02_down_port":"Eth-Trunk43","bas01_down_port":"Eth-Trunk43","rbp_name":"ch-yz-sw14-ky9312-pppoe"},"TLX-15":{"up_port_yz":"Eth-Trunk114","up_port_ch":"Eth-Trunk14","port_hw":"Eth-Trunk2","bas02_down_port":"Eth-Trunk45","bas01_down_port":"Eth-Trunk45","rbp_name":"ch-yz-sw15-tlx9312-jk"},"QH-16":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","port_hw":"Eth-Trunk13","port_zte":"Eth-Trunk7","bas02_down_port":"Eth-Trunk6","bas01_down_port":"Eth-Trunk6","rbp_name":"ch-yz-sw16-qh9312-jk"},"XTZ-17":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","bas02_down_port":"Eth-Trunk47","bas01_down_port":"Eth-Trunk47","rbp_name":"ch-yz-sw17-xtz9312-jk"},"DBS-18":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk3","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk53","bas01_down_port":"Eth-Trunk54","rbp_name":"ch-yz-sw18-dbs9312"},"DBS-19":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","bas02_down_port":"Eth-Trunk49","bas01_down_port":"Eth-Trunk49","rbp_name":"ch-yz-sw19-dbs9312"},"KY-20":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","port_hw":"Eth-Trunk27","port_zte":"Eth-Trunk26","bas02_down_port":"Eth-Trunk50","bas01_down_port":"Eth-Trunk50","rbp_name":"ch-yz-sw20-ky9312-pppoe"},"CHJ-21":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk41","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk39","bas01_down_port":"Eth-Trunk39","rbp_name":"ch-yz-sw21-chj9312-pppoe"},"CHJ-22":{"up_port_yz":"Eth-Trunk1","up_port_ch":"Eth-Trunk0","port_zte":"Eth-Trunk17","bas02_down_port":"Eth-Trunk59","bas01_down_port":"Eth-Trunk59","rbp_name":"ch-yz-sw22-chj9312-pppoe"},"YZL-23":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","bas02_down_port":"Eth-Trunk19","bas01_down_port":"Eth-Trunk19","rbp_name":"ch-yz-sw23-yzl9312-pppoe"}}',
	// 'device9312' => '{"柴河-华为":{"up_port_yz":"Eth-Trunk12","up_port_ch":"Eth-Trunk9","port_hw":"Eth-Trunk36","port_zte":"Eth-Trunk26","bas02_down_port":"Eth-Trunk9","bas01_down_port":"Eth-Trunk9","rbp_name":"ch-yz-sw09-chj9312-jk"},"柴河-中兴":{"up_port_yz":"Eth-Trunk114","up_port_ch":"Eth-Trunk14","port_hw":"Eth-Trunk2","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk51","bas01_down_port":"Eth-Trunk3","rbp_name":"ch-yz-sw10-xf9312-pppoe"},"西丰新局":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","port_hw":"Eth-Trunk6","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk41","bas01_down_port":"Eth-Trunk41","rbp_name":"ch-yz-sw11-ct9312-pppoe"},"西丰老局":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","port_hw":"Eth-Trunk23","port_zte":"Eth-Trunk24","bas02_down_port":"Eth-Trunk43","bas01_down_port":"Eth-Trunk43","rbp_name":"ch-yz-sw14-ky9312-pppoe"},"昌图新局":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk42","port_zte":"Eth-Trunk43","bas02_down_port":"Eth-Trunk42","bas01_down_port":"Eth-Trunk42","rbp_name":"ch-yz-sw12-ct9312"},"昌图老局":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_zte":"Eth-Trunk18","bas02_down_port":"Eth-Trunk46","bas01_down_port":"Eth-Trunk46","rbp_name":"ch-yz-sw13-xf9312"},"开原老局":{"up_port_yz":"Eth-Trunk114","up_port_ch":"Eth-Trunk14","port_hw":"Eth-Trunk2","bas02_down_port":"Eth-Trunk45","bas01_down_port":"Eth-Trunk45","rbp_name":"ch-yz-sw15-tlx9312-jk"},"开原新局":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","port_hw":"Eth-Trunk27","port_zte":"Eth-Trunk26","bas02_down_port":"Eth-Trunk50","bas01_down_port":"Eth-Trunk50","rbp_name":"ch-yz-sw20-ky9312-pppoe"},"清河":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","bas02_down_port":"Eth-Trunk47","bas01_down_port":"Eth-Trunk47","rbp_name":"ch-yz-sw17-xtz9312-jk"},"调兵山新局":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk3","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk53","bas01_down_port":"Eth-Trunk54","rbp_name":"ch-yz-sw18-dbs9312"},"调兵山_老":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","bas02_down_port":"Eth-Trunk49","bas01_down_port":"Eth-Trunk49","rbp_name":"ch-yz-sw19-dbs9312"},"铁岭县":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","port_hw":"Eth-Trunk13","port_zte":"Eth-Trunk7","bas02_down_port":"Eth-Trunk6","bas01_down_port":"Eth-Trunk6","rbp_name":"ch-yz-sw16-qh9312-jk"},"银州区":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk41","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk39","bas01_down_port":"Eth-Trunk39","rbp_name":"ch-yz-sw21-chj9312-pppoe"},"新台子":{"up_port_yz":"Eth-Trunk1","up_port_ch":"Eth-Trunk0","port_zte":"Eth-Trunk17","bas02_down_port":"Eth-Trunk59","bas01_down_port":"Eth-Trunk59","rbp_name":"ch-yz-sw22-chj9312-pppoe"},"柴河09":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","bas02_down_port":"Eth-Trunk19","bas01_down_port":"Eth-Trunk19","rbp_name":"ch-yz-sw23-yzl9312-pppoe"}}'
		// 额外信息，用于IP备案
		'extraInfo'=>[
				
		],
		
];