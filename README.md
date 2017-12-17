## Xianda小项目

基于ThinkPHP 5.0.x开发

目前模块有：

| 模块名       | 功能描述              |
| --------- | ----------------- |
| docs      | 开发文档库             |
| checkME60 | 城域网BAS设备健康度检查(完成) |
| common    | 公共库               |
| esserver  | excel服务器辅助(完成)    |
| index     | 首页                |
| kxemas    | 客响维材管理系统(终止开发)    |
| othermode | template模板        |
| trouble   | 办公终端报修流程(开发中)     |
| zx_apply  | 专线业务开通辅助(使用中)     |

本地 clone 后需要在本目录下执行一次 `composer install`

composer 的安装及使用请查看： [composer 中文网](http://www.phpcomposer.com/ )

> 另：`public/static/`内文件已打包，不保存在repo中。
> `docs/`内执行`xianda-build-sh.sh`可生成静态文档（需安装jekyll）

### 生成docs步骤

 1. 安装 rubyinstaller

   [官网下载](https://rubyinstaller.org/downloads/)安装

 2. 安装 jekyll

   `gem install jekyll`

 3. 安装 bundler

   在`/docs`里执行`gem install bundler`

4. 安装依赖

  在`/docs`里执行`bundle install`

5. 生成静态文档到`/public/docs`

  `jekyll b -d ../public`或者`./xianda-build-sh.sh`
