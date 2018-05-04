<?php
return [ 
		'version'=>"V1.0417-kx",
		'领用进度'=>[
				0=>"待审批",
				1=>"审批未通过",
				2=>"审批通过",
		],
		'发放进度'=>[
				0=>"待知晓",
				1=>"拒绝接收",
				2=>"同意接收",
		],
		'db_esserver'=>[
				'type'           => 'Sqlsrv',
				// 服务器地址
				'hostname'       => '223.100.98.60',
				// 数据库名
				'database'       => 'ESapp1',
				// 用户名
				'username'       => 'sa',
				// 密码
				'password'       => 'Zz123456',
				// 端口
				'hostport'       => '1433',
				// 数据库编码默认采用utf8
				'charset'        => 'gbk2312',
				// 数据库表前缀
				'prefix'         => 'dbo.ES_',

		],
];