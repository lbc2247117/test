<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>投票明细</title>
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
        <div class="pull-left">
            <div class="btn btn-default" @click="back">返回</div>
        </div>
        <div class="col-md-3">
            微信昵称:<span style='color: red'>{{nickname}}</span><br/>
            当前票数:<span style='color: red'>{{entryCount}}</span>
        </div>
        <div class="pull-right">
            <div class="btn bg-laia" @click="addFn">添加</div>
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
                    <th>投票数量</th>
                    <th @click="order('votetime')" style="cursor: pointer">投票时间<span :class="['glyphicon',sortType=='asc'?'glyphicon-arrow-up':'glyphicon-arrow-down']"></span></th>
                    <th>头像</th>
                    <th>城市</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(idx, obj) in tableData">


                    <td>{{obj.id}}</td>
                    <td>{{obj.openid}}</td>
                    <td>{{obj.nickname}}</td>
                    <td>{{obj.count}}</td>
                    <td>{{obj.votetime}}</td>
                    <td><img src="{{obj.headimgurl}}" height="80"></td>
                    <td>{{obj.city}}</td>
                    <td>
                        <div class='btn btn-link' @click="delFn(idx)">删除</div>
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
    <div id="editCnr" v-show="editCnrVisible" style="display:none;">

        <input type="hidden" id="isEditNickName" name="isEditNickName" value="0">
        <div id="editCnt">
            <div id="header">
                <div class="pull-right" id="editClose" @click="hideEdit">&times;</div>
            </div>
            <div>
                <table>
                    <tbody>
                        <tr>
                            <td>添加票数<span class="must">*</span></td>
                            <td>
                                <input  type="text" v-model="addCount" class="form-control">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div id="actionCnr">
                    <div class="col-md-6">
                        <div @click="addVote" class="pull-left btn bg-laia btn-sm">保存</div>
                    </div>
                    <div class="col-md-6">
                        <div class="pull-right btn btn-default btn-sm" @click="hideEdit">取消</div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
    </div>
<div id="footer">© 2016 版权归世纪云道所有</div>
<!-- JavaScript -->
<script src="/active/Public/js/bootstrap.min.js"></script>
<script src="/active/Application/Admin/View//Public/static/js/app.js"></script>
<?php $str = "/active/Public/js/uploadPreview.js,/active/Public/js/jquery.form.js,/active/Public/js/vue.min.js,/active/Application/Admin/View//Public/static/js/index/detail.js"; $arr = explode(",", $str); ?>
<?php if(is_array($arr)): foreach($arr as $key=>$src): ?><script src="<?php echo ($src); ?>"></script><?php endforeach; endif; ?>
</body>
</html>