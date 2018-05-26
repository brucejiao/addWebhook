//Copyright (C) DICP-CMC Innovation Institute of Medicine.

<!DOCTYPE html>
<?php
 error_reporting(E_ERROR); 
 require_once("config.inc.php"); 
 limitvalidation(3); 
 $administration=$_SESSION['user_tid']==1?true:false; 
 
 if(!$_SESSION['user_gid']){
	 echo "你还没有分配到小组，无法填写购买申请单。请与管理员联系。";
	 echo "<a href='/stdcompd/zuneihaocai/'>返回耗材首页</a>";
	 exit();
 }

 if(!checkrole('gmcreate', $globalCfg['role'])){
	 echo "你没有填写购买单的权限。请与管理员联系。";
	 echo "<a href='/stdcompd/zuneihaocai/'>返回耗材首页</a>";
	 exit();	  
}

 if(isset($_REQUEST['id'])){
	$userid=$_REQUEST['id'];//user GM list
	 
	$sql="SELECT a.*		  
		  FROM `1803hcbuy` a
		  LEFT JOIN `1803hcgmtmp` b ON b.`hcbuyid`=a.`id`
		  WHERE b.`gmuserid`='$userid'";
	$items=array();
	if($query=$db->query($sql)){
		while($rows=$query->fetch_assoc()){
			$items[]=$rows;
		}
	}	
}
?>
<html lang="zh">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="DICP, ACS, Spare parts">
	<meta name="keywords" content="Administration">
	<meta name="author" content="xiaoys">
    <title><?php echo $globalCfg['companyName'];?>耗材管理</title>

    <!-- Bootstrap --> 
<link rel="stylesheet" href="style.css">	
	<link rel="stylesheet" href="../plugin/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="../plugin/bootstrap/switch/bootstrap-switch.css">
	<link rel="stylesheet" href="datepicker/css/datepicker3.css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="../plugin/html5/html5shiv.min.js"></script>
      <script src="../plugin/html5/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
	
	<div class="jumbotron">
		<div class="container text-center">
			<h1><?php echo $globalCfg['companyName'];?>耗材购买申请单</h1>
		</div>
	</div>
	<div class="container">
	<div class="row">
	<?php
		$array_header=array('申请人',
		'所属小组',
		'申请日期',
		'是否加急');
		$array_content=array(
		'#',
		'名称',
		'CAS编号',
		'单位',
		'规格',
		'数量',
		'估价(元)',	
		'总估价(元)',
		'自购',
		'自存',
		'耗材种类',
		'来源',
		'购买原因',
		'所属项目',
		'储存位置',
		'备注');
		$array_footer=array('购买人',
		'供货商',
		'联系方式',
		'订单号',
		'下单时间',
		'是否先付款',
		'备注');
	 /*$array_headval=array(applyid,
		groupid,
		applydate,
		urgent);
		$array_contentval=array(name,
		spec,
		quantity,
		unitprice,
		totalfee,	
		selfbuy,	
		typeid,	
		reason,	
		projectid,	
		place,	
		hcnote);	
		$array_footerval=array(purchid,	
		providerid,	
		providertel,	
		id,	
		stepdate,	
		prepayment,	
		providernote);*/
	?>
	<div class="form-horizontal">
		<div class="form-group">	
			<div class="col-md-6">
				<label class="col-md-6 control-label">申请人<!--span class="additem btn btn-default btn-xs" id="newuser">新</span--></label>
				<div class="col-md-5">
					
					<?php
						$defaultuserid=$userid?$userid:$_SESSION["user_uid"];
						$sql="SELECT a.*,
									 b.`name` as groupname,
									 b.`id` as groupid		
								FROM `1803hcuserinfo` a 
									LEFT JOIN `1803hcgroup` b ON b.`id`=a.`user_gid` 
									WHERE a.`user_uid`>0 
									ORDER BY CONVERT(`user_tname` USING gbk) ASC";
						//echo $sql;
						$group="未分组";
						$groupid=0;
						if($query=$db->query($sql)){
								echo '<select class="form-control" id="applyuserid"  disabled>';
								while($rows=$query->fetch_assoc()){									
									$selected=($rows['user_uid']==$defaultuserid)?"selected":"";
									echo $selected.$rows['user_uid'];
									if($rows['user_uid']==$defaultuserid){
										$group=$rows['groupname'];
										$groupid=$rows['groupid'];
									}

									echo "<option value='".$rows['user_uid']."' $selected>".$rows['user_tname']."</option>";
								}
								echo '</select>';
							}						
						$applydate=date("Y/m/d");
						$urgent='';
					?>
					
				</div>	
			</div>

			<div class="col-md-6">
				<label class="col-md-3 control-label">所属小组</label>
				<div class="col-md-5">
					<input class="form-control" id="groupid" data-id="<?php echo $groupid?>" value="<?php echo $group?>" readonly>
				</div>
			</div>
		</div>
		<div class="form-group">	
			<div class="col-md-6">
				<label class="col-md-6 control-label">申请日期</label>
				<div class="col-md-5">
					<input class="form-control" id="applydate" value="<?php echo $applydate?>" readonly>
				</div>	
			</div>

			<div class="col-md-6">
				<label class="col-md-3 control-label">是否加急</label>
				<div class="col-md-5">
					<input type="checkbox" id="urgent" <?php echo $urgent;?>>
				</div>	
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-6">
				<label class="col-md-6 control-label">可使用经费</label>
				<div class="col-md-5">			
					
						<?php
							
							//$sql1 = "SELECT a.id AS bdid, a.bdName, a.startDate, a.endDate, SUM(a.bdValue) AS bdV, SUM(a.spValue) AS spV FROM `1803hcbudget`a WHERE a.bdType = 'original' AND a.uId = (SELECT user_gid FROM `1803hcuserinfo` WHERE user_uid = ".$_SESSION["user_uid"].") AND a.uType = 'team' AND '".date("Y-m-d")."'>= a.startDate AND '".date("Y-m-d")."'< a.endDate";
							
							/* $sql = "SELECT a.id AS bdid, a.bdName, a.startDate, a.endDate, (a.bdValue+IFNULL(b.bdV,0)) AS bdV, c.spV AS spV FROM `1803hcbudget`a 

							LEFT JOIN (SELECT pId, SUM(bdValue) AS bdV FROM `1803hcbudget` WHERE `bdType` = 'change' GROUP BY pId) b ON b.pId = a.id 

							LEFT JOIN (SELECT bdid, SUM(IFNULL(`unitprice`,0)*IFNULL(`quantity`,0)) AS spV FROM `1803hcbuy` WHERE step in ".$GMSP->getStepsAfter('TD').") c ON (c.bdid = a.id OR c.bdid IN (SELECT id FROM `1803hcbudget` WHERE pId = a.id))  

							WHERE a.bdType = 'original' AND a.uId = (SELECT user_gid FROM `1803hcuserinfo` WHERE user_uid = ".$_SESSION["user_uid"].") AND a.uType = 'team' AND '".date("Y-m-d")."'>= a.startDate AND '".date("Y-m-d")."'<= a.endDate";
							 */
							$bgdate = date('Y-m-01', strtotime(date("Y-m-d")));
							$eddate = date('Y-m-d', strtotime("$bgdate +1 month -1 day"));

							$sql = "SELECT '".date("Ym")."' AS bdid, '".date("Y-m")."本月小组经费"."' AS bdName, '".$bgdate."' AS startDate, '".$eddate."' AS endDate, 100000 AS bdV,SUM(totalfee) AS spV FROM `1803hcapply`  
							WHERE 
							(step in ".$GMSP->getStepsAfter('TD').")   
							AND
							 groupid = (SELECT user_gid FROM `1803hcuserinfo` WHERE user_uid = ".$_SESSION["user_uid"].")
							AND '".$bgdate."'< date_format(applydate, '%Y-%m-%d') and date_format(applydate, '%Y-%m-%d') <'".$eddate."'";
							
							
							//   echo $sql;
							// die;
							//小组内经费名唯一，包括change的内容。经费开销包括了经费以及实际开销。			
					
							if($query = $db->query($sql)){
								$num = 0;
								$optionlist = array();
									
								while ($res = $query -> fetch_assoc()){
									// var_dump($res);
									if($num == 0) $defaultBd = $res;
									$num++;
									if($res["bdName"]=="") continue;
									$optionlist[]=$res;										
								}
							}
							echo '<select class = "form-control" id="avBudget" '.(count($optionlist)==0?"disabled":"").'>';					
							echo "<option value='{\"bdid\":\"-1\"}'>".(count($optionlist)==0?"已无可用经费":"请选择预算经费")."</option>";
				
							foreach($optionlist as $val){
								echo "<option value=".json_encode($val).">".$val["bdName"]."</option>";
							}
							echo "</select>";							

							$spend = $defaultBd["spV"];							
							$total = $defaultBd["bdV"];	
							$rest = $total - $spend;						
							$percent = 100*$rest/$total;							
						?>
					
				</div>	
			</div>

			<div class="col-md-6">
				<label class="col-md-3 control-label">经费详情</label>
				<div class="col-md-5 form-inline">
					<div class="progress">
  <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id = "bd-progBar" style="width:0%">0%   
  </div>
</div>
				</div>
				<div>
					<small class="text-danger" id="bd-dateRange"></small><?php //echo $sql;?>
				</div>	
			</div>			
		</div>	
	</div>	
	
	<fieldset>
		<legend class="page-header text-center"><h4>购买申请单</h4>
			<span id="shenqingid"></span><?php echo $applycode;?></legend>
			<table id="mainTable" class="table table-striped bg-warning" style="font-size:11.5px">
				<thead><tr>
					<?php
						foreach($array_content as $val){
							echo "<th>$val</th>";
						}
					?>
				</tr></thead>
				<tbody>
				<?php
					array_push($items,
						array(
						"name"=>"",
						"cas"=>"",
						"spec"=>"",
						"quantity"=>"1",
						"unitprice"=>"0",
						"totalfee"=>"0",
						"selfbuy"=>"1",
						"selfsave"=>"0",
						"typeid"=>"0",
						"source"=>"商城",
						"reason"=>"",
						"projectid"=>"",
						"place"=>"",
						"hcnote"=>"")
					);
					
					
					foreach($items as $item){
						$name=$item["name"];
						$cas=$item["cas"];
						$spec=$item["spec"];
						$quantity=$item["quantity"];
						$unitprice=$item["unitprice"];
						$totalfee=$item["totalfee"];
						$selfbuy=$item["selfbuy"];
						$selfbuy=$item["selfsave"];
						$typeid=$item["typeid"];
						$source=$item["source"];
						$reason=$item["reason"];
						$projectid=$item["projectid"];
						$place=$item["place"];
						$hcnote=$item["hcnote"];
						//data-toggle="popover" 物品名称危化品检查
						echo '<tr>
							<th><input type="checkbox"></th>
							<td class="name" name="name[]" 
								data-container="body"
								
								data-placement="top"
								data-trigger="focus"
								
								data-gmid="'.$item["id"].'"
							>'.$name.'</td>

							<td class="cas" name="cas[]">'.$cas.'</td>

							<th>
								<select  class="unit" name="unit[]">
									';
									$unit=array(										
										"个",
										"件",
										"盒",
										"瓶",					
										"袋",					
										"台",					
										"粒",		
										"根",
										"次",
										"小时",
										"L",
										"ml",
										"mg",
										"g",
										"kg",
										"mm",
										"m",
									);
									foreach($unit as $val){
										echo "<option value='$val'>$val</option>";										
									}
									echo '
								</select>
							</th>
							<td class="spec" name="spec[]">'.$spec.'</td>
							<td class="quantity" name="quantity[]">'.$quantity.'</td>
							<td class="unitprice" name="unitprice[]">'.$unitprice.'</td>
							<th class="totalfee" name="totalfee[]">'.$totalfee.'</th>
							<th class="editable" >
								<select class="selfbuy" name="selfbuy[]"><option value="1" '.(($selfbuy==1)?"selected":"").'>是</option><option value="0" '.(($selfbuy==0)?"selected":"").'>否</option></select>
							</th>
							<th class="editable" >
								<select class="selfsave" name="selfsave[]"><option value="1" '.(($selfsave==1)?"selected":"").'>是</option><option value="0" '.(($selfsave==0)?"selected":"").'>否</option></select>
							</th>							
							<th class="editable">';
							$sql="SELECT * FROM `1803hctype` WHERE 1 ORDER BY name desc";
							if($query=$db->query($sql)){
								echo '<select class="typeid" name="typeid[]">';
								while($rows=$query->fetch_assoc()){
									if($rows["id"] == 5){//"试剂:普通试剂"
										echo "<option value='".$rows["id"]."' selected = \"selected\">".$rows["name"]."</option>";
									}else{
										echo "<option value='".$rows["id"]."'>".$rows["name"]."</option>";
									}
								}
								echo "</select>";
							}	
						echo '</th>
							<th class="editable">
								<select class="source" name="source[]" disabled>
								<option value="商城" selected>商城</option>
								<option value="合同">合同</option>
								<option value="其它" selected>其它</option>
								</select>
							</th>
							<td class="reason" name="reason[]">'.$reason.'</td>
							
							<th class="editable">';
							echo '<select class="projectid" name="projectid[]">';
							echo '<option value="0" >请选择</option>';
							$sql="SELECT id,abbrev AS name FROM `1803projects` WHERE CONCAT(`incharger`,`executor`,`attendant`) LIKE '%".$_SESSION['user_tname']."%'";
							if($query=$db->query($sql)){							
								while($rows=$query->fetch_assoc()){
									echo "<option value='".$rows["id"]."'>".$rows["name"]."</option>";
								}								
							}
							echo "</select>";
							echo '</th>
							
							<td class="place" name="place[]">'.$place.'</td>
							<td class="hcnote" name="hcnote[]">'.$hcnote.'</td>
						</tr>';
					}
				?>
				</tbody>
				<tfoot>
					<tr>
					<th>总计</th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					</tr>
				</tfoot>
			</table>
			<p class="btn btn-default" id="newline">新增行</p>
			<p class="btn btn-default" id="delline">删除行</p>
			<a href="files/yzdjd20150908.doc">易制毒申请表</a>
			&nbsp <a  href="files/yizhibao20160106.doc">易制爆申请表</a>
			&nbsp <a  href="../weihuapin/" target="_blan">2013-2015危化品查询</a>
	</fieldset>
	<div class="pageheader"><h3></h3></div>
	

	<div class="form-horizontal">
		<div class="form-group">	
			<div class="col-md-6">
				<label class="col-md-6 control-label">购买人</label>
				<div class="col-md-5">
					
					<?php
						$sql="SELECT * FROM `1803hcuserinfo` WHERE `user_uid`>1 ORDER BY CONVERT(`user_tname` USING gbk) ASC";
							if($query=$db->query($sql)){
							
								echo '<select class="form-control" id="buyuserid">';
								while($rows=$query->fetch_assoc()){
									$selected=($rows['user_uid']==$defaultuserid)?"selected":"";
									echo "<option value=".$rows['user_uid']." $selected>".$rows['user_tname']."</option>";
								}
								echo '</select>';
							}	
					?>
					
				</div>	
			</div>

			<div class="col-md-6">
				<label class="col-md-3 control-label">建议供货商<span class="additem btn btn-danger btn-xs" id="newprovider">New</span></label>
				<div class="col-md-5">
					
					<?php
						$sql="SELECT * FROM `1803hcprovider` ORDER BY CONVERT(`name` USING gbk) ASC";
						if($query=$db->query($sql)){
							echo '<select class="form-control" id="providerid" name="providerid">';
							$i=1;
							while($rows=$query->fetch_assoc()){
								
								if($rows['name']=='未知'){
									echo "<option value=".$rows['id']." selected>".$rows['name']."</option>";
									$contact=$rows['contact'];
								}else
									echo "<option value=".$rows['id'].">".$rows['name']."</option>";
								$i++;
							}
							echo '</select>';
						}	
					?>
					
				</div>
			</div>
		</div>
		<div class="form-group">	
			<div class="col-md-6">
				<label class="col-md-6 control-label">订单号</label>
				<div class="col-md-5">
					<input class="form-control" id="invoicecode" value="<?php echo $proid;?>" readonly>
				</div>	
			</div>

			<div class="col-md-6">
				<label class="col-md-3 control-label">联系方式</label>
				<div class="col-md-5">
					<input class="form-control contact" value="<?php echo $contact;?>" readonly>
				</div>	
			</div>
		</div>	
		<div class="form-group">	
			<div class="col-md-6">
				<label class="col-md-6 control-label">下单日期</label>
				<div class="col-md-5">
					<!--input class="form-control datepicker" id="invoicedate" value="" -->
					<input class="form-control" id="invoicedate" value="" readonly>
				</div>	
			</div>
			
			
			

			<div class="col-md-6">
			
			<label class="col-md-3 control-label">预付款项目</label>
				<div class="col-md-5">					
					<?php
						$disabled="disabled";
						/*if(($_SESSION['user_tname']=='陈成') ||
							($_SESSION['user_tname']=='董岩') ||
							($_SESSION['user_tname']=='管理员')) $disabled="";*/
						
						echo '<select class="form-control" id="prepayment" name="prepayment" '.$disabled.'>
							<option value=0>正常申请单</option>
							<option value=1>预付虚拟清单</option>';
						/*$sql="SELECT * FROM `1803hcjfys` WHERE `list`=0 ORDER BY CONVERT(`name` USING gbk) ASC";
						if($query=$db->query($sql)){							
							$i=1;
							while($rows=$query->fetch_assoc()){								
								echo "<option value=".$rows['id'].">".$rows['name'].":".$rows['quantity']."</option>";
								$i++;
							}
							
						}*/												
						echo '</select>';	
					?>					
				</div>
			</div>	
				
			</div>
			

		<div class="form-group">	
			<div class="col-md-6">
				<label class="col-md-6 control-label">备注</label>
				<div class="col-md-6">
					<textarea class="form-control" id="providernote"></textarea>
				</div>	
			</div>
			<div class="col-md-6">
				<p class="col-md-offset-3 col-md-5 btn btn-success submit">提交购买申请单</p>
			</div>
		</div>	
	</div>
		</div>	
	<div class="page-header"><h4>已购商品价格查询</h4></div>
	<div class="row">	
	<div class="input-group col-md-6">
		<span  class="input-group-addon">
			<input type="checkbox" id="iszero"> 单价>0
		</span>
		<input id="prices" type="text" class="form-control" placeholder="已购商品价格搜索">  
		<span class="input-group-btn">
			<button id="pricesubmit" class="btn btn-pirmary">Search</button>
		</span>			
	</div>

	<div style="padding-top:20px">
		<table class="table table-condensed table-hovered">
			<thead><tr><th>#</th><th>名称</th><th>价格</th><th>规格</th><th>种类</th><th>供货商</th><th>购买申请单</th><th>操作</th></tr></thead>
			<tbody id="priceresults"></tbody>
		</table>
	</div>
	</div>
	</div><!--end of row-->
	<div class = 'msg'></div>
	</div><!--end of container-->

<div class="footer">
      <div class="container">
        <p class="text-muted text-center">2014 Email:<a href="mailto:xiaoys@dicp.ac.cn">Xiaoys@dicp.ac.cn</a></p>
      </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">新增供货商</h4>
      </div>
      <div class="modal-body"> 
		<div id='newprovider'>
			<div class="control-group">
				<div class="form-group">
					<label >供货商名称</label>
					<input class="form-control" placeholder="" id="providername">
				</div>
				<div class="form-group">
						<label >联系方式</label>
						<input class="form-control" placeholder="" id="contact">						
				</div>
				<div class="">
					<span class="btn btn-info" id="addprovider">添加</span>
				</div>
			</div>				
		</div>  
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

	<!-- Bootstrap core JavaScript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script type="text/javascript" src="../plugin/jquery/jquery-2.0.3.min.js"></script>
	<script type="text/javascript" src="../plugin/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="datepicker/js/bootstrap-datepicker.js"></script>	
	<script type="text/javascript" src="../plugin/editable/mindmup-editabletable.js"></script>
	<script type="text/javascript" src="../plugin/editable/numeric-input-example.js"></script>
	<script type="text/javascript" src="../plugin/bootstrap/switch/bootstrap-switch.js"></script>
	<script type="text/javascript" src="../plugin/jquery/jquery.fix.clone.js"></script>
	<script>
	
/***budget******************/	
	$("#avBudget").on("change",function(){		
		var bd = $.parseJSON($(this).val());
		var spend = bd.spV;
		var total = bd.bdV;
		var rest = bd.bdV - bd.spV;
		var percent = 100.0*rest/total;
		percent = percent.toFixed(1);
		console.log("====total=="+total+"==bd.bdV=="+bd.bdV+"==bd.spV==="+bd.spV);
		
		$("#bd-progBar").html(percent+"%("+rest.toFixed(1)+")");
		$("#bd-progBar").prop("aria-valuenow",percent);
		$("#bd-progBar").prop("style","width:"+percent+"%");
		
		if(percent < 30)
			$("#bd-progBar").prop("class","progress-bar progress-bar-danger");
		else if(percent < 60)
			$("#bd-progBar").prop("class","progress-bar progress-bar-warning");	
		else
			$("#bd-progBar").prop("class","progress-bar progress-bar-success");	
		
		
		$("#bd-dateRange").html((typeof(bd.startDate)=="undefined"?"":bd.startDate)+(typeof(bd.endDate)=="undefined"?"":" To "+bd.endDate));				
	});
	
/*********************************/	
	$('#mainTable').editableTableWidget().numericInputExample();
	$('#providerinfo').editableTableWidget();	
	$("p#newline").click(function(){
			$('#mainTable').find("tbody tr:last-child").after($('#mainTable').find("tbody tr:last-child").clone());
			//tg
			CheckType($('#mainTable').find("tbody tr:last-child"));
			$('#mainTable').find("tbody tr:last-child").find("td.name").removeAttr("data-gmid");
			$('#mainTable').editableTableWidget().numericInputExample();
	});
	$("p#delline").click(function(){			
			$("input:checked").each(function(){				
				if($(this).closest("tbody").children().length>1){
					var line=$(this).closest("tr");
					var id=line.find("td.name").attr("data-gmid");
					if(id>0){
						if(confirm("这是退单物品，确定删除？")){
							$.post("record.php",{"id":id,"table":"1803hcgmtmp"},function(data){
								var status=$.parseJSON(data); 
								if(status.records=="OK"){					
									line.remove();
								}
							});
						}else return;
					}else
						line.remove();
				}else
					alert("最后一行，不能删除!!");
			});
	});
	$(".additem").click(function(){			
		$("#myModal").modal();
	});	
	$("span#addprovider").click(function(){		
		$.post("detail_save.php",{'table':'1803hcprovider','name':$('#providername').val(),'contact':$('#contact').val(),'source':'apply'},function(data){
			var item=$.parseJSON(data);
			if(item.provider){						
				$("select#providerid option:selected").prop("selected",false);
				$("select#providerid option:last-child").after(item.provider);
				$("input.contact").val(item.contact);
				$("#myModal").modal("hide");
			}
		});
	});
	$("#providerid").change(function(e){
		var name=$(this).find("option:selected").val();		
		$.post("shenqing_autofill.php",{'table':'1803hcprovider','name':name},function(data){
			var item=$.parseJSON(data);
			$("input.contact").val(item.contact);
		});	
	});	
	$("#applyuserid").change(function(e){
		var name=$(this).find("option:selected").text();		
		$.post("shenqing_autofill.php",{'table':'1803hcuserinfo','name':name},function(data){
			var item=$.parseJSON(data);
			$("input#groupid").val(item.groupname);
		});	
	});
	
	var CheckType = function($ele){
		$ele.popover("destroy");
		var element=$ele.parent();//tr
		var name=$.trim($ele.text());	
		$.post("shenqing_autofill.php",{'table':'1803haocai','name':name},function(data){
			if(data){ 
				var item=$.parseJSON(data);
				element.children("td.spec").text(item.spec);
				element.children("td.unitprice").text(item.unitprice);				
				element.find("select.typeid").val(item.typeid);
				$("select#providerid").val(item.providerid);
				$("input.contact").val(item.contact);				
			}
		});
		//自动提示填写易制爆，易制毒，剧毒品申请单***********/
		element.find("select.typeid").prop("disabled",false);
		$.post("remote.php",{"name":$.trim(name),"type":"YZD"},function(data){// alert(data);
			var item=$.parseJSON(data);
			//alert(item.content);
			if (item.content!=""){	
				$ele.attr("data-content",name+item.content);
				$ele.popover("show");
				switch(item.key){
					case "isJDP":element.find("select.typeid").val(item.tid);element.find("select.typeid").prop("disabled",true);break;
					case "likeJDP":element.find("select.typeid").val(item.tid);break;
					case "isYZD":element.find("select.typeid").val(item.tid);element.find("select.typeid").prop("disabled",true);break;//YZD包括易制毒或易制爆
					case "isWHP":element.find("select.typeid").val(item.tid);element.find("select.typeid").prop("disabled",true);break;
					case "likeWHP":element.find("select.typeid").val(item.tid);;break;
					default:;
				}
			};				
		});
	}
	$(document).on('change', 'td.name',function(e) {//autofill function		
		var $ele=$(this);//td
		CheckType($ele);
	});
	$("p.submit").click(function(){
		/***validate budget*/			
			var bd = $.parseJSON($("#avBudget option:selected").val());
			var bdid = bd.bdid;
			var rest = bd.bdV - bd.spV;
			if(bdid == "-1") {alert("请选择预算经费！");return  false;}
			var tbdV=$("table#mainTable tfoot tr").children().eq(6).text();
			if(tbdV > rest) {alert("预算经费不足！");return  false;}			
		/******************/
		var statu =true;
		var whpList=new Array('试剂:易制毒','试剂:易制爆','试剂:剧毒品','试剂:危化品');
		var unitList="ml L g mg m mm";	
		if($("#providerid option:selected").text()=="未知") statu=confirm("供货商未知，继续吗？");
        if(statu){
			var applyuserid=$("#applyuserid").val(),
				urgent=($("#urgent").prop("checked")?1:0),
				buyuserid=$("#buyuserid").val(),	
				providerid=$("#providerid").val(),	
				invoicecode=$("#invoicecode").val(),	
				invoicedate=$("#invoicedate").val(),
				prepayment=$("#prepayment").val(),
				providernote=$("#providernote").val(),
				groupid = $("#groupid").attr("data-id");
			var item=new Array();		
			var items=new Array();
			var totalfee = 0;
			var checkFlag = true;

			$("table#mainTable tbody").children("tr").each(function(index,val){//清单中耗材列表
					var element=$(this);
					var name=element.find("td.name").text(),
						cas=element.find("td.cas").text(),
						unit=element.find("select.unit").val(),
						spec=element.find("td.spec").text(),
						unitprice=element.find("td.unitprice").text(),
						quantity=element.find("td.quantity").text(),
						selfbuy=element.find("select.selfbuy").val(),
						selfsave=element.find("select.selfsave").val(),
						typeid=element.find("select.typeid").val(),
						typeName=element.find("select.typeid option:selected").text(),
						source=element.find("select.source").val(),
						reason=element.find("td.reason").text(),
						projectid=element.find("select.projectid").val(),
						place=element.find("td.place").text(),
						hcnote=element.find("td.hcnote").text();
						totalfee += parseFloat(unitprice)*parseFloat(quantity);					

					if ($.trim(name)==""){
						alert("第"+(index+1)+"行:\n物品名不能为空");
						checkFlag = false;
						return false;
					}	
					//选择危化品，剧毒，易制毒，易制爆及标准品时CAS为必填
					if(typeid==1 || typeid==2 || typeid==3 || typeid==4 || typeid==16){
						if ($.trim(cas)==""){
						alert("第"+(index+1)+"行:\nCAS编码不能为空");
						checkFlag = false;
						return false;
						}
					}
					
					if ($.trim(unitprice)=="0" && selfbuy=="1"){
						alert("第"+(index+1)+"行("+name+"):\n自购物品估价不能为0");
						checkFlag = false;
						return false;
					}	
					/* if (parseFloat(unitprice)*parseFloat(quantity) >= 5000){
						alert("第"+(index+1)+"行("+name+"):\n单条记录不能超过5000元");
						checkFlag = false;
						return false;
					} */	
					if (selfsave=="1" && projectid == "0"){
						alert("第"+(index+1)+"行("+name+"):\n必须指定自存物品的所属项目！");
						checkFlag = false;
						return false;
					}
					//alert(typeName+','+unit);						
					/* if (whpList.indexOf(typeName)>-1){
						if(unitList.indexOf(unit)<0){
							alert("第"+(index+1)+"行("+name+"):\n危化品单位请选择国际标准单位：体积(ml,L)，重量(g,mg)，长度(m,mm)");
							checkFlag = false;
							return false;
						}
					} */
					item={
						"name":name,
						"cas":cas,
						"unit":unit,
						"spec":spec,
						"unitprice":unitprice,
						"quantity":quantity,
						"selfbuy":selfbuy,
						"selfsave":selfsave,
						"typeid":typeid,
						"source":source,
						"reason":reason,
						"projectid":projectid,
						"place":place,
						"hcnote":hcnote,					
						"providerid":providerid,
						"step":((prepayment==0)?'SQ':'RK'),//虚拟清单(prepayment='1'),不需要审核,直接将状态设置为'6'.
						"status":prepayment,//清单种类
						"bdid":bdid,//经费预算。
						"userid":applyuserid,
						"groupid":groupid,	
					};
					items.push(item);
			});

			if (!checkFlag) return;

			var applyinfo={//申请清单信息
				'applyuserid':applyuserid,
				'groupid':groupid,
				'urgent':urgent,
				'buyuserid':buyuserid,
				'providerid':providerid,
				'invoicecode':invoicecode,
				'invoicedate':invoicedate,
				'prepayment':prepayment,
				'providernote':providernote,
				'totalfee':totalfee,
				'step':'SQ'
				};
			if(prepayment==1) $.extend(applyinfo,{'success':'3'}); //虚拟清单(prepayment='1'),不需要审核,直接将状态设置为'3'.	
			//alert("OK2");
			// return;
			$.post("apply_save.php",{
									'applyinfo':applyinfo,
									'applyitems':items
				},function(data){
				if(data){
					// $('.msg').html(data); 
					location.href ="apply_print.php?id="+data;
				}
			});	
		}
	});
	$("input.datepicker").datepicker({format:"yyyy-mm-dd"});
	$("#pricesubmit").click(function(){		
		var srchtxt=$("#prices").val(),
		notzero=$("#iszero").prop("checked");
		if(srchtxt=="") {alert('请输入搜索内容。');return};		
		$.post("record.php",{"price":srchtxt,"notzero":notzero},function(data){
			var item=$.parseJSON(data);
			$("#priceresults").html(item.records);					
		});	
	});
	/* $(document).on('click', 'span.paddline',function(e) {		
		$("p#newline").click();
		var targetline=$('#mainTable').find("tbody tr:last-child");
		var currentline=$(this).parent().parent("tr");
		targetline.find('.name').text(currentline.find('.pname').text());	
		targetline.find('.spec').text(currentline.find('.pspec').text());	
		targetline.find('.unitprice').text(currentline.find('.punitprice').text());
		if(currentline.find('.ptype').text() =="试剂:剧毒品" |
		  currentline.find('.ptype').text() =="试剂:易制爆" |
		  currentline.find('.ptype').text() =="试剂:危化品" |
		  currentline.find('.ptype').text() =="试剂:易制毒")
		{
			targetline.find('select.typeid').prop("disabled",true);
		}else{
			targetline.find('select.typeid').prop("disabled",false);
		}	
	}); */
	</script>
<!-- Analytics
================================================== -->
  </body>
</html>