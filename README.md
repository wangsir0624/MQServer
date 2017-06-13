# MQServer
PHP写的消息队列服务器，可以用来异步执行一些任务，比如发送邮件，短信之类的

### 安装
需要通过composer包来进行安装
<pre>composer create-project wangjian/mqserver mqserver *</pre>
输入上面的命令，就可以下载到mqserver目录下

### 启动
启动命令格式为
<pre>php index.php [options] ip port</pre>
可选参数有两个，q表示服务器可以创建的最大队列数目，默认为10。服务器会定期清理不活跃的连接，如果客户端在max_time秒内没有任何动作，那么服务器会主动关闭这个连接，max_time可以通过t参数来设置，这个参数的默认值为600秒。
例如，输入如下命令
<pre>php index.php -q 20 -t 600 127.0.0.1 3000</pre>
表示队列服务器监听127.0.0.1:3000，服务器最大队列数目为20, 连接超过600秒没有动作，则会被服务器自动关闭。

### 与服务器交互
[使用我编写的wangjian/mqclient库与服务器交互](https://github.com/wangsir0624/MQClient/)

