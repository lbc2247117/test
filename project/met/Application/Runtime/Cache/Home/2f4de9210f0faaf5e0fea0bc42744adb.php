<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
    <head>
        <meta name="renderer" content="webkit">
        <meta charset="utf-8" /><meta name="robots" content="noindex,nofllow" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta name="description" content="盐城万达汽车销售服务有限公司东台分公司主要经营长安轿车品牌汽车、专业作业车、汽车配件、通用设备等，品质有保证！" />
        <meta name="keywords" content="盐城万达汽车销售服务有限公司东台分公司" />
        <link href="http://7xnjsm.com1.z0.glb.clouddn.com/favicon.jpg" rel="shortcut icon" type="image/x-icon" />
        <title>长安汽车-盐城万达东台分公司</title>
        <link rel="stylesheet" href="/Public/css/bootstrap.min.css">
        <link href="/Application/Home/View/Public/static/css/base.css" rel="stylesheet" />
    </head>
    <body>
        <div id="wrapper">
            <header>
                <nav class="header-nav">
                    <!--  <div class="nav-logo">
                          <img src="http://7xnjsm.com1.z0.glb.clouddn.com/logo.png">
                      </div>-->
                    <div class="pull-right header-menu">
                        <ul class="menu-box">
                            <li id="about"><a href="/Home/Index/about.html">关于我们</a></li>
                            <li id="tell"><a href="/Home/Index/tell.html">联系我们</a></li>
                            <li id="factory"><a href="/Home/Index/factory.html">走进工厂</a></li>
                            <li id="product"><a href="/Home/Index/product.html">产品展示</a></li>
                            <li id="home"><a href="/Home/Index/index.html">网站首页</a></li>
                        </ul>
                    </div>
                </nav>
            </header>
            <div class="content">
                <div class="content-box">



<a href="javascript:void(0);" class="v2-banner__dz" style="display:block;"><img src="<?php echo ($setting["headrPic"]); ?>"></a>
<article class="sidebar-v2__article">
    <div class="met_article">
        <div class="product-v2">
            <ul class="product-v2__list list-unstyled">
                <li class="col-lg-4 col-md-6 col-sm-6 v2-mtb15" v-for="(idx,obj) in tableData">
                    <a href="javascript:void(0);" @click='jumpGoodDesc(obj.id)'><img :src="obj.goodPic"></a>
                    <div class="product-v2__list__box v2-ac product-v2__dg">
                        <h2><div class="pull-left">{{obj.name}}</div><div style="color:red;" class="pull-right">{{obj.price}}</div><div class="clearfix"></div></h2>
                        <p class="v2-mt15 v2-lc" title="{{obj.remark}}"><i>●</i>{{obj.remark}}</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</article>
<div class="clearfix"></div>
<footer class="main-footer sticky footer-type-1">
    <div class="foot-header">
        <div class="container">
            <div class="row">
                <div class="col-xs-8 col-sm-8 col-md-9">
                    <div class="foot-nav text-left">
                        <a href="/Home/Index/index.html" >网站首页</a>|<a href="/Home/Index/product.html" >产品展示</a>|<a href="/Home/Index/factory.html">走进工厂</a>|<a href="/Home/Index/tell.html" >联系我们</a>|<a href="/Home/Index/about.html">关于我们</a>
                    </div>
                </div>
                <div class="col-xs-4 col-sm-4 col-md-3 hidden-xs">
                    <div class="go-up"> <a href="#" rel="go-top"> <i class="fa-angle-up"></i> <span class="title text-big">返回顶部</span> </a> </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-inner">
        <div class="container">
            <div class="row hidden-xs">
                <div class="col-xs-12 col-sm-9 col-md-10">
                    <div class="row padding-large-top">
                        <div class="col-xs-5 col-sm-7">
                            <div class="footer-text ">
                                <p><?php echo ($setting["company"]); ?> 版权所有 2016-2017</p>
                                <p><?php echo ($setting["address"]); ?></p>
                                <p>电话：<?php echo ($setting["mobile"]); ?>  QQ:<?php echo ($setting["QQ"]); ?>  </p><div id="metinfo_91mb_Powered"></div><p></p>

                                <div class="powered_by_metinfo">Powered&nbsp;by&nbsp;<a href="javascript:void(0);">QiMeng&nbsp;</a> ©2010-2016&nbsp;</div>
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-5 borderL padding-large-left">
                            <p class="title text-bold text-large "><?php echo ($setting["tell"]); ?></p>
                            <p class="title margin-little-top ">周一至周日（9:00 - 22:00）</p>
                            <a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo ($setting["QQ"]); ?>&site=qq&menu=yes" class="btn btn-warning btn-lg margin-top " role="button"> <i class="fa-qq"></i> QQ在线服务 </a>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-2">
                    <div class="wxcon pull-right">
                        <p class=""><img src="<?php echo ($setting["qr"]); ?>" width="125" height="125">
                        </p></div>
                </div>
            </div>
            <div class="row padding-big-top padding-big-bottom visible-xs padding-left">
                <div class="col-xs-12">
                    <p class="title text-bold text-large  hidden-xs"><?php echo ($setting["tell"]); ?></p>
                    <p class="title margin-little-top  hidden-xs">周一至周日（9:00 - 22:00）</p>
                    <a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo ($setting["QQ"]); ?>&site=qq&menu=yes" class="btn btn-warning btn-lg margin-top  hidden-xs" role="button"> <i class="fa-qq"></i> QQ在线服务</a>
                    <p class="margin-little-top "> </p> 
                    <p><?php echo ($setting["company"]); ?> 版权所有 2016-2017</p>
                    <p><?php echo ($setting["address"]); ?> </p>
                    <p>电话：<?php echo ($setting["mobile"]); ?>  QQ:<?php echo ($setting["QQ"]); ?>  </p><div id="metinfo_91mb_Powered"></div><p></p>
                    <p></p>
                    <div class="powered_by_metinfo">Powered&nbsp;by&nbsp;<a href="javascript:void(0);">QiMeng&nbsp;</a> ©20010-2017&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</footer>
</div>
</div>
</div>
<!-- JavaScript -->
<script src="/Public/js/jquery.min.js"></script>
<script src="/Public/js/bootstrap.min.js"></script>
<script src="/Public/js/vue.min.js"></script>
<script src="/Application/Home/View/Public/static/js/base.js"></script>
<?php $str = "/Application/Home/View/Public/static/js/product.js"; $arr = explode(",", $str); ?>
<?php if(is_array($arr)): foreach($arr as $key=>$src): ?><script src="<?php echo ($src); ?>"></script><?php endforeach; endif; ?>
<script>
    var _hmt = _hmt || [];
    (function () {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?e2be48a47ce95bd707fb6b163e090a68";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>
</body>
</html>