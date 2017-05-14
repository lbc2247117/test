<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>中秋活动后台</title>
        <link rel="stylesheet" href="/active/Public/css/bootstrap.min.css">
        <link rel="stylesheet" href="/active/Application/Admin/View//Public/static/css/sb-admin.css">
        <link rel="stylesheet" href="/active/Application/Admin/View//Public/static/font-awesome/css/font-awesome.min.css">
        <script src="/active/Public/js/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/active/Application/Admin/View//Public/static/css/index.css" />
</head>
<body>
    <div id="wrapper">
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?php echo U('index/index');?>">运营活动后台</a>
            </div>
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
                    <li class="dropdown">
                        <a href="index">16年教师节</a>
                    </li>
                    <li class="dropdown">
                        <a href="sendsms">成都GT赛车</a>
                    </li>
                    <li class="dropdown">
                        <a href="midautumn">16年中秋节</a>
                    </li>
                </ul>


                <ul class="nav navbar-nav navbar-right navbar-user">

                    <li class="dropdown user-dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> 你好,<?php echo session('username');?> <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="#"><i class="fa fa-gear"></i> 设置</a></li>
                            <li class="divider"></li>
                            <li><a href="<?php echo U('login/logout');?>"><i class="fa fa-power-off"></i> 退出</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
<div id="page-wrapper">
    <div id="alertCnr" v-show="alert.show" class="alert" :class="alert.type" style="display:none;">
        <div class="close" @click="hideAlert">&times;</div>
        <span v-html="alert.msg"></span>
    </div>
    <div v-show="notice.show" class="alert" :class="notice.type" style="display:none;">
        <span>{{notice.msg}}</span>
        <div class="close" @click="hideNotice">&times;</div>
    </div>
    <div id="searchCnr">

        <div class="col-md-2">
            <div class="input-group">
                <input type="text" id="keyWord" v-model="searchKey" class="form-control" placeholder="输入要查找的用户名或手机号">
                <div class="input-group-addon bg-laia" @click="searchFn">
                    <span class="glyphicon glyphicon-search"></span>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <select class="form-control" v-model="selType" @change="getData">
                <option v-for="(idx,obj) in prizeType" :value="obj.id" v-text="obj.text"></option>
            </select>
        </div>
        <div class="col-md-2">
            总访问量:<span style='color: red'>{{clickCount}}</span><br/>
            总分享量:<span style='color: red'>{{shareCount}}</span>
        </div>
        <div class="col-md-2">
            中奖总数:<span style='color: red'>{{prizeCount}}</span><br/>
            领奖总数:<span style='color: red'>{{getPrize}}</span>
        </div>
        <div class="pull-right">
            <div class="btn bg-laia" @click="showPrize">奖品设置</div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>公众号ID</th>
                    <th>用户名</th>
                    <th @click="order('prizetime')" style="cursor: pointer">抽奖时间<span :class="['glyphicon',sortType=='asc'?'glyphicon-arrow-up':'glyphicon-arrow-down']"></span></th>
                    <th>电话</th>
                    <th>头像</th>
                    <th>城市</th>
                    <th>中奖类型</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(idx, obj) in tableData">

                    <td>{{obj.id}}</td>
                    <td>{{obj.openid}}</td>
                    <td>{{obj.nickname}}</td>
                    <td>{{obj.prizetime}}</td>
                    <td>{{obj.mobile}}</td>
                    <td><img src="{{obj.headimgurl}}" height="80"></td>
                    <td>{{obj.city}}</td>
                    <td>
                        <div v-if="obj.type==1">1元红包</div>
                        <div v-if="obj.type==2">2元红包</div>
                        <div v-if="obj.type==3">爱奇艺会员</div>
                        <div v-if="obj.type==4">小龙坎代金券</div>
                        <div v-if="obj.type==5">芭菲盛宴自助券</div>
                    </td>
                    <td>
                        <div v-if="obj.status==0"  @click="onOrOff(idx)" class='btn btn-link'>未领领</div>
                        <div v-if="obj.status==1" @click="onOrOff(idx)" class='btn btn-link'><span style="color: red">已领领</span></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div>
        <ul v-show="showPageNav" class="pagination">
            <li :class="curPage < 2 ? 'disabled' : ''" @click="pageNav(curPage-1)">
                <a href="javascript:;">上一页</a>
            </li>
            <li v-for="obj in pageShowData" :class="curPage == obj.num ? 'active' : ''" @click="pageNav(obj.num)">
                <a href="javascript:;">{{obj.num}}</a>
            </li>
            <li :class="curPage < pageAllData.length ? '' : 'disabled'" @click="pageNav(curPage+1)">
                <a href="javascript:;">下一页</a>
            </li>
        </ul>

        <div id="pageCount" class="btn bg-laia">
            <span>第 {{curPage}} 页 | </span>
            <span>共 {{pageCount}} 页</span>
        </div>
    </div>
</div>
</div>
<div id="footer">© 2016 版权归世纪云道所有</div>
<!-- JavaScript -->
<script src="/active/Public/js/bootstrap.min.js"></script>
<script src="/active/Application/Admin/View//Public/static/js/app.js"></script>
<?php $str = "/active/Public/js/uploadPreview.js,/active/Public/js/jquery.form.js,/active/Public/js/vue.min.js,/active/Application/Admin/View//Public/static/js/index/prize.js"; $arr = explode(",", $str); ?>
<?php if(is_array($arr)): foreach($arr as $key=>$src): ?><script src="<?php echo ($src); ?>"></script><?php endforeach; endif; ?>
</body>
</html>