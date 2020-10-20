# 声明

该项目是基于hyperf编写的demo项目，仅供参考

# 系统要求

- PHP >= 7.2
- Swoole PHP 扩展 >= 4.4 并且关闭 `Short Name`
- OpenSSL PHP 扩展
- JSON PHP 拓展
- PDO PHP 拓展
- Redis PHP 拓展

# 安装

获取代码
`git clone https://github.com/swimtobird/hyperf-demo.git`

执行数据库迁移
`php bin/hyperf.php migrate`

安装组件
`composer install -vvv`

启动
`php bin/hyperf.php server:watch`

# 文档
- [hyperf安装](./doc/Hyperf_install.md)
- [hyperf实践](./doc/Hyperf_practice.md)
- [Swoole_tracer使用](./doc/Swoole_tracker_user.md)