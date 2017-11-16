-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        10.1.21-MariaDB - mariadb.org binary distribution
-- 服务器操作系统:                      Win32
-- HeidiSQL 版本:                  9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出  表 tlxda.phpweb_sysinfo 结构
CREATE TABLE IF NOT EXISTS `phpweb_sysinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '序号',
  `label` varchar(50) DEFAULT NULL COMMENT '标签',
  `value` varchar(50) DEFAULT NULL COMMENT '值',
  `option` varchar(50) DEFAULT NULL COMMENT '选项',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- 正在导出表  tlxda.phpweb_sysinfo 的数据：~3 rows (大约)
/*!40000 ALTER TABLE `phpweb_sysinfo` DISABLE KEYS */;
INSERT INTO `phpweb_sysinfo` (`id`, `label`, `value`, `option`) VALUES
	(1, 'version', 'V1.0328', NULL),
	(2, 'troubleDept', '网络部', '传输中心网，络优化中心，无线动力中心，客户响应中心'),
	(3, 'troubleDept', '财务部', '203，204，205');
/*!40000 ALTER TABLE `phpweb_sysinfo` ENABLE KEYS */;

-- 导出  表 tlxda.trouble_check 结构
CREATE TABLE IF NOT EXISTS `trouble_check` (
  `code` int(4) DEFAULT NULL COMMENT '验证码',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '生成时间',
  `loginName` varchar(30) DEFAULT NULL COMMENT '登陆名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='验证码记录表';

-- 正在导出表  tlxda.trouble_check 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `trouble_check` DISABLE KEYS */;
INSERT INTO `trouble_check` (`code`, `time`, `loginName`) VALUES
	(123, '2017-07-16 22:23:42', 'yuxianda.tl');
/*!40000 ALTER TABLE `trouble_check` ENABLE KEYS */;

-- 导出  表 tlxda.trouble_forms 结构
CREATE TABLE IF NOT EXISTS `trouble_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `createTime` datetime DEFAULT NULL COMMENT '申请时间',
  `applicant` varchar(15) DEFAULT NULL COMMENT '申请人',
  `emailAddr` varchar(50) DEFAULT NULL COMMENT '申请人邮箱',
  `dept` varchar(36) DEFAULT NULL COMMENT '申请部门',
  `dept2` varchar(36) DEFAULT NULL COMMENT '所在中心',
  `troubleType` varchar(12) DEFAULT NULL COMMENT '故障类型',
  `troubleDescrition` varchar(150) DEFAULT NULL COMMENT '故障现象',
  `applicationApproval` varchar(30) DEFAULT NULL COMMENT '申请部门审批意见',
  `applicationApprovalTime` datetime DEFAULT NULL COMMENT '申请审批时间',
  `approvalOpinion` varchar(30) DEFAULT NULL COMMENT '客响中心领导审批意见',
  `approvalTime` datetime DEFAULT NULL COMMENT '客响审批时间',
  `receiver` varchar(30) DEFAULT NULL COMMENT '派单指定受理人',
  `dispatchTime` datetime DEFAULT NULL COMMENT '派单时间',
  `acceptanceTime` datetime DEFAULT NULL COMMENT '代维受理时间',
  `results` varchar(60) DEFAULT NULL COMMENT '处理结果',
  `marks` int(1) DEFAULT NULL COMMENT '申请人确认打分',
  `managerConfirm` varchar(30) DEFAULT NULL COMMENT '派单人确认处理结果',
  `logs` varchar(500) DEFAULT NULL COMMENT '操作记录',
  `status` int(2) DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='故障报修单';

-- 正在导出表  tlxda.trouble_forms 的数据：~7 rows (大约)
/*!40000 ALTER TABLE `trouble_forms` DISABLE KEYS */;
INSERT INTO `trouble_forms` (`id`, `createTime`, `applicant`, `emailAddr`, `dept`, `dept2`, `troubleType`, `troubleDescrition`, `applicationApproval`, `applicationApprovalTime`, `approvalOpinion`, `approvalTime`, `receiver`, `dispatchTime`, `acceptanceTime`, `results`, `marks`, `managerConfirm`, `logs`, `status`) VALUES
	(1, '2017-06-01 16:53:00', '于显达', 'yuxianda.tl@ln.chinamobile.com', '网络部', '客户响应中心', '软件故障', '无法登录4a，显示空白，无响应', '同意', '2017-06-01 17:09:00', '同意', '2017-06-01 17:20:00', '李钰', '2017-06-01 17:22:00', '2017-06-01 17:25:00', '已解决', 5, '已确认', '主任 马宏英 已同意，2017-6-10 17:19:35；客响领导 马宏英 已同意，2017-6-10 17:37:45；派单人 刘勇 已派单，2017-6-10 17:38:37；代维 李钰 已受理，2017-6-10 17:39:40；代维 李钰 处理完毕，2017-6-10 17:39:40；申请人 于显达 已确认，2017-6-10 17:42:31；派单人已确认，2017-6-10 17:42:44；', 2),
	(2, '2017-06-02 16:53:00', '于显达', 'yuxianda.tl@ln.chinamobile.com', '网络部', '客户响应中心', '软件故障', '资管系统异常退出。无法使用', '同意', '2017-06-02 17:09:00', '同意', '2017-06-02 17:20:00', '李钰', '2017-06-02 17:22:00', '2017-06-02 17:25:00', '已解决', 5, '已确认', '主任 马宏英 已同意，2017-6-10 17:19:35；客响领导 马宏英 已同意，2017-6-10 17:37:45；派单人 刘勇 已派单，2017-6-10 17:38:37；代维 李钰 已受理，2017-6-10 17:39:40；代维 李钰 处理完毕，2017-6-10 17:39:40；申请人 于显达 已确认，2017-6-10 17:42:31；派单人已确认，2017-6-10 17:42:44；', -1),
	(3, '2017-06-03 16:53:00', '郭晓晨', 'guoxiaochen.tl@ln.chinamobile.com', '财务部', '财务部', '硬件故障', '开机蓝屏', '同意', '2017-06-03 17:09:00', '同意', '2017-06-03 17:20:00', '李钰', '2017-06-03 17:22:00', '2017-06-03 17:25:00', '已解决', 5, '已确认', '经理 王明东 已同意，2017-6-10 17:19:35；客响领导 马宏英 已同意，2017-6-10 17:37:45；派单人 刘勇 已派单，2017-6-10 17:38:37；代维 李钰 已受理，2017-6-10 17:39:40；代维 李钰 处理完毕，2017-6-10 17:39:40；申请人 于显达 已确认，2017-6-10 17:42:31；派单人已确认，2017-6-10 17:42:44；', -1),
	(4, '2017-06-04 16:53:00', '于显达', 'yuxianda.tl@ln.chinamobile.com', '网络部', '客户响应中心', '软件故障', '无法登录4a，显示空白，无响应', '同意', '2017-06-04 17:09:00', '同意', '2017-06-04 17:20:00', '李钰', '2017-06-04 17:22:00', '2017-06-04 17:25:00', '已解决', 5, '已确认', '主任 马宏英 已同意，2017-6-10 17:19:35；客响领导 马宏英 已同意，2017-6-10 17:37:45；派单人 刘勇 已派单，2017-6-10 17:38:37；代维 李钰 已受理，2017-6-10 17:39:40；代维 李钰 处理完毕，2017-6-10 17:39:40；申请人 于显达 已确认，2017-6-10 17:42:31；派单人已确认，2017-6-10 17:42:44；', -1),
	(5, '2017-06-05 16:54:00', '于显达', 'yuxianda.tl@ln.chinamobile.com', '网络部', '客户响应中心', '软件故障', '资管系统异常退出。无法使用', '同意', '2017-06-05 17:09:00', '同意', '2017-06-05 17:20:00', '李钰', '2017-06-05 17:22:00', '2017-06-05 17:25:00', '已解决', 5, '已确认', '主任 马宏英 已同意，2017-6-10 17:19:35；客响领导 马宏英 已同意，2017-6-10 17:37:45；派单人 刘勇 已派单，2017-6-10 17:38:37；代维 李钰 已受理，2017-6-10 17:39:40；代维 李钰 处理完毕，2017-6-10 17:39:40；申请人 于显达 已确认，2017-6-10 17:42:31；派单人已确认，2017-6-10 17:42:44；', -1),
	(6, '2017-06-06 16:54:00', '郭晓晨', 'guoxiaochen.tl@ln.chinamobile.com', '财务部', '财务部', '硬件故障', '开机蓝屏', '同意', '2017-06-06 17:09:00', '同意', '2017-06-06 17:20:00', '李钰', '2017-06-06 17:22:00', '2017-06-06 17:25:00', '已解决', 5, '已确认', '经理 王明东 已同意，2017-6-10 17:19:35；客响领导 马宏英 已同意，2017-6-10 17:37:45；派单人 刘勇 已派单，2017-6-10 17:38:37；代维 李钰 已受理，2017-6-10 17:39:40；代维 李钰 处理完毕，2017-6-10 17:39:40；申请人 于显达 已确认，2017-6-10 17:42:31；派单人已确认，2017-6-10 17:42:44；', -1),
	(7, '2017-06-07 16:54:00', '于显达', 'yuxianda.tl@ln.chinamobile.com', '网络部', '客户响应中心', '软件故障', '资管系统异常退出。无法使用', '同意', '2017-06-07 17:09:00', '同意', '2017-06-07 17:20:00', '李钰', '2017-06-07 17:22:00', '2017-06-07 17:25:00', '已解决', 5, '已确认', '主任 马宏英 已同意，2017-6-10 17:19:35；客响领导 马宏英 已同意，2017-6-10 17:37:45；派单人 刘勇 已派单，2017-6-10 17:38:37；代维 李钰 已受理，2017-6-10 17:39:40；代维 李钰 处理完毕，2017-6-10 17:39:40；申请人 于显达 已确认，2017-6-10 17:42:31；派单人已确认，2017-6-10 17:42:44；', 5);
/*!40000 ALTER TABLE `trouble_forms` ENABLE KEYS */;

-- 导出  表 tlxda.trouble_user 结构
CREATE TABLE IF NOT EXISTS `trouble_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `UserLogin` varchar(30) DEFAULT NULL COMMENT '登录名',
  `UserName` varchar(15) DEFAULT NULL COMMENT '用户名',
  `UserDept2` varchar(45) DEFAULT NULL COMMENT '中心',
  `UserDept` varchar(15) DEFAULT NULL COMMENT '部门',
  `MobilePhone` int(6) DEFAULT NULL COMMENT '电话',
  `Email` varchar(50) DEFAULT NULL COMMENT '邮箱',
  `UserRole` int(1) DEFAULT NULL COMMENT '角色',
  `wxId` varchar(32) DEFAULT NULL COMMENT '微信ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='内置用户表';

-- 正在导出表  tlxda.trouble_user 的数据：~2 rows (大约)
/*!40000 ALTER TABLE `trouble_user` DISABLE KEYS */;
INSERT INTO `trouble_user` (`id`, `UserLogin`, `UserName`, `UserDept2`, `UserDept`, `MobilePhone`, `Email`, `UserRole`, `wxId`) VALUES
	(1, 'liuyong', '刘勇', '客户响应中心', '网络部', 610673, 'liuyongtl.tl@ln.chinamobile.com', 3, NULL),
	(2, 'liyu', '李钰', '', '', 660836, 'liuyongtl.tl@ln.chinamobile.com', 4, NULL),
	(3, 'yuxianda', '于显达', '客户响应中心', '网络部', 610671, 'yyyyyy', 1, NULL);
/*!40000 ALTER TABLE `trouble_user` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
