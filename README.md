# MQServer
PHP写的消息队列服务器，可以用来异步执行一些任务，比如发送邮件，短信之类的

### 安装
需要通过composer包来进行安装
<pre>composer create-project wangjian/mqserver mqserver *</pre>
输入上面的命令，就可以下载到mqserver目录下

### 启动
启动命令格式为
<pre>php index.php [options] ip port</pre>
启动参数：
p    指定服务器日志路径，此日志用来记录服务器接收到的指令，此日志在服务器重新启动时恢复之前的数据。此选项的默认值为./data，即在当前目录下的data文件中存储日志信息。
例如，输入
<pre>php index.php 127.0.0.1 3000</pre>
即可在127.0.0.1:3000端口监听客户端请求，并将日志写入到当前目录下的data文件中。请勿删除此data文件，否则可能会引起数据丢失。

### 持久化
服务器会把每次接收到的命令记录到日志文件中，在下一次启动时，服务器可以重放此日志来实现数据恢复。服务器每次启动时，会对此日志进行压缩。

### 与服务器交互
[使用我编写的wangjian/mqclient库与服务器交互](https://github.com/wangsir0624/MQClient/)

