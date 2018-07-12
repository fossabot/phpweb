---
layout:         post
title:          为php安装redis扩展-windows系统
date:           2018-01-25 11:21:32--0800
summary:       在windows中，不同的php版本对应不同的redis扩展版本，安装配置略繁琐，记录一下。
categories:     notes
update_date:    2018-07-11 17:27:55
---

## 0. 安装redis

可以访问redis的[官网](https://redis.io/)，但是官方只提供linux版本的redis。windows版本可以下载由Microsoft Open Tech group 编译好的版本 <https://github.com/MSOpenTech/redis>，不过现在貌似改名了，直接跳转到<https://github.com/MicrosoftArchive/redis/>，直接在[releases](https://github.com/MicrosoftArchive/redis/releases)页面下载即可。下载msi包，安装时可直接安装为windows服务并设置环境变量。很方便。

目前，最新版本一直是[3.2.100Pre-release](https://github.com/MicrosoftArchive/redis/releases/download/win-3.2.100/Redis-x64-3.2.100.msi)。最新稳定版是[3.0.504](https://github.com/MicrosoftArchive/redis/releases/download/win-3.0.504/Redis-x64-3.0.504.msi)。

在cmd执行测试，执行`redis-cli -v`查看版本。或：

```sh
C:\Users\Administrator>redis-cli
127.0.0.1:6379> ping
PONG
```

### 1. 确认php版本

在php安装目录执行`php -v`。或者在php里执行`phpinfo()`。windows 版的php为Thread Safe（ts）。

```sh
C:\Users\Administrator>php -r "phpinfo();">c:\phpinfo.txt
```

上面代码将phpinfo输出到`c:\phpinfo.txt`，可在不架设web的情况下快速查看phpinfo。

### 2. 下载DLL（重点！）

#### 2.1 php_igbinary.dll

访问：<http://windows.php.net/downloads/pecl/releases/igbinary/>，可看见不同的版本，依据版本优先选`a.b.c`中基于`a.b`，选`c`最大的文件夹进入。例如为选择`2.0.x`，当前最新的是`2.0.7`，进入后可看见支持从php5.6到7.2。下载对应版本即可。（选ts、线程安全版本）

| php版本 | 文件夹 | 下载文件                                                     |
| ------- | ------ | ------------------------------------------------------------ |
| 5.6     | 2.0.7  | [php_igbinary-2.0.7-5.6-ts-vc11-x86.zip](https://windows.php.net/downloads/pecl/releases/igbinary/2.0.7/php_igbinary-2.0.7-5.6-ts-vc11-x86.zip) |
| 7.2     | 2.0.7  | [php_igbinary-2.0.7-7.2-ts-vc15-x86.zip](https://windows.php.net/downloads/pecl/releases/igbinary/2.0.7/php_igbinary-2.0.7-7.2-ts-vc15-x86.zip) |

下载的zip包里有俩文件是我们需要的：`php_igbinary.dll`、`php_igbinary.pdb`（可选）。将它们复制到php的ext目录下。

#### 2.2 php_redis.dll

访问：<http://pecl.php.net/package/redis>，可看见Downloads下面有DLL的下载链接，根据`Version`点击`DLL`查看支持的php版本，如点击`2.2.7`后面的DLL，可看见这个版本的DLL支持PHP5.3到PHP5.6。

| php版本 | Version         | 下载文件                                                     |
| ------- | --------------- | ------------------------------------------------------------ |
| 5.6     | 2.2.7           | [5.6 Thread Safe (TS) x86](https://windows.php.net/downloads/pecl/releases/redis/2.2.7/php_redis-2.2.7-5.6-ts-vc11-x86.zip) |
| 7.2     | 4.1.0(当前最新) | [7.2 Thread Safe (TS) x86](https://windows.php.net/downloads/pecl/releases/redis/4.1.0/php_redis-4.1.0-7.2-ts-vc15-x86.zip) |

下载的zip包里有俩文件是我们需要的：`php_redis.dll`、`php_redis.pdb`（可选）。将它们复制到php的ext目录下。

### 3. 配置php.ini

在php安装目录执行`php --ini`，可查看当前加载的php.ini所在位置。在php.ini中添加：

```ini
extension=php_igbinary.dll
extension=php_redis.dll
```

注意`php_igbinary.dll`要放在`php_redis.dll`前面。

### 4. 测试

重启apache。可以在`phpinfo()`中看见redis的配置。

```
php -r "phpinfo();">c:\phpinfo.txt
```

可在`c:\phpinfo.txt`看见如下字样：（用notepad++打开。自带的notepad无换行。）

```
redis

Redis Support => enabled
Redis Version => 2.2.7
```

或写段php代码测试

```php
<?php
//连接本地的 Redis 服务
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
echo "Connection to server sucessfully";
//设置 redis 字符串数据
$redis->set("tutorial-name", "Redis tutorial");
// 获取存储的数据并输出
echo "Stored string in redis:: " . $redis->get("tutorial-name");
?>
```

