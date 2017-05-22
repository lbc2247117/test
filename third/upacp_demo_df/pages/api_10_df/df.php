﻿<form class="api-form" method="post" action="demo/api_10_df/Form_6_2_DF.php" target="_blank">
<p>
<label>商户号：</label>
  <input id="merId" pattern="\d{15}" type="text" name="merId" placeholder="" value="777290058110097" title="默认商户号仅作为联调测试使用，正式上线还请使用正式申请的商户号" required="required"/>
</p>
<p>
  <label>订单发送时间：</label>
  <input id="txnTime" pattern="\d{14}" type="text" name="txnTime" placeholder="订单发送时间" value="<?php echo date('YmdHis')?>" title="取北京时间，YYYYMMDDhhmmss格式。" required="required"/>
</p>
<p>
  <label>商户订单号：</label>
  <input id="orderId" pattern="[0-9a-zA-Z]{8,32}" type="text" name="orderId" placeholder="商户订单号" value="<?php echo date('YmdHis')?>" title="8-32位数字字母，自行定义内容。" required="required"/>
</p>
<p>
  <label>交易金额：</label>
  <input id="txnAmt" pattern="\d{1,12}" type="text" name="txnAmt" placeholder="交易金额" value="1000" title="单位为分，正数" required="required"/>
</p>
<p>
<label>&nbsp;</label>
<input type="submit" class="button" value="提交" />
<input type="button" class="showFaqBtn" value="遇到问题？"  />
</p>
</form>

<hr />
<p class="faq">
* 此demo使用的交易卡写死在代码里了，请查看Form_6_2_DF代码。<br />
</p>

<div class="question" >
<hr />
<h4>代付您可能会遇到...</h4>
<p class="faq">
<a href="https://open.unionpay.com/ajweb/help/faq/list?ha=6226090000000048" target="_blank">测试卡信息</a><br>
<a href="https://open.unionpay.com/ajweb/help/faq/list?ha=http400" target="_blank">http400错误</a><br>
<a href="https://open.unionpay.com//ajweb/help/respCode/respCodeList?respCode=9100004" target="_blank">测试环境9100004</a><br>
<a href="https://open.unionpay.com/ajweb/help/faq/list?ha=9100004" target="_blank">正式环境9100004</a><br>
<a href="https://open.unionpay.com//ajweb/help/respCode/respCodeList?respCode=6100030" target="_blank">6100030</a><br>
<br>
</p>
<hr />
<?php include $_SERVER ['DOCUMENT_ROOT'] . '/upacp_demo_df/pages/more_faq.php';?>
</div>

