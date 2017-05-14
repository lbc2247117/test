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



<div class="banner">
    <ol id="listpoint" class="banner-point-box">
        <li v-for="(idx,obj) in banner" @click="moveToPointer(idx)"></li>
    </ol>
    <div  class="banner-pic-box">
        <div v-for="(idx,obj) in banner" class="banner-pic" @click="jumpTo(idx)">
            <img :src="obj.path">
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="content-wrapper">
    <div class="content-header">
        <div class="content-title">
            <h1 class="text-center"><a href="product.html" title="车辆纵览" target="_self">车辆纵览</a></h1>
            <span>Overview of models</span>
        </div>
        <p class="text-center margin-small-top"></p>
    </div>
    <div class="content-body">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-6" v-for="(idx,obj) in product"> 
                    <div class="thumbnail">
                        <div class="thumbnail-img">
                            <a href="javascript:void(0);" title="{{obj.name}}" @click='jumpGoodDesc(obj.id)'>
                                <img :src="obj.goodPic"  alt="{{obj.name}}" width="400" height="240" class="img-hov">
                                <img :src="obj.goodPic"  alt="{{obj.name}}" width="400" height="240" class="img-nhov">
                            </a> 
                        </div>
                        <div class="caption">
                            <h3 class="text-large text-bold text-center padding-top padding-bottom">{{obj.name}}</h3>
                            <h3 style="color: red" class="text-large text-center">{{obj.price}}</h3>
                            <p>{{obj.remark}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="content-wrapper">
    <div class="content-header">
        <div class="content-title">
            <h1 class="text-center"><a href="factory.html" title="走进工厂" target="_self">走进工厂</a></h1>
            <span>Into the factory</span>
        </div>
        <p class="text-center margin-small-top"></p>
    </div>
    <div class="tab-content">
        <div class="tab-pane  active" id="excase_type_35">
            <div class="grid gallery">
                <figure class="effect-zoe col-sm-6 col-md-3" v-for="(idx,obj) in factory"> <a href="javascript:void(0);" title="{{obj.name}}"><img :src="obj.picPath" title="{{obj.name}}" alt="{{obj.name}}" class="img-responsive"></a>
                    <figcaption>
                        <h2 class="text-left"><a href="javascript:void(0);">{{obj.name}}</a></h2>
                    </figcaption>
                </figure>
            </div>
        </div>
    </div>
</div>
<div class="content-wrapper">
    <div class="content-header">
        <div class="content-title">
            <h1 class="text-center"><a href="" title="销售服务" target="_self">销售服务</a></h1>
            <span>SALES SERVICE</span>
        </div>
        <p class="text-center margin-small-top"></p>
    </div>
    <div class="content-body">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-sm-4 col">
                    <div>
                        <img src="/Public/img/cir1.png" class="imgs">
                        <font>
                        <span>长安汽车销售</span>
                        <p class="col-desc">长安汽车专营店，贴心服务，专业售后，品质有保障！</p>
                        </font>
                    </div>
                </div>
                <div class="col-sm-12 col-sm-4 col">
                    <div>
                        <img src="/Public/img/cir2.png" class="imgs">
                        <font>
                        <span>汽车配件销售</span>
                        <p class="col-desc">全面覆盖高,中,低端市场,货真价实,物美价廉，海量选择！</p>
                        </font>
                    </div>
                </div>
                <div class="col-sm-12 col-sm-4  col">
                    <div>
                        <img src="/Public/img/cir3.png" class="imgs">
                        <font>
                        <span>汽车维修</span>
                        <p class="col-desc">多位资深汽车维修师为您解决爱车的疑难杂症！</p>
                        </font>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="content-wrapper">
    <div class="content-header">
        <div class="content-title">
            <h1 class="text-center"><a href="" title="关于我们" target="_self">关于我们</a></h1>
            <span>ABOUT US</span>
        </div>
        <p class="text-center margin-small-top"></p>
    </div>
    <div class="content-body">
        <div class="container">
            <div class="row hidden-xs">
                <div class="col-sm-12 text-big" v-html="dataObj.about">
                </div>
            </div>
            <div class="row visible-xs">
                <div class="col-sm-12 text-big" v-html="dataObj.about">
                </div>
            </div>
        </div>
    </div>
</div>
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
<?php $str = "/Application/Home/View/Public/static/js/index.js"; $arr = explode(",", $str); ?>
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