# Hyperf安装

## 安装swoole[若安装请跳过]

没有安装的，可以按照[swoole官方安装说明](https://wiki.swoole.com/#/environment)指示步骤安装即可

## 配置PHP

先定位php.ini位置

```sh
[root@iZwz9dkdcnxsz2ul6p6n7iZ ~]# php --ini
Configuration File (php.ini) Path: /usr/local/php/lib
Loaded Configuration File:         /usr/local/php/lib/php.ini
Scan for additional .ini files in: (none)
Additional .ini files parsed:      (none)
```

然后在php.ini添加swoole.use_shortname = off

```sh
[root@iZwz9dkdcnxsz2ul6p6n7iZ ~]cat > /usr/local/php/lib/php.ini << \EOF
swoole.use_shortname = off
EOF
```

## 创建Hyperf项目

```php
#  通过脚手架安装
composer create-project hyperf/hyperf-skeleton  -vvv
# 进入安装好的 Hyperf 项目目录
cd hyperf-skeleton
# 启动 Hyperf
php bin/hyperf.php start
```

至此，安装完成，开始愉快编码了

## 优雅调试

首先安装 Whoops

```php
composer require --dev filp/whoops
```

然后配置 Whoops 专用异常处理器。

```php
// config/autoload/exceptions.php
return [
    'handler' => [
        'http' => [
            \Hyperf\ExceptionHandler\Handler\WhoopsExceptionHandler::class,
        ],    
    ],
];
```

修改APP异常处理器处理，添加环境判断

```php
// app/Exception/Handler/AppExceptionHandler

    public function isValid(Throwable $throwable): bool
    {
        //return true;
        /**
         * 当环境是生产环境时候，该处理再启动
         */
        return env('APP_ENV') === 'prod';
    }
```

最终效果：

![iShot2020-10-1611.16.31](/Users/luyonghong/Documents/同尚/iShot2020-10-1611.16.31.png)

## 热更新

> 热更新，顾名思义就是文件被修改后立马重启，不需要重新执行命令，提高开发效率必备
>
> 请勿于生产环境使用，切记！切记！切记！重要事情说三次

首先安装 Watcher

```php
composer require hyperf/watcher --dev
```

然后如果是Mac系统

```bash
brew install fswatchCopy to clipboardErrorCopied
```

如果是其他unix、linux系统

```bash
wget https://github.com/emcrisostomo/fswatch/releases/download/1.14.0/fswatch-1.14.0.tar.gz \
&& tar -xf fswatch-1.14.0.tar.gz \
&& cd fswatch-1.14.0/ \
&& ./configure \
&& make \
&& make install
```

最后将启动命令运行起来

```php
php bin/hyperf.php server:watch
```

~~php bin/hyperf.php start~~

补充：

- 删除文件需要手动重启才能生效。
- vendor 中的文件需要使用 classmap 形式自动加载才能被扫描。（即执行`composer dump-autoload -o`)

