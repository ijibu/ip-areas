ip-areas
========

以纯真IP库为基础，同步新浪IP地址查询接口数据到本地，以后做离线IP查询用

1.首先去下载最新的纯真IP库。本程序使用的是纯真2013-8-25日的库，IP数据记录：445348条，数据库大小：9M。由于IP库比较大，就不包含在git库中了。需要的可以联系鄙人。(@ijibu)

2.下载下来的IP库为.dat格式，为了程序上实现方便，将其解压为`ip.txt`文件，格式如下：
    
    1.24.236.0      1.24.239.255    内蒙古乌兰察布市 联通
    
    1.24.244.0      1.24.247.255    内蒙古乌海市 联通
	
    将文件放在application/cache/目录下，后面的程序将会使用。

3.php处理上面的解压文件，然后把每一行的第一个IP获取出来，保存成一个新的文件`uniquestartip.ini`，格式如下：
    
    61.138.100.101
    
    61.138.100.102
    
    61.138.100.130
    
    61.138.100.131
    
    61.138.100.134
	
	在本代码库中，调用：/main/parseIpTxt 方法即可实现。
	
4.将`uniquestartip.ini`入库
	
    在本代码库中，调用：/main/importIp 方法

    将会在application/cache/目录下生成 insertip1.sql文件，将该文件导入mysql即可，已做了插入优化。

5.本来在后台用PHP的curl模拟浏览器，伪造代理和IP去循环调用新浪IP查询接口，结果查询几千条后就不返回查询结果了。由于工作急迫，没有花时间去研究curl，遂采用下面的方式来实现。采用JS去循环调用新浪IP查询接口，然后再把查询结果的数据提交到后台入库。
    处理`startip.ini`(方法其实就是批量替换)，处理成js的数组形式，这样好在JS中进行循环，保存在`statip.js`,内容如下：
    
    var ips = ['61.138.100.131', '61.138.100.134'];
	
    本代码库为了考虑低端浏览器，保存为多个js文件，每个文件50000个IP。会在application/cache/目录下生成多个statip[n].js，拷贝到static/js/目录下面即可。

    在本代码库中，调用：/main/segmentIp2js 方法

5.最终在前段引入`startip.js`文件，然后即可。

6.执行main/index方法，就可以开始跑了。可以去视图中设置每秒中抓几个IP，根据自己的网络设置吧。鄙人比较胆小，也怕太频繁了会被新浪封锁，所以默认每秒2次在跑。

    由于鄙人参与开源项目少，所以估计文档和思路会让人不方便上手，多多原谅。
    
Acknowledgements
----------------

© 2013, Ijibu. Released under the [MIT License](License.md).
	
**ip-areas** is authored and maintained by [ijibu][rsc].

 * [Weibo](http://weibo.com/ijibu) (@ijibu)
 * [Github](http://github.com/ijibu) (@ijibu)
 * [Blog](http://blog.csdn.net/ijibu) (http://blog.csdn.net/ijibu)

[rsc]: http://weibo.com/ijibu