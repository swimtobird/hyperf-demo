# Swoole_tracker 如何跟踪与分析

## 进程列表

统计客户端fpm和cli的pid，可进行远程调试、查看调用栈以及进程CPU和内存占用统计
![img](https://img.kancloud.cn/41/f1/41f1a5bb4041f339e15c61614e820f71_3184x1682.png)

## 远程调试

对于以下四种工具，可以直接在服务端点击按钮远程开启后进行调试，无需修改代码，重启服务。开启后发生请求，日志自动上传服务端后台进行分析展示

> 对性能有所影响，开启调试完毕后请及时关闭。

### 阻塞检测

可详细看到阻塞的堆栈信息，执行耗时，系统调用信息

![img](https://img.kancloud.cn/b1/75/b1751c80bd114d359b8142e823fa2edd_4920x2100.png)

### 内存泄漏

> 会获取到存在内存泄漏的文件堆栈信息，说明你需要在代码逻辑执行完毕后将该键名 `unset` 掉，就可以解决内存泄漏问题

![img](https://img.kancloud.cn/be/af/beaf3926da81d7800ed26f04223b3b51_4938x1236.png)

### 性能分析

性能分析可以生成分层分析表、调用图和火焰图，都可以直观的找到对应的瓶颈所在

![img](https://img.kancloud.cn/2f/7e/2f7e81e4d4594e536909b45598001680_4952x1870.png)

#### 分层分析表

![img](https://img.kancloud.cn/af/a9/afa9c8f1813bbd8d0e6bfeba8068b0a7_4924x2602.png)

#### 调用图

![img](https://img.kancloud.cn/fe/19/fe193b0b2eef5c66e934ec47a44c74fa_3560x1446.png)

#### 火焰图

![img](https://img.kancloud.cn/61/97/61976a466d36ae8a56269340584011db_2398x240.png)