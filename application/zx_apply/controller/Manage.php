<?php
namespace app\zx_apply\controller;

use think\Controller;
use think\Db;
use app\zx_apply\model\Vlantables;
use app\zx_apply\model\Infotables;
use app\zx_apply\model\Iptables;
use Overtrue\Pinyin\Pinyin;

class Manage extends Index
{

	protected $beforeActionList = [
		'checkAuth'
	];

	protected function checkAuth()
	{
		if (session("user.role") != "manage") {
			return $this->redirect("index/query");
		}
	}

	public function index()
	{
		return $this->redirect("todo");
	}

	/**
	 * 待办
	 *
	 * @return mixed|string
	 */
	public function todo()
	{
		if (request()->isGet()) {
			$this->assign("list", $this->refleshTodoList());
			return $this->fetch();
		}
		if (request()->isPost()) {
			$req = input("get.req");
            // $infotables = new Infotables ();
			$info = Infotables::get(input("post.id")); // 获取器数据
			$data = input("post.");
			if ($info) {
                // 设置不需要输出的字段
				$detail = $info->hidden([
					'aDate',
					'tags',
					'ipMask',
					'ipBMask',
					'create_time',
					'update_time',
					'delete_time'
				])->toArray();
			} else {
				return $this->success("这条待办找不到啦！肿么办？");
			}
			$extra = $detail["extra"];
			if (is_array($extra)) {
				foreach ($extra as $k => $v) {
					$detail[$k] = $v;
				}
			}
			$detail["ip"] = $info["ip"]; // 更正ip为 str 形式
			$detail["ipB"] = $info["ipB"]; // 更正ipB
			unset($detail["extra"]);
			if ($req == "getDetail") {
                // 返回单条数据及同客户名的信息摘要
				$cName = mb_substr($info["cName"], 2, 10, "utf-8");
				$field = "id,instanceId,cName,create_time,neFactory,vlan,aStation,ip,aPerson,aEmail,delete_time";
				$relative = collection(Infotables::withTrashed()->where("cName", "like", "%" . $cName . "%")->where("id", "<>", $info["id"])->field($field)->select())->toArray();
				$result = [
					"related" => $relative,
					"detail" => $detail,
					"string" => $cName
				];
				return json($result);
			} else if ($req == "auto_pre") {
				$device = config("aStation")[$data["aStation"]];
				$genIp = Iptables::generateIP($data["zxType"]);
				$genVlan = Vlantables::generateVlan($device, null, 1);
				return [
					"genIp" => $genIp,
					"preVlan" => $genVlan["preVlan"],
					"usedVlans" => $genVlan["usedVlans"],
					"device" => $device
				];
			} else if ($req == "distribution") {
				$this->checkInstanceID($info, $data);
				$data = $this->checkAndSetIp($info, $data);
				$data = $this->checkAndSetVlan($data);
                // $data ["status"] = 1;
				$result = $this->updateInfo($data);
                // 防止提前修改 status 导致 信息未修改无法识别
				if ($result) {
					if (isset($data["vlan"])) {
						Infotables::where("id", $data["id"])->setInc("status");
					}
					return $this->result($this->refleshTodoList(), 1, "操作成功。<br>是否发送邮件通知给申请人？");
				} else {
					return $this->result(null, 2, "本次提交信息并未修改");
				}
			}
		}
	}

	public function generateVlan()
	{
		$aStation = "";
		$aStationConf = config("aStation");
		if (array_key_exists(input("get.d"), $aStationConf)) {
			$device = $aStationConf[input("get.d")];
			return Vlantables::generateVlan($device, "预分配", 1);
		}
	}

	protected function updateInfo($data)
	{
		$extraHeader = config("extraInfo");
		foreach ($extraHeader as $k => $v) {
			$data["extra"][$v] = $data[$v];
			unset($data[$v]);
		}
        // unset ( $data ["delete_time"] );
		$infotables = new Infotables();
        // 更新单条数据
		$result = $infotables->isUpdate(true)->save($data, [
			"id" => $data["id"]
		]);
        // 更新最后分配的IP
		isset($data["ip"]) && Iptables::setLastIp($data["ip"]);
		$infotables->find($data["id"]);
		return $result;
	}

	protected function refleshTodoList()
	{
		$where = ["status" => 0];
		$field = "id,cName,create_time,aPerson,ifOnu,instanceId,zxType,aStation";
		$data = Infotables::where($where)->field($field)->order("create_time desc")->select(); // explode(",", $field)
		return json_encode($data, 256);
	}

	/**
	 * 发邮件告知申请已处理
	 */
	public function sendResultEmail($address = '', $zxType = '', $cName = '[unknown cName]')
	{
		$input = input("post.");
		$address = $input['address'];
		$zxType = $input['zxType'];
		$cName = $input['cName'];
		$db = Infotables::field("zxType,cName,vlan,ip,ipB,aEmail")->find($input["id"]);
		$address = $db->aEmail;
		$title = '[申请已处理]' . $zxType . '专线-' . $cName;
		$body = "";
		$body .= dump($db->toArray(), false);
		$body .= "<p>更多信息请登陆系统查看：</p><br>内网： <a href='http://10.65.178.202/zx_apply/index/query.html'>http://10.65.178.202/zx_apply/index/query.html</a><br>外网： <a href='http://223.100.98.60:800/zx_apply/index/query.html'>http://223.100.98.60:800/zx_apply/index/query.html</a>";
		$body .= "<hr><p style='color: blue;'>Tips: 系统内查看时可右键菜单，发现更多操作。</p>";
		$result = $this->sendEmail($address, $title, $body);
		return $this->result($result, is_bool($result) ? 1 : 0);
	}

	/**
	 * 信息查询
	 */
	public function query()
	{
		if (request()->isGet()) {
            // 访问
			$aStation = array_keys(config("aStation"));
			$zxTitle = [
				"label" => "zx_apply-new-rb",
				"order" => "24,1,4,5,6,9,10,19,22,23,26"
			];
			$this->assign([
				"aStationData" => implode(",", $aStation),
				"colHeaderData" => $this->getHeader($zxTitle["label"], $zxTitle["order"]),
				"colWidthsData" => $this->getColWidths($zxTitle["order"]),
				"data" => $this->getInfoData()->toJson()
			]);
			return $this->fetch();
		}
		if (request()->isPost()) {
            // 获取台账
            // return $this->getInfoData();
			input("post.r") == "info" && $data = $this->getInfoData(input("post.zxType"))->toArray();
			input("post.r") == "detail" && $data = Infotables::get(input("post.id"))->toJson();
			input("post.r") == "search" && $data = $this->querySearch(input("post."));
			input("get.r") == "update" && $data = $this->queryUpdateInfo(input("post."));
			input("post.r") == "delete" && $data = $this->queryDelete(input("post."));
			return $data;
		}
		if (request()->isPut()) {
            // 相关操作
			input("post.r") == "script" && $data = $this->generateScript(input("post.id/a")[0]);
			input("post.r") == "export_zg" && $data = $this->generateZgWorkflow(explode(",", input("post.id")));
			input("post.r") == "export_jtip" && $data = $this->generateJtIp(explode(",", input("post.id")));
			input("post.r") == "export_gxbip" && $data = $this->generateGxbIp(explode(",", input("post.id")));
			input("post.r") == "export" && $data = $this->queryExport(input("post.zxType"));
			return $data;
		}
	}

	protected function generateScript($id = null)
	{
		$data = Infotables::get($id);
		if ($data["zxType"] == "互联网") {
			if ($data['neFactory'] == 'ONU') {
				return $this->error("ONU业务暂不支持数据制作脚本");
			}
			return $this->generateScriptNet($data);
		}
		if ($data["zxType"] == "卫生网") {
			return $this->generateScriptWsw($data);
		}
	}

	/**
	 * 数据制作脚本生成-互联网
	 *
	 * @param unknown $data            
	 * @return string|NULL[]|string[]
	 */
	private function generateScriptNet($data)
	{
		$data["domain"] = 'tlyd-rb'; // 1. domain
		$aStation = config("aStation");
		$data["sw93"] = $aStation[$data["aStation"]]; // 2. 9312名
		$pinyin = new Pinyin();
		$desc = substr($data["sw93"], 0, stripos($data["sw93"], "-") + 1);
		$desc = str_replace("CHJ", "TL", $desc);
		$_desc = $pinyin->convert(preg_replace("/[^\x{4e00}-\x{9fa5}A-Za-z0-9-]/u", "", $data["cName"]));
		foreach ($_desc as $v) {
			$desc .= ucfirst($v);
		}
		$data["desc"] = $desc . "_NET"; // 3. 描述
		$device9312 = json_decode(config("device9312"), true)[$data["sw93"]];

		function bas($bas, $device9312, $data)
		{
			$trunk = $device9312['bas' . $bas . '_down_port'];
			$rbp = $device9312['rbp_name'];
			$_bas_name = [
				"01" => "01-CHJ",
				"02" => "02-YZL"
			];
			if (strlen($data["domain"]) > 4) { // tlyd-rb
				$rbp = "\n remote-backup-profile " . $rbp;
			} else {
				$rbp = null;
			}
			$script = "interface " . $trunk . "." . $data["vlan"];
			$script .= "\ndis th\n ";
			$script .= "\n description [LNTIL-MA-CMNET-BAS" . $_bas_name[$bas] . "ME60X]" . $trunk . "." . $data["vlan"] . "-[" . $data["desc"] . "]";
			$script .= "\n user-vlan " . $data["vlan"] . $rbp;
			$script .= "\n bas\n #\n access-type layer2-subscriber default-domain authentication " . $data["domain"];
			$script .= "\n authentication-method bind\n";
			$script .= "static-user " . $data["ip"] . " " . $data["ip"] . " gateway " . substr($data["ip"], 0, strripos($data["ip"], ".") + 1) . "1 " . "interface " . $trunk . "." . $data["vlan"] . " vlan " . $data["vlan"] . " domain-name " . $data["domain"] . " detect\r\n";
			return $script;
		}

		function the93($device9312, $data)
		{
			if ($data["neFactory"]) {
				$data["neFactory"] === '华为' && $down = "port_hw";
				$data["neFactory"] === '中兴' && $down = "port_zte";
			} else {
				return "网元厂家未定义";
			}
			$script = "vlan " . $data["vlan"] . "\ndis th\n \n";
			$script .= "description to-[" . $data["desc"] . "]\nq";
			$script .= "\ninterface " . $device9312["up_port_yz"]; // 上行银州 bas02
			$script .= "\nport trunk allow-pass vlan " . $data["vlan"];
			if (strlen($data["domain"]) > 4) { // 上行柴河 bas01
				$script .= "\ninterface " . $device9312["up_port_ch"] . "\nport trunk allow-pass vlan " . $data["vlan"];
			}
			$script .= "\ninterface " . $device9312[$down]; // 下行
			$script .= "\nport trunk allow-pass vlan " . $data["vlan"];
			$script .= "\nq\r\n";
			return $script;
		}
		$result = [
			"bas01" => bas("01", $device9312, $data),
			"bas02" => bas("02", $device9312, $data),
			"the93" => [
				$data["sw93"],
				the93($device9312, $data)
			]
		];
		return $result;
	}

	/**
	 * 数据制作脚本生成-卫生网
	 *
	 * @param unknown $data            
	 * @return string|NULL[]|string[]
	 */
	private function generateScriptWsw($data)
	{
		$aStation = config("aStation");
		$data["sw93"] = $aStation[$data["aStation"]]; // 2. 9312名
		$pinyin = new Pinyin();
		$desc = substr($data["sw93"], 0, stripos($data["sw93"], "-") + 1); // 3. 描述
		$desc = str_replace("CHJ", "TL", $desc);
		$_desc = $pinyin->convert(preg_replace("/[^\x{4e00}-\x{9fa5}A-Za-z0-9-]/u", "", $data["cName"]));
		foreach ($_desc as $v) {
			$desc .= ucfirst($v);
		}
		$device9312 = json_decode(config("device9312"), true)[$data["sw93"]];
		$trunk = $device9312['bas02_down_port'];
		$ip = Iptables::ip_parse($data["ip"]);
		$ipB = Iptables::ip_parse($data["ipB"]);
		$script = "interface " . $trunk . "." . $data["vlan"] . "\ndis th\n";
		$script .= "\n vlan-type dot1q " . $data["vlan"];
		$script .= "\n description [LNTIL-MA-CMNET-BAS02-YZLME60X]" . $trunk . "." . $data["vlan"] . "-[" . $desc . "]";
		$script .= "\n ip binding vpn-instance tlwsw";
		$script .= "\n ip address " . long2ip($ipB[2] + 1) . " " . long2ip($ipB[1]);
		$script .= "\n traffic-policy remarkdscp inbound";
		$script .= "\n statistic enable";
		$script .= "\nip route-static vpn-instance tlwsw " . long2ip($ip[2]) . " " . long2ip($ip[1]) . " " . long2ip($ipB[2] + 2) . "\r\n";
        /* the9312 */
		if ($data["neFactory"]) {
			$data["neFactory"] === '华为' && $down = "port_hw";
			$data["neFactory"] === '中兴' && $down = "port_zte";
		} else {
			return [
				"the93" => [
					"网元厂家未定义",
					""
				]
			];
		}
		$the9312 = "vlan " . $data["vlan"] . "\ndis th\n\n";
		$the9312 .= "description to-[" . $desc . "]\nq";
		$the9312 .= "\ninterface " . $device9312["up_port_yz"] . "\nport trunk allow-pass vlan " . $data["vlan"];
		$the9312 .= "\ninterface " . $device9312[$down] . "\nport trunk allow-pass vlan " . $data["vlan"] . "\nq\r\n";
		return [
			"bas02" => $script,
			"the93" => [
				$data["sw93"],
				$the9312
			]
		];
	}

	protected function generateZgWorkflow($ids = null)
	{
		$row = 5;
		$cellValues = [];
		$default = [
			"B" => "铁岭",
			"D" => "占用",
			"E" => "223.100.96.0/20",
			"F" => "集客专线",
			"G" => "互联网专线",
			"H" => "LNTIL-MA-CMNET-BAS02-YZME60X",
			"L" => "卜玉",
			"M" => 18841050815,
			"N" => "Auto import! --Xianda",
			"O" => "铁岭",
			"P" => "卜玉",
			"R" => "其他",
			"T" => "辽宁",
			"W" => "客户响应中心",
			"X" => "buyu.tl@ln.chinamobile.com",
			"AD" => "静态",
			"AH" => "已启用",
			"AI" => "铁岭柴河街局1号楼2层210综合机房"
		];
		foreach ($ids as $id) {
			foreach ($default as $k => $v) {
				$cellValues[$k . $row] = $v;
			}
			$data = Infotables::get($id)->toArray();
			$cellValues["C" . $row] = $data["ip"] . "/32"; // ip
			$cellValues["K" . $row] = $data["create_time"]; // 分配时间
			$cellValues["Q" . $row] = $data["cName"]; // 客户名
            /* 以下为选填 */
			$cellValues["I" . $row] = $data["cPerson"]; // 客户联系人
			$cellValues["J" . $row] = $data["cPhone"] + 0; // 客户电话
			$cellValues["U" . $row] = $data["cAddress"]; // 客户地址
			$cellValues["V" . $row] = $data["cEmail"]; // 客户邮箱
			$cellValues["AG" . $row] = $data["instanceId"] + 0; // 政企专线计费代号
			$row++;
		}
		$pFilename = './sampleData/zg_import.xls';
		$this->exportExcelFile($pFilename, 0, $cellValues, 'Xls', '资管流程-' . $data["cName"] . date("Ymd_His") . '.xls');
	}

	protected function generateJtIp($ids = null)
	{
		$row = 4;
		$cellValues = [];
		$default = [
			"D" => "其他",
			"F" => "企业",
			"G" => "辽宁",
			"H" => "铁岭",
			"Q" => "静态",
			"T" => "互联网专线",
			"U" => "占用",
			"V" => "已启用",
			"AA" => "铁岭移动客户响应中心",
			"AB" => "卜玉",
			"AC" => 18841050815,
			"AD" => "buyu.tl@ln.chinamobile.com"
		];
		foreach ($ids as $id) {
			foreach ($default as $k => $v) {
				$cellValues[$k . $row] = $v;
			}
			$data = Infotables::get($id)->toArray();
			$cellValues["B" . $row] = $data["ip"] . "/32"; // ip
			$cellValues["C" . $row] = $data["cName"]; // 客户名
			$cellValues["L" . $row] = $data["cAddress"]; // 客户地址
			$cellValues["M" . $row] = $data["cPerson"]; // 客户联系人
			$cellValues["N" . $row] = $data["cPhone"] + 0; // 客户电话
			$cellValues["O" . $row] = $data["cEmail"]; // 客户邮箱
			$cellValues["R" . $row] = $data["create_time"]; // 分配时间
			$row++;
		}
		$pFilename = './sampleData/ip_jt.xlsx';
		$this->exportExcelFile($pFilename, 1, $cellValues, 'Xlsx', '集团IP备案' . $data["cName"] . date("Ymd_His") . '.xlsx');
	}

	protected function generateGxbIp($ids = null)
	{
		$row = 2;
		$cellValues = [];
		$default = [
			"D" => "其他",
			"F" => "企业",
			"Q" => "静态"
		];
		foreach ($ids as $id) {
			foreach ($default as $k => $v) {
				$cellValues[$k . $row] = $v;
			}
			$data = Infotables::get($id)->toArray();
			$cellValues["A" . $row] = $data["ip"]; // ip
			$cellValues["B" . $row] = $data["ip"]; // ip
			$cellValues["C" . $row] = $data["cName"]; // 客户名
			$cellValues["L" . $row] = $data["cAddress"]; // 客户地址
			$cellValues["M" . $row] = $data["cPerson"]; // 客户联系人
			$cellValues["N" . $row] = $data["cPhone"] + 0; // 客户电话
			$cellValues["O" . $row] = $data["cEmail"]; // 客户邮箱
			$cellValues["R" . $row] = $data["create_time"]; // 分配时间
			$cellValues["F" . $row] = "企业";
			$cellValues["G" . $row] = isset($data["extra"]["province"]) ? $data["extra"]["province"] : "";
			$cellValues["H" . $row] = isset($data["extra"]["city"]) ? $data["extra"]["city"] : "";
			$row++;
		}
		$pFilename = './sampleData/ip_gxb.xls';
		$this->exportExcelFile($pFilename, 4, $cellValues, 'Xls', '工信部IP备案' . $data["cName"] . date("Ymd_His") . '.xls');
	}

	/**
	 * 导出到excel
	 *
	 * @param unknown $pFilename
	 *            模板文件地址
	 * @param unknown $workSheetIndex
	 *            工作表索引
	 * @param unknown $cellValues
	 *            数据
	 * @param unknown $writerType
	 *            输出类型 Xls or Xlsx
	 * @param unknown $fileName
	 *            输出文件名
	 */
	private function exportExcelFile($pFilename, $workSheetIndex, $cellValues, $writerType, $fileName, $writerMethod = "setCellValue")
	{
		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($pFilename);
		$worksheet = $spreadsheet->getSheet($workSheetIndex);
        // 编辑worksheet
		foreach ($cellValues as $d => $v) {
			$worksheet->$writerMethod($d, $v);
            // $worksheet->getCell ( $d )->setValue ( $v );
		}
        // 定义spreadsheet参数并输出到浏览器
		$spreadsheet->getProperties()->setCreator("Xianda");
		$spreadsheet->getProperties()->setLastModifiedBy('Xianda');
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, $writerType);
		if ($writerType == "Xls") {
			header('Content-Type: application/vnd.ms-excel'); // 告诉浏览器将要输出excel03文件
		} else {
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // 告诉浏览器数据excel07文件
		}
		header('Content-Disposition: attachment;filename="' . $fileName . '"'); // 告诉浏览器将输出文件的名称
		header('Cache-Control: max-age=0'); // 禁止缓存
		$writer->save("php://output");
		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);
		unset($writer);
	}

	public function _getDevice9312Info()
	{
		return config("device9312");
	}

	/**
	 * get:加载数据到handsontable并验证,
	 * post:上传,后台处理入库
	 * 1.默认post为新申请,移除ip、vlan信息再保存,
	 * 严格验证，不合规不许提交，标记status：0
	 * 2.带post参数type=import,视为旧信息导入,
	 * 生成ip表（全）和vlan表信息（不全）。直接入库，并标记tags:导入
	 * 3.为了新增字段不修改数据库，将新增字段用json保存到一列。
	 * 在csv_to_array时，需要获取额外的字段
	 *
	 * @return void|string|mixed|string
	 */
	public function _ht_apply()
	{
		$zxInfoTitle = [
			"label" => "zx_apply-new-rb",
			"order" => "0,1,2,3,4,5,6,7,8,9,10,12,13,14,15,16,17,18,19,29,30,31,32,33,34,35,36,37"
		];
		if (request()->isPost()) {
			$postData = input("post.data");
			$type = input("post.type");
			$dataHeader = $this->getHeader($zxInfoTitle["label"], $zxInfoTitle["order"], true);
            // 获取数据库的列名
			$dataHeader = explode(",", $dataHeader);
            // 根据列名和数据转成php数组
            // $postData = substr ( $postData, 3 ); // 莫名奇妙的前三个字节是垃圾数据。3天才研究出来，只能这样解决！！！(目前已在前端解决)
			$data = $this->csv_to_array($dataHeader, $postData);
            // return dump($data);
            // 获取额外的字段
			$extraHeader = config("extraInfo");
			foreach ($data as $k => $v) {
                // 清除空元素
				$data[$k] = array_filter($v);
				$temp = [];
				foreach ($extraHeader as $kk => $vv) {
					if (isset($data[$k][$vv])) {
						$data[$k]["extra"][$vv] = $data[$k][$vv];
						unset($data[$k][$vv]);
					}
				}
				if (isset($v["aStation"]) && $v["aStation"] == "柴河局") {
					$data[$k]["neFactory"] = isset($data[$k]["neFactory"]) ? $data[$k]["neFactory"] : "";
					$data[$k]["aStation"] .= "-" . $data[$k]["neFactory"];
				}
				$data[$k]["aDate"] = isset($data[$k]["aDate"]) ? $data[$k]["aDate"] : "";
				if ($this->checkDateIsValid($data[$k]["aDate"])) {
                    // 申请时间转存到 create_time
					$data[$k]["create_time"] = strtotime($data[$k]["aDate"]);
				}
			}
            // 若导入，ip/vlan信息要单独存储。
			$result = Infotables::createInfo($data, $type);
            // $result = $info->save($data[0]);
			return dump($result);
		}
		if (request()->isGet()) {
			if (input('?get.t')) {
				$aStation = array_keys(config("aStation"));
				$this->assign([
					"aStation" => implode(",", $aStation),
					'colHeaderData' => $this->getHeader($zxInfoTitle["label"], $zxInfoTitle["order"]),
					"colWidthsData" => $this->getColWidths($zxInfoTitle["order"])
				]);
				return $this->fetch("_ht_apply");
			}
		}
	}

	/**
	 * ip、vlan申请
	 *
	 * @return mixed|string
	 */
	public function import()
	{
        // post数据传给 _ht_apply()
		return $this->fetch();
	}

	/**
	 * 系统设置
	 *
	 * @return mixed|string
	 */
	public function settings()
	{
		if (request()->isGet()) {
			if (!strpos(request()->header("referer"), request()->action())) {
				session("settings_back_url", request()->header("referer"));
			}
			$lastIp = Iptables::ip_export(Db::table("phpweb_sysinfo")->where("label", "zx_apply-lastIP")->value("value"));
			$this->assign([
				"lastIp" => $lastIp
			]);
			return $this->fetch();
		} else if (request()->isPost()) {
			if (input("post.exec") == "ok_ip") {
				return Iptables::setLastIp(input("post.lastIpStr"));
			}
			if (input("post.exec") == "cal_ip") {
				$unusedIps = [];
                // todo
				return $this->result($unusedIps, 1);
			}
			if (input("post.exec") == "ok_vlan") {
				return Vlantables::importUsedVlan(input("post.device"), input("post.vlanImport"));
			}
		}
	}

	/**
	 * todo预分配时检查获取的数据与数据库中的ip/ipB是否重复
	 *
	 * @param unknown $info            
	 * @param unknown $data            
	 * @return void|NULL|unknown
	 */
	protected function checkAndSetIp($info, $data)
	{
		if ($info["ip"] != $data["ip"]) { // 获取的ip有变化，则检查是否冲突
			$ip = Iptables::check($data["ip"], "ip", $data["zxType"]);
			if ($ip)
				return $this->error("互联ip冲突，", null, $ip["cName"]);
		}
        // 设置ipMask
		$ip_array = Iptables::ip_parse($data["ip"]);
		$data["ip"] = $ip_array[2];
		$data["ipMask"] = $ip_array[1];
		if ($data["ipB"] == "") {
            // 设置 ipB为null
			$data["ipB"] = null;
			$data["ipBMask"] = null;
			return $data;
		}
		if ($info["ipB"] != $data["ipB"]) {
			$ipB = Iptables::check($data["ipB"], "ipB", $data["zxType"]);
			if ($ipB)
				return $this->error("业务ip冲突，", null, $ipB["cName"]);
		}
        // 设置ipBMask
		$ipB_array = Iptables::ip_parse($data["ipB"]);
        /* 若未提供ipBMask，默认强制设置ipBMask为-8 */
		$ipB_array[1] == -1 && $ipB_array = Iptables::ip_parse(Iptables::ip_export($ipB_array[0], -8));
        /* 修正ip为ip_start */
		$data["ipB"] = $ipB_array[2];
		$data["ipBMask"] = $ipB_array[1];
		return $data;
	}

	/**
	 * todo预分配时检查获取的vlan是否已分配，并记录
	 *
	 * @param unknown $data            
	 * @return unknown|void
	 */
	protected function checkAndSetVlan($data)
	{
		if ($data["vlan"] == "") {
			$data["vlan"] = null;
			return $data;
		}
		$vlan = Vlantables::check($data["zxType"], $data["aStation"], $data["vlan"]);
		if ($vlan && $vlan["id"] != $data["id"]) { // 找到vlan且vlan的id与自己的id不同
			return $this->error("vlan冲突，", null, $vlan["cName"]);
		} else {
			Vlantables::createVlan($data["aStation"], $data["vlan"], $data["cName"], $data["id"]);
			return $data;
		}
	}

	/**
	 * 校验日期格式是否正确
	 *
	 * @param string $date
	 *            日期
	 * @param string $formats
	 *            需要检验的格式数组
	 * @return boolean
	 */
	protected function checkDateIsValid($date, $formats = array("Y-m-d", "Y/m/d"))
	{
		$unixTime = strtotime($date);
		if (!$unixTime) { // strtotime转换不对，日期格式显然不对。
			return false;
		}
        // 校验日期的有效性，只要满足其中一个格式就OK
		foreach ($formats as $format) {
			if (date($format, $unixTime) == $date) {
				return true;
			}
		}
		return false;
	}

	private function cacheSettings()
	{
		$client = new \Redis();
		$client->connect('127.0.0.1', 6379);
		$pool = new \Cache\Adapter\Redis\RedisCachePool($client);
		$simpleCache = new \Cache\Bridge\SimpleCache\SimpleCacheBridge($pool);
		\PhpOffice\PhpSpreadsheet\Settings::setCache($simpleCache);
	}

	public function tt()
	{
		$data = 10 & 255;
		return dump($data);
	}
}