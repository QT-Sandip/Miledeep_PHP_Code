<?php

session_start();

include "../includes/config.php";
$LOT=$_GET['ID']; //JS READING VARIBLE
$LOT_ID=$_GET['ID'];


if(isset($_GET['Mix'])){
	$Mix=$_GET['Mix'];
}else{
	$Mix='0';
}


$SqlGradingHl="SELECT * FROM `PPC_GRADING_HL` where LOT_ID='$LOT_ID'";
$StmtGradingHl=$iconn->query($SqlGradingHl);
$RowGradingHl=$StmtGradingHl->fetch_array();



if( $RowGradingHl['IsMix']!='' && $RowGradingHl['MixIds']!='NA'){
 $LOT_ID=$RowGradingHl['MixIds'];
 $Mix=$RowGradingHl['IsMix'];
}



$USER_ID=$_SESSION['r_id'];

if($Mix==0){
$sql_LOTS="SELECT * FROM `inputmaterial_details` where id='$LOT_ID'";
$stmt_LOTS=$iconn->query($sql_LOTS);
$ROW_LOTS=$stmt_LOTS->fetch_array();

}else{
$IDS=str_replace('_' , ',' ,$LOT_ID);
 $sql_LOTS="SELECT * FROM `inputmaterial_details` where id in ($IDS)";
 $stmt_LOTS=$iconn->query($sql_LOTS);
$LotNumbers='';
$LotWeight='';
while($ROW_LOTS = $stmt_LOTS->fetch_array()){
$LotWeight+=$ROW_LOTS['WEIGHT_IN_KGS'];
if($LotNumbers==''){
$LotNumbers.=$ROW_LOTS['LOT_NO'];
$COUNT.=$ROW_LOTS['COUNT'];
}else{
$LotNumbers.=', '.$ROW_LOTS['LOT_NO'];
$COUNT.=', '.$ROW_LOTS['COUNT'];
}

}
$ROW_LOTS['LOT_NO']=$LotNumbers;
$ROW_LOTS['WEIGHT_IN_KGS']=$LotWeight;
$ROW_LOTS['COUNT']=$COUNT;

}




$GradingJson=$RowGradingHl['HOSO_W_DATA'];

if($GradingJson==''){
  $GradingJson=1;

 }else{
	 $GradingJson=json_decode($GradingJson,true);
 }


foreach($GradingJson  as $value){
				$ary_samples[]=$value;  // Colecting all weightments as single arrary

				if(!in_array($value['Count'],$ary_counts)){
				$ary_counts[]= $value['Count'];
				}
			}

			sort($ary_counts);




extract($_POST);
extract($_GET);



 ?>



<!DOCTYPE html>

<html>

<head>

<?php include "../includes/meta.php"; ?>

 </head>

<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">

<div class="wrapper">

<?php include "../includes/header.php"; ?>
  <!-- Left side column. contains the logo and sidebar -->
 <?php include "../includes/sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
   <section class="content-header">

<h2 align="center">HL Grading - <span style="color:Tomato; bold">
<?php echo $ROW_LOTS['LOT_NO'] ?></span>(<?php echo $ROW_LOTS['COUNT'] ?>C<?php// echo $ROW_LOTS['WEIGHT_IN_KGS'] ?>)</h3>

	</a>
		</a>

<ol class="breadcrumb">

        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">HL Grading</li>

      </ol>

    </section>


    <!-- Main content -->

    <section class="content">
      <!-- Small boxes (Stat box) -->



    <div id="tdetails" class="table-responsive" >
        <div class="box">
          <form role="form" method="post">
			    <div class="box-body">
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Grading Machine</label>
                 <input  class="form-control" name='GRADING_MACHINE' id="GRADING_MACHINE"
				  value="" placeholder="Enter GRADING MACHINE">
				</select>
                </div>


				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Enter Count</label>
                 <input required type="text" class="form-control" name='COUNT' id="COUNT"
				  value="" placeholder="Enter COUNT">
				</select>
                </div>

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Enter Weight</label>
                  <input required type="text" class="form-control" name='WEIGHT' id="WEIGHT"
				  value="" placeholder="Enter Weight">
                </div>


                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Enter No Of Crates</label>
                  <input required type="text" class="form-control" name='TUBS' id="TUBS"
				  value="" placeholder="Enter number of Crates">
                </div>


            </form>

			  <div class="col-xs-12 box-footer">

			     <div align="center" class="row" >
                <button id="Add" align="right" class='btn btn-success btn-sm btn-flat'><i class='fa fa-plus'>
				</i> Add</button>

                <button id="Submit"  align="right" class='btn btn-primary btn-sm btn-flat'><i class='fa fa-plus'>
				</i> Submit</button>

				<button id="delete"  align="right" class='btn btn-danger btn-sm btn-flat'><i class='fa fa-delete'>
</i> Delete</button>



			  </div>

              </div>


</div>




<div id="tdetails" class="table-responsive col-sm-5" >
    <div class="box">

        <div class="box-body">


  <table id="" class="table table-bordered table-hover" ALIGN="CENTER">
    <thead >
	 <tr >
	   <th align='center'>Grading Machine</th>
	   <td id="<?php  echo $row_HL_GRADING['ID'];?>" > <?php echo $GRADING_MACHINE; ?> </td>
	  </tr >
      <tr >

	  <th align='center' >S.No</th>
	  <th align='center'>Count</th>
	  <th align='center'>Total Weight</th>
	  
	  <th align='center'>Tubs</th>
	  <th align='center'>Actions</th>

      </tr>
    </thead>
    <tbody>

	<?php





	//echo $sql;
	$s_no=1;
$HlWeight=0;
	foreach($ary_counts as $count){

			$weight_sum=0;
			$Tubs=0;

			foreach($GradingJson  as $value){
			if($value['Count'] == $count){
				 $weight_sum=$weight_sum + $value['NetWeight'];
				 $Tubs++;
				$GRADING_MACHINE =$value['GRADING_MACHINE'];
			}
			}
$HlWeight+=$weight_sum;
	?>
	<tr >

	<td  id="<?php  echo $row_HL_GRADING['ID'];?>" > <?php echo $s_no; ?>    </td>
	<td id="<?php  echo $row_HL_GRADING['ID'];?>" > <?php echo $count; ?> </td>
	<td id="<?php  echo $row_HL_GRADING['ID'];?>" > <?php echo $weight_sum; ?> </td>
	
	<td id="<?php  echo $row_HL_GRADING['ID'];?>" > <?php echo $Tubs; ?> </td>


	<TD>
	<button id="<?php  echo $count;?>"
	onClick="reply_click(this.id)" class="glyphicon-remove btn btn-danger"> Delete</button>
	</TD>

    </tr>


	  <?php
$s_no++;
   }

	  ?>

	 <tr >

    <td></td>
	 <th>Total HL</th>
    <th><?php echo $HlWeight ?> Kgs</th>
    <th>Yield</th>
    <th> <?php // echo round(($HlWeight/$ROW_LOTS['WEIGHT_IN_KGS'])*100,2) ?>-- % </th>
     </tr>



     </tbody>

  </table>
</div>
</div>

</div>

    </section>

</div>



    <?php include "../includes/footer.php"; ?>
	<?php include "../includes/scripts.php"; ?>
 <?php include "../includes/AjaxLoader.php"; ?>

  </div>



</body>

<script>
$('document').ready(function(){

    	$(document).on('click','#Add',function(e){
		e.preventDefault();

			//alert('Hi');

			var LOT_ID='<?php echo $LOT_ID ?>';
			var USER_ID='<?php echo $USER_ID ?>';
			var Mix='<?php echo $Mix ?>';
			var GradingJson = <?php echo json_encode($GradingJson,true); ?>;

			if(GradingJson==1 || GradingJson==null){
				var passedArray=new Array();
			}else{
			    var passedArray = <?php echo json_encode($GradingJson,true); ?>;
			}
			var GRADING_MACHINE= $('#GRADING_MACHINE').val();
			var Count= $('#COUNT').val();
			var NetWeight= $('#WEIGHT').val();
			var TUBS= $('#TUBS').val();
			if(TUBS==''){
			TUBS=1;
			}


			var validation=false;

			if(Count==""){
			alert("count is empty");
			$('#COUNT').focus();
			}else if(NetWeight==""){
			alert("Weight is empty");
			$('#WEIGHT').focus();
			}else{
				validation=true;
			}


			if(validation==false){
			return;
			}







			var Defect= $('#Defect').val();
			var i=0;
			for(i=0; i < TUBS; i++ ){
				var arr = {
				'GRADING_MACHINE': GRADING_MACHINE,
				'Count': Count,
				'Defect': Defect,
				'NetWeight': NetWeight,
				'Sno': '1',
				'TimeStamp': '',
				'TotalWeight': NetWeight,
				'TraysWeight': '0.0'

			}

			passedArray.push(arr);
			}

			var HOSO_W_DATA=JSON.stringify(passedArray);

			var finalData = {
				'HOSO_W_DATA':HOSO_W_DATA,
				'USER_ID':USER_ID,
				'LOT_ID':LOT_ID,
				'TYPE_ID':'8',
				'DATA_TYPE':'2',
				'MIX': Mix,
				'GRADING_MACHINE':'GRADING_MACHINE',
				'From':'Web'
				};


		$.ajax({
		  url:'https://sme.silverlights.in/api/api.php?f=postHOSOWData',
		  type:'POST',
		  dataType: 'json',
		  data: JSON.stringify(finalData),
		  success:function(data){
			   var obj = JSON.parse(JSON.stringify(data));
			   alert(obj.status);
			 if(obj.status=='SUCCESS'){

				alert('Weights Succesfully');
				location.reload();
				}else{
				alert(obj.msg);
				location.reload();
				}
			}
			});
  });



	$(document).on('click','#delete',function(e){
e.preventDefault();

	var LOT_ID='<?php echo $LOT_ID ?>';

$.ajax({
	url:'AjDltAl.php',
  type:'POST',
  data:{'TYPE':'GRDNG_DELETE','LOT_ID':LOT_ID},
	success:function(data){
	 if(data=='SUCCESS'){

		alert('Deleted Succesfully');
		location.reload();
		}else{
		alert(data);
		location.reload();
		}
	}
	});
});


});
  </script>

<script>
  function reply_click(clicked_id) {

var LOT_ID='<?php echo $LOT ?>';



		// alert(clicked_id);
	//	 alert("No Access Pls Contact Admin");
	$.ajax({
		  url:'AjDltAl.php',
		  type:'POST',
		  data:{'TYPE':'HL_GRDNG_DELETE','LOT_ID':LOT_ID, 'COUNT' : clicked_id},
		  success:function(data){
			  console.log(data);

			 $("#CODE_LIST").html(data);
			 alert("Deleted");
			location.reload();
			}
			});
  }
</script>
<Script>

$('document').ready(function(){

	var LOT_ID='<?php echo $count ?>';
	//alert(LOT_ID);

	if(LOT_ID !=''){
	// document.getElementById("GRADING_MACHINE").readOnly  = true;
	 document.getElementById("GRADING_MACHINE").disabled = true;
	}




});

</script>
</html>
