<!doctype html>
<html lang="en">

<!-- 

  借地写说明：
  jquery-ui的说明参考：http://www.runoob.com/jqueryui/jqueryui-tutorial.html
  jquery的说明参考：http://www.w3school.com.cn/jquery/index.asp
  
  tabs-api为横向的标签，下面定义的div比如tabs-purchase是竖向的标签，按已有的往下添加，名字别重复就行。
  
  新增横向标签：
  1. <div id="tabs-api"><ul><li>下面新加个a标签，指向一个锚点。
  2. 上一条的<ul>同级别下新加一个<div>，id使用上一条锚点指定的id。
  
  新增纵向标签：
  1. js加一行，设置纵向标签的参数。
  2. 总之参考已有的样例吧。
  
-->

<head>
  <meta charset="utf-8">
  <title>代付示例</title>
  <link rel="stylesheet" href="static/jquery-ui.min.css">
  <script src="static/jquery-1.11.2.min.js"></script>
  <script src="static/jquery-ui.min.js"></script>
  <script src="static/demo.js"></script>
  <script>
  	$(function() {
       setApiDemoTabs("#tabs-df");
       setApiDemoTabs("#tabs-df-batch");
	  });
  </script>
  <link rel="stylesheet" href="static/demo.css">

</head>
<body style="background-color:#e5eecc;">
<div id="wrapper">

<div id="header">
<h2>代付产品示例</h2>

</div>

<div id="tabs-api">
  <ul>
    <li><a href="#tabs-api-1">前言</a></li>
    <li><a href="#tabs-api-2">代付样例</a></li>
    <li><a href="#tabs-api-3">批量代付样例</a></li>
    <li><a href="#tabs-api-4">常见开发问题</a></li>
  </ul>
  
  <div id="tabs-api-1">
    <?php include 'pages/api_10_df/introduction.php';?>
  </div>
  
  <div id="tabs-api-4">
    <?php include 'pages/dev_faq.php';?>
  </div>
  
  <div id="tabs-api-2">
	<div id="tabs-df">
	  	<ul>
	    <li><a href="#tabs-df-1">说明</a></li>
	    <li><a href="pages/api_10_df/df.php">代付</a></li>
	    <li><a href="pages/api_10_df/query.php">交易状态查询</a></li>
		<li><a href="pages/api_10_df/file_transfer.php">对账文件下载</a></li>
        <!-- 一般不会给商户开权限，所以默认不显示
	    <li><a href="pages/api_10_df/front_auth.php">实名认证（前台）</a></li>
	    <li><a href="pages/api_10_df/back_auth.php">实名认证（后台）</a></li>
        -->
	  </ul>
	  <div id="tabs-df-1">
	     <?php include 'pages/api_10_df/df_intro.html';?>
	  </div>
	</div>
  </div>
  
  <div id="tabs-api-3">
	  <div id="tabs-df-batch">
		  <ul>
		    <li><a href="#tabs-df-batch-1">说明</a></li>
		    <li><a href="pages/api_10_df/batch.php">批量代付</a></li>
		    <li><a href="pages/api_10_df/batch_query.php">批量代付查询</a></li>
		    <li><a href="pages/api_10_df/file_transfer.php">对账文件下载</a></li>
		  </ul>
		  <div id="tabs-df-batch-1">
	        <?php include 'pages/api_10_df/df_batch_intro.html';?>
	      </div>
		</div>
	  </div> <!-- end of tabs-api-3-->
  </div> <!-- end of tabs-api-->
</div><!-- end of wrapper-->
 
 
</body>
</html>