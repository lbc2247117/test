<form class="api-form" method="post" action="demo/api_10_df/Form_6_6_BatTransQuery.php" target="_blank">
<p>
<label>商户号：</label>
  <input id="merId" pattern="\d{15}" type="text" name="merId" placeholder="" value="777290058110097" title="默认商户号仅作为联调测试使用，正式上线还请使用正式申请的商户号" required="required"/>
</p>
<p>
  <label>订单发送时间：</label>
  <input id="txnTime" pattern="\d{14}" type="text" name="txnTime" placeholder="订单发送时间" value="" title="填写被的批量查询交易的订单发送时间，YYYYMMDDhhmmss格式" required="required"/>
</p>
<p>
  <label>批次号：</label>
  <input id="batchNo" pattern="\d{4}" type="text" name="batchNo" placeholder="批次号" value="" title="填写被查询的批量交易的批次号" required="required"/>
</p>
<p>
<label>&nbsp;</label>
<input type="submit" class="button" value="提交" />
<input type="button" class="showFaqBtn" value="遇到问题？"  />
</p>
</form>
<hr />
<p class="faq">
* 此demo代码默认会将文件存放至D:/file/目录，请先建立此文件夹并保证有读写权限。如需修改其他路径，请至Form_6_6_BatTransQuery中修改。<br />
</p>
<div class="question" >
<hr />
<h4>批量代付查询您可能会遇到...</h4>
<p class="faq">
71XXXXX：respMsg中文提示挺明确的，请参考那个信息。<br>
</p>
<hr />
<?php include $_SERVER ['DOCUMENT_ROOT'] . '/upacp_demo_df/pages/more_faq.php';?>
</div>


