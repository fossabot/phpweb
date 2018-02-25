## Xianda小项目:bow:

[![Build Status](https://travis-ci.org/yuxianda/phpweb.svg?branch=master)](https://travis-ci.org/yuxianda/phpweb)

基于ThinkPHP 5.0.x开发

更新log可查看 [CHANGELOG](CHANGELOG.md)

目前模块有：

| 模块名       | 功能描述                                     |
| --------- | ---------------------------------------- |
| docs      | 开发文档库( *beta*) :fire:                    |
| checkME60 | 城域网BAS设备健康度检查(**done**) :heavy_check_mark: |
| common    | 公共库                                      |
| esserver  | excel服务器辅助(**done**) :heavy_check_mark:  |
| index     | 首页                                       |
| kxemas    | 客响维材管理系统(*shutdown)  :no_entry_sign:     |
| othermode | template模板                               |
| trouble   | 办公终端报修流程(*pause) :wrench:                |
| zx_apply  | 专线业务开通辅助(*alpha*) :fire:                 |

本地 clone 后需要在本目录下执行一次 `composer install`

composer 的安装及使用请查看： [composer 中文网](http://www.phpcomposer.com/ )

> 另：`public/static/`内文件已打包，不保存在repo中。详见[release](https://github.com/yuxianda/phpweb/releases)。
>
> `docs`需搭环境（*jekyll*）在`docs/`内执行`xianda-build-sh.sh`生成静态文档。

### 生成docs步骤

以windows系统为例，安装git-for-windows后，使用 git-bash，切换到`/docs`目录

 1. 安装 rubyinstaller

   [官网下载](https://rubyinstaller.org/downloads/)安装

 2. 安装 jekyll

   ```sh
   gem install jekyll
   ```
   安装出错请参考[这里](https://yuxianda.github.io/notes/create-blog-by-using-jekyll.html#%E5%AE%89%E8%A3%85jekyll)。

 3. 安装 bundler

   ```sh
   gem install bundler
   ```

4. 安装依赖

  ```sh
  bundle install
  ```

5. 生成静态文档到`/public/phpweb`

  ```sh
  ./xianda-build-sh.sh
  ```
