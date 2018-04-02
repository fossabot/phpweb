

### v0.4.0(2018-04-02)

- `Infotables`增加 ip掩码和ipB掩码，互联网ip掩码默认32，其他默认30，所有ipB掩码默认29
- 取消`Iptables`数据表，保留其model类及方法
- 定位为辅助平台，提供有限的台账维护功能。主要功能为限制数据录入的规范性。导出为各项工作所需的数据。包含但不限于：
  - 台账格式
  - 资管流程模板格式
  - 做数据脚本
  - ip备案模板格式（2种）
- 管理员查询页面可右键调用数据制作脚本生成、资管系统录入模板、集团IP备案导入模板、工信部IP备案导入模板功能。
- 更新`handsontable`组件到v1.18.1/v0.38.1

### v0.3.2(2018-01-31)

- 更新`dhtmlx`组件到5.1.0
- 修复excel服务器账号密码找回bug
- 添加系统log记录功能
- zx_apply模块需登陆后使用

### v0.2.2.0108(2018-01-08)

- 补充`/docs/_posts/2017-12-21-handsontable-usage-notes.md`内容

> 总结的自己脑袋都乱了。官网这个[docs](https://docs.handsontable.com)真的一点也不友好。

### v0.2.2.0107(2018-01-07)

- 补充`/docs/_posts/2017-12-21-handsontable-usage-notes.md`内容
- 使用`handsontable`作为grid组件。与`dhtmlXGrid`相比的优点：
  - 有配置默认值，上手更简单
  - UI更接近Excel，功能也更近似Excel
  - 可全局搜索，性能良好

### v0.2.2(2017-12-22)

- 修改docs入口为项目名，可在github pages自动发布
- 静态资源引入handsontable
- 改进docs开发文档样式，修复几处bug

### v0.2.0 (2017-12-18)

- 新增docs，记录开发文档
- 大量微调整

### v0.1.0 (2017-11-16)

开始托管于github

- initial release