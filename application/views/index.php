<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>IP抓包程序</title>
<meta name="description" content="" />
<meta name="keywords" content="" />
<script type="text/javascript" src="<?php echo JS_PATH ?>jquery.min.js"></script>
<script type="text/javascript" src="<?php echo JS_PATH ?>startip<?php echo $step ?>.js"></script>
<script type="text/javascript">
	var Ijibu = {};

	//获取cookie
	Ijibu.getCookie = function(name) {
		name = name.replace(/([\.\[\]\$])/g, "\\$1");
		var rep = new RegExp(name + "=([^;]*)?;", "i");
		var co = document.cookie + ";";
		var res = co.match(rep);
		if (res) {
			return res[1] || ""
		} else {
			return ""
		}
	};
	
	//设置cookie
	Ijibu.setCookie = function(name, value, expire, path, domain, secure) {
		var cstr = [];
		cstr.push(name + "=" + escape(value));
		if (expire) {
			var dd = new Date();
			var expires = dd.getTime() + expire * 3600000;
			dd.setTime(expires);
			cstr.push("expires=" + dd.toGMTString())
		}
		if (path) {
			cstr.push("path=" + path)
		}
		if (domain) {
			cstr.push("domain=" + domain)
		}
		if (secure) {
			cstr.push(secure)
		}
		document.cookie = cstr.join(";")
	};
	
	//删除cookie
	Ijibu.deleteCookie = function(name) {
		document.cookie = name + "=;expires=Fri, 31 Dec 1999 23:59:59 GMT;"
	};

	var ipCount = ips.length, i = parseInt(Ijibu.getCookie('getLen'));
	if (isNaN(i)) {
		i = 0;
	}
	
	/**
	 * 获取IP地址，每次获取5个，先不考虑并行出错的情况。
	 */
	function getIp() {
		for (var j = 0; j < 1; j++) {
			if (i >= ipCount) {
				alert('执行完毕');
				clearInterval(getIpInt);
			}
			var ip = ips[i], url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' + ip;
			
			$.getScript(url, function(){
				if (typeof remote_ip_info != 'undefined') {
					if (remote_ip_info.ret == 1) {
						remote_ip_info.ip = ip;
						$.ajax({
							type: "POST",
							url: '/main/syncIpName',
							data: remote_ip_info,
							dataType: "json",
							success: function() {
								//clearInterval(getIpInt);
							},
							error: function() {
								alert('系统错误，请稍后再试');
								setIpErrors(ip, 2);
								clearInterval(getIpInt);
							}
						});
					}
				} else {
					setIpErrors(ip, 2);
					clearInterval(getIpInt);
				}
			}).fail(function(jqxhr, settings, exception) {
				setIpErrors(ip, 1);
			});	
		}

		i++;
		Ijibu.setCookie('getLen', i, 24 * 3, '/');
		$('#ipCount').html(i);
	}
	
	/**
	 * 记错获取IP错误的记录
	 *	type:1为新浪错误，2为云更新错误	
	 */
	function setIpErrors(ip, type) {
		var errors = Ijibu.getCookie('errors');
		errors = errors + ',' + ip + '-' + type;
		Ijibu.setCookie('errors', errors, 24 * 3);
	}
	
	/**
	 * 发送获取IP地址错误的记录
	 */
	function sendErrors() {
		var errors = Ijibu.getCookie('errors');
		if (errors) {
			$.ajax({
				type: "POST",
				url: '/main/getIpErrors',
				data: {errorIp: errors},
				dataType: "json",
				success: function() {
					Ijibu.setCookie('errors', '');
					//clearInterval(sendErrorInt);
				},
				error: function() {
					//clearInterval(sendErrorInt);
				}
			});
		}
	}
	
	var getIpInt = setInterval('getIp()', 5000), sendErrorInt = sendErrors('sendErrors()', 250000);
</script>
</head>

<body>
已经抓包：<div id="ipCount">
0
</div>条。
</body>
</html>