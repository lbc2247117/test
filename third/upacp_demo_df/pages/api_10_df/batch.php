<form class="api-form" method="post" action="demo/api_10_df/Form_6_5_BatTrans.php" target="_blank">
<p>
<label>商户号：</label>
  <input id="merId" pattern="\d{15}" type="text" name="merId" placeholder="" value="777290058110097" title="默认商户号仅作为联调测试使用，正式上线还请使用正式申请的商户号" required="required"/>
</p>
<p>
  <label>订单发送时间：</label>
  <input id="txnTime" pattern="\d{14}" type="text" name="txnTime" placeholder="订单发送时间" value="<?php echo date('YmdHis')?>" title="取北京时间，YYYYMMDDhhmmss格式。" required="required"/>
</p>
<p>
  <label>批次号：</label>
  <input id="batchNo" pattern="\d{4}" type="text" name="batchNo" placeholder="批次号" value="0002" title="当天唯一，0001-9999，商户号+批次号+上交易时间确定一笔交易" required="required"/>
</p>
<p>
  <label>总笔数：</label>
  <input id="totalQty" pattern="\d{1,12}" type="text" name="totalQty" placeholder="总笔数" value="10" title="测试环境限制20笔/个文件" required="required"/>
</p>
<p>
  <label>总金额：</label>
  <input id="totalAmt" pattern="\d{1,12}" type="text" name="totalAmt" placeholder="总金额" value="1000" title="单位为分，正数" required="required"/>
</p>
<p>
  <label>批量文件存放路径：</label>
  <input id="filePath" type="text" name="filePath" value="d:/file/DF00000000777290058110097201507140002I.txt" required="required"/>
</p>
<p>
<label>&nbsp;</label>
<input type="submit" class="button" value="提交" />
<input type="button" class="showFaqBtn" value="遇到问题？"  />
</p>
</form>

<hr />
<p class="faq">
* 请确定批量文件路径下有文件。<br />
* 使用样例文件时请修改批量文件中的商户号、日期、批次号、订单号等信息再点提交。<br />
</p>

<div class="question" >
<hr />
<h4>批量代付您可能会遇到...</h4>
<p class="faq">
<a href="https://open.unionpay.com/ajweb/help/faq/list?ha=6226090000000048" target="_blank">测试卡信息</a><br>
<a href="https://open.unionpay.com//ajweb/help/respCode/respCodeList?respCode=9100004" target="_blank">测试环境9100004</a><br>
<a href="https://open.unionpay.com/ajweb/help/faq/list?ha=9100004" target="_blank">正式环境9100004</a><br>
71XXXXX：respMsg中文提示挺明确的，请参考那个信息。
<br>
</p>
<hr />
<?php include $_SERVER ['DOCUMENT_ROOT'] . '/upacp_demo_df/pages/more_faq.php';?>
</div>


