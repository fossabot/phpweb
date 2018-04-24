<?php
return [ 
		'version' => "V1.0313.zx",
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
				"请选择" => null,
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
				"银州区" => "YZL-23",
				"柴河局" => null
		],
		'device9312' => '{"CHJ-09":{"up_port_yz":"Eth-Trunk12","up_port_ch":"Eth-Trunk9","port_hw":"Eth-Trunk36","port_zte":"Eth-Trunk26","bas02_down_port":"Eth-Trunk9","bas01_down_port":"Eth-Trunk9","rbp_name":"ch-yz-sw09-chj9312-jk"},"XF-10":{"up_port_yz":"Eth-Trunk114","up_port_ch":"Eth-Trunk14","port_hw":"Eth-Trunk2","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk51","bas01_down_port":"Eth-Trunk3","rbp_name":"ch-yz-sw10-xf9312-pppoe"},"CT-11":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","port_hw":"Eth-Trunk6","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk41","bas01_down_port":"Eth-Trunk41","rbp_name":"ch-yz-sw11-ct9312-pppoe"},"CT-12":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk42","port_zte":"Eth-Trunk43","bas02_down_port":"Eth-Trunk42","bas01_down_port":"Eth-Trunk42","rbp_name":"ch-yz-sw12-ct9312"},"XF-13":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_zte":"Eth-Trunk18","bas02_down_port":"Eth-Trunk46","bas01_down_port":"Eth-Trunk46","rbp_name":"ch-yz-sw13-xf9312"},"KY-14":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","port_hw":"Eth-Trunk23","port_zte":"Eth-Trunk24","bas02_down_port":"Eth-Trunk43","bas01_down_port":"Eth-Trunk43","rbp_name":"ch-yz-sw14-ky9312-pppoe"},"TLX-15":{"up_port_yz":"Eth-Trunk114","up_port_ch":"Eth-Trunk14","port_hw":"Eth-Trunk2","bas02_down_port":"Eth-Trunk45","bas01_down_port":"Eth-Trunk45","rbp_name":"ch-yz-sw15-tlx9312-jk"},"QH-16":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","port_hw":"Eth-Trunk13","port_zte":"Eth-Trunk7","bas02_down_port":"Eth-Trunk6","bas01_down_port":"Eth-Trunk6","rbp_name":"ch-yz-sw16-qh9312-jk"},"XTZ-17":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","bas02_down_port":"Eth-Trunk47","bas01_down_port":"Eth-Trunk47","rbp_name":"ch-yz-sw17-xtz9312-jk"},"DBS-18":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk3","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk53","bas01_down_port":"Eth-Trunk54","rbp_name":"ch-yz-sw18-dbs9312"},"DBS-19":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","bas02_down_port":"Eth-Trunk49","bas01_down_port":"Eth-Trunk49","rbp_name":"ch-yz-sw19-dbs9312"},"KY-20":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","port_hw":"Eth-Trunk27","port_zte":"Eth-Trunk26","bas02_down_port":"Eth-Trunk50","bas01_down_port":"Eth-Trunk50","rbp_name":"ch-yz-sw20-ky9312-pppoe"},"CHJ-21":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk41","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk39","bas01_down_port":"Eth-Trunk39","rbp_name":"ch-yz-sw21-chj9312-pppoe"},"CHJ-22":{"up_port_yz":"Eth-Trunk1","up_port_ch":"Eth-Trunk0","port_zte":"Eth-Trunk17","bas02_down_port":"Eth-Trunk59","bas01_down_port":"Eth-Trunk59","rbp_name":"ch-yz-sw22-chj9312-pppoe"},"YZL-23":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","bas02_down_port":"Eth-Trunk19","bas01_down_port":"Eth-Trunk19","rbp_name":"ch-yz-sw23-yzl9312-pppoe"}}',
		// 'device9312' => '{"柴河-华为":{"up_port_yz":"Eth-Trunk12","up_port_ch":"Eth-Trunk9","port_hw":"Eth-Trunk36","port_zte":"Eth-Trunk26","bas02_down_port":"Eth-Trunk9","bas01_down_port":"Eth-Trunk9","rbp_name":"ch-yz-sw09-chj9312-jk"},"柴河-中兴":{"up_port_yz":"Eth-Trunk114","up_port_ch":"Eth-Trunk14","port_hw":"Eth-Trunk2","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk51","bas01_down_port":"Eth-Trunk3","rbp_name":"ch-yz-sw10-xf9312-pppoe"},"西丰新局":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","port_hw":"Eth-Trunk6","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk41","bas01_down_port":"Eth-Trunk41","rbp_name":"ch-yz-sw11-ct9312-pppoe"},"西丰老局":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","port_hw":"Eth-Trunk23","port_zte":"Eth-Trunk24","bas02_down_port":"Eth-Trunk43","bas01_down_port":"Eth-Trunk43","rbp_name":"ch-yz-sw14-ky9312-pppoe"},"昌图新局":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk42","port_zte":"Eth-Trunk43","bas02_down_port":"Eth-Trunk42","bas01_down_port":"Eth-Trunk42","rbp_name":"ch-yz-sw12-ct9312"},"昌图老局":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_zte":"Eth-Trunk18","bas02_down_port":"Eth-Trunk46","bas01_down_port":"Eth-Trunk46","rbp_name":"ch-yz-sw13-xf9312"},"开原老局":{"up_port_yz":"Eth-Trunk114","up_port_ch":"Eth-Trunk14","port_hw":"Eth-Trunk2","bas02_down_port":"Eth-Trunk45","bas01_down_port":"Eth-Trunk45","rbp_name":"ch-yz-sw15-tlx9312-jk"},"开原新局":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","port_hw":"Eth-Trunk27","port_zte":"Eth-Trunk26","bas02_down_port":"Eth-Trunk50","bas01_down_port":"Eth-Trunk50","rbp_name":"ch-yz-sw20-ky9312-pppoe"},"清河":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","bas02_down_port":"Eth-Trunk47","bas01_down_port":"Eth-Trunk47","rbp_name":"ch-yz-sw17-xtz9312-jk"},"调兵山新局":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk3","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk53","bas01_down_port":"Eth-Trunk54","rbp_name":"ch-yz-sw18-dbs9312"},"调兵山_老":{"up_port_yz":"Eth-Trunk112","up_port_ch":"Eth-Trunk12","bas02_down_port":"Eth-Trunk49","bas01_down_port":"Eth-Trunk49","rbp_name":"ch-yz-sw19-dbs9312"},"铁岭县":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","port_hw":"Eth-Trunk13","port_zte":"Eth-Trunk7","bas02_down_port":"Eth-Trunk6","bas01_down_port":"Eth-Trunk6","rbp_name":"ch-yz-sw16-qh9312-jk"},"银州区":{"up_port_yz":"Eth-Trunk113","up_port_ch":"Eth-Trunk13","port_hw":"Eth-Trunk41","port_zte":"Eth-Trunk5","bas02_down_port":"Eth-Trunk39","bas01_down_port":"Eth-Trunk39","rbp_name":"ch-yz-sw21-chj9312-pppoe"},"新台子":{"up_port_yz":"Eth-Trunk1","up_port_ch":"Eth-Trunk0","port_zte":"Eth-Trunk17","bas02_down_port":"Eth-Trunk59","bas01_down_port":"Eth-Trunk59","rbp_name":"ch-yz-sw22-chj9312-pppoe"},"柴河09":{"up_port_yz":"Eth-Trunk2","up_port_ch":"Eth-Trunk1","bas02_down_port":"Eth-Trunk19","bas01_down_port":"Eth-Trunk19","rbp_name":"ch-yz-sw23-yzl9312-pppoe"}}'
		// 额外信息，用于IP备案
		'extraInfo' => [ 
				"单位性质" => "unitProperty",
				"单位分类" => "unitCategory",
				"行业分类" => "industryCategory",
				"使用单位证件类型" => "credential",
				"使用单位证件号" => "credentialnum",
				"所在省" => "province",
				"所在市" => "city",
				"所在县区" => "county",
				"应用服务类型" => "appServType" 
		],
		// 登陆后跳转到manage模块的账号
		'manageEmails' => [ 
				"yuxianda.tl",
				"yangjietl.tl" 
		],
		
		// 不再使用（直接在index/apply.html里定义）
		// 单位性质
		'unitProperty' => [ 
				1 => '军队',
				2 => '政府机关',
				3 => '事业单位',
				4 => '企业',
				5 => '个人',
				6 => '社会团体',
				7 => '国瑞',
				8 => '个体工商户',
				9 => '民办非企业',
				10 => '基金会',
				11 => '律师事务所',
				12 => '外国文化中心',
				99 => '未知' 
		],
		// 单位分类
		'unitCategory' => [ 
				1 => 'ISP',
				2 => 'IDC',
				3 => 'ISP和IDC',
				4 => '公益性互联网络单位',
				5 => '其他',
				11 => '电信业务经营者',
				12 => '公益性互联网络单位',
				14 => '管局单位',
				15 => '前置单位',
				999 => '未知' 
		],
		// 行业分类
		'industryCategory' => [ 
				1 => '农、林、牧、渔业',
				2 => '采矿业',
				3 => '制造业',
				4 => '电力、燃气及水的生产和供应业',
				5 => '建筑业',
				6 => '交通运输、仓储和邮政业',
				7 => '信息传输、计算机服务和软件业',
				8 => '批发和零售业',
				9 => '住宿和餐饮业',
				10 => '金融业',
				11 => '房地产业',
				12 => '租赁和商务服务业',
				13 => '科学研究、技术服务和地质勘查业',
				14 => '水利、环境和公共设施管理业',
				15 => '居民服务和其他服务业',
				16 => '教育',
				17 => '卫生、社会保障和社会福利业',
				18 => '文化、体育和娱乐业',
				19 => '公共管理与社会组织',
				20 => '国际组织',
				21 => 'example',
				999 => '未知' 
		],
		// 使用单位证件类型
		'credential' => [ 
				1 => '工商营业执照',
				2 => '身份证',
				3 => '组织机构代码证书',
				4 => '事业法人证书',
				5 => '军队代号',
				6 => '社团法人证书',
				7 => '护照',
				8 => '军官证',
				9 => '组织机构代码证书',
				10 => '组织机构代码证书' 
		],
		'province' => [ 
				210000 => '辽宁省' 
		],
		'city' => [ 
				211200 => '铁岭市' 
		],
		'county' => [ 
				211201 => '市辖区',
				211202 => '银州区',
				211204 => '清河区',
				211221 => '铁岭县',
				211223 => '西丰县',
				211224 => '昌图县',
				211281 => '调兵山市',
				211282 => '开原市' 
		],
		'appServType' => [ 
				1 => '企事业单位专线接入',
				2 => '个人专线接入',
				3 => '固定宽带接入',
				4 => '移动网接入',
				5 => '网络设备',
				6 => '网站专线接入',
				7 => 'IDC服务类',
				8 => '物联网' 
		],
		'colWidth' => [ 
				0 => 98,
				1 => 96,
				2 => 65,
				3 => 50,
				4 => 65,
				5 => 100,
				6 => 240,
				7 => 220,
				8 => 450,
				9 => 50,
				10 => 116,
				11 => 116,
				12 => 107,
				13 => 107,
				14 => 164,
				15 => 93,
				16 => 96,
				17 => 164,
				18 => 200,
				19 => 75,
				20 => 200,
				21 => 60,
				22 => 107,
				23 => 164,
				24 => 98,
				25 => 98,
				26 => 50,
				27 => 80,
				28 => 0,
				29 => 76,
				30 => 76,
				31 => 76,
				32 => 132,
				33 => 107,
				34 => 62,
				35 => 62,
				36 => 76,
				37 => 104 
		] 
];