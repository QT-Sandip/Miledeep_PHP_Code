<?php



session_start();

include "../includes/config.php";




$INVOICE_ID=$_GET['id'];

$SQL_CODE_LIST="SELECT * FROM `CODE_LIST_CREATE` where INV_ID='$INVOICE_ID'";
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);


$SQL_PO_LIST="SELECT * FROM `PO_VARIETY_LIST` where INV_ID='$INVOICE_ID'";
$STMT_PO_LIST=$iconn->query($SQL_PO_LIST);


$sql_invoice="SELECT * FROM `INVOICE_DATA` where ID='$INVOICE_ID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();




extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	$D_O_ANALYSIS = date("d-m-Y", strtotime($D_O_ANALYSIS));
	$D_O_COMPLEATION = date("d-m-Y", strtotime($D_O_COMPLEATION));
	
	$datas = explode('&',$FULL_NAME);
	 $FULL_NAME = $datas[0];
	 $SHORT_NAME = $datas[1];
	
	
	$PACKING = $T_PACKINGS. ' X ' .$E_PACKINGS.' ' .$UOM;
	
	 $sql_org_insert="INSERT INTO `CODE_LIST_CREATE`(`INV_ID`, `SHORT_NAME`, `FULL_NAME`, 
	`BRAND`, `T_O_MCS`, `GRADES`, `MCS`,`T_PACKINGS`,`E_PACKINGS`,`UOM`, `PACKING`, `GLAZE_PERC`, `DAY_CODE`, 
	`FARM_REG_ID`,`NAME_OF_FARMER`,`ADD_OF_FARMER`)
	VALUES ('$INVOICE_ID','$SHORT_NAME','$FULL_NAME','$BRAND','$T_O_MCS','$GRADES',
	'$MCS','$T_PACKINGS','$E_PACKINGS','$UOM','$PACKING','$GLAZE_PERC','$DAY_CODE','$FARM_REG_ID',
	'$NAME_OF_FARMER','$ADD_OF_FARMER' )";
	
	

	$stmt1=$iconn->query($sql_org_insert);
	$M_T_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('CODE_LIST_CREATE', 'CODE_LIST_ID', $M_T_REPORT_ID, $_POST, ['Remarks' => 'Code List Created'], 'QualityControl');
		
			 $SQL_INV_UPDATE="UPDATE `INVOICE_DATA` SET 
				`DEC_STATUS`='1'
				WHERE ID='$INVOICE_ID'";
	
	
	$STMT_INV_UPDATE=$iconn->query($SQL_INV_UPDATE);
		
		
		header('Location: DocAddReports.php?id='.$INVOICE_ID);
	}
}

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

        

<h2 align="center">CODE LIST - <span style="color:Tomato; bold"><?php echo $invoice_details['PO_NUM'] ?></span></h3>



	</a>

			</a>



<ol class="breadcrumb">

        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">CODE LIST</li>

      </ol>

    </section>

	
	
	
	<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title modheading"></h4>
            </div>
            <div class="modal-body checkdiv">
			

			
            
			</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->
	  
	  

	  
	 
    <div id="tdetails" class="table-responsive" >  

        <div class="box">
		
		
          <form role="form" method="post">
		  
		
              
			    <div class="box-body">
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">DAY CODES</label>
                  <input required type="text" class="form-control"  name="DAY_CODE" id="DAY_CODE"
				  placeholder="Enter DAY CODE">
                </div>
				
				
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">VARITY (FULL NAME)</label>
                 
				  <select class="form-control" name="FULL_NAME" id="FULL_NAME">
				
				<?php while($row=$STMT_PO_LIST->fetch_array()){ ?>				
				<option value="<?php echo $row['ID'].'&'.$row['TYPE'];?>"><?php echo $row['FULL_NAME'];?></option> 
				<?php } ?>				
				
				</select>
				 
                </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">TOTAL NO OF SLABS IN CARTON</label>
                  <input required type="number" class="form-control" name='T_PACKINGS' id="T_PACKINGS" 
				  placeholder="Enter TOTAL  PACKINGS">
                </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">WEIGHT OF EACH SLAB</label>
                  <input required type="number" class="form-control" name='E_PACKINGS' id="E_PACKINGS" 
				  placeholder="WEIGHT OF EACH PACKING">
                </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Unit Of Measurement</label>
                  <select class="form-control" name="UOM" id="UOM">
				  <option value="KGS">KGS</option>
				  <option value="LBS">LBS</option>				  
				  <option value="GRAMS">GRAMS</option>				  
				  </select>
                </div>
				
				
              
			
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">GLAZE PERCENTAGE</label> 
                  <input required type="number" class="form-control" name='GLAZE_PERC' id="GLAZE_PERC"
				  placeholder="Enter GLAZE PERCENTAGE">
                </div>
				
				
				
				
				  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">FARM REG NUMBER</label>
                  <input required autocomplete="off" type="text" class="form-control" name='FARM_REG_ID' id="FARM_REG_ID"
				   placeholder="Enter FARM REG NUMBER">
				   
				   <div class=" drop-down-menu result"></div>
                </div>
				
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BRAND</label>
                  <input required type="text" class="form-control" name="BRAND" id="BRAND" 
				  placeholder="Enter BRAND" value="<?php echo $invoice_details['BRAND']?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  
				  <label for="exampleInputEmail1">GRADES</label>
                  <input required type="text" class="form-control" name="GRADES" id="GRADES"
				  placeholder="Enter GRADES">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">MASTER CARTONS</label>
                  <input type="text" class="form-control"  name="MCS" id="MCS"
				  placeholder="Enter MASTER CARTONS EX 12,13">
                </div>
				
				
				<div class="form-group col-sm-3" >
                  
				  <label for="exampleInputEmail1">TOTAL NUMBER OF MASTER CARTONS</label>
                  <input required type="text" class="form-control" name="T_O_MCS" id="T_O_MCS" 
				   placeholder="Enter TOTAL NUMBER OF MASTER CARTONS">
                </div>
				
				
				
				
   
               
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label>
                   <input  type="text" class="form-control" name='VERIFIED_BY' id="VERIFIED_BY" 
				   placeholder="Enter Verified by - Designation"  value="<?php echo $invoice_details['VARIFIED_ID']; ?>">
   
               
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Name Of Farmer </label>
                   <input required type="text" class="form-control" name='NAME_OF_FARMER' id="NAME_OF_FARMER" 
				   placeholder="Enter Name Of Farmer"  value="<?php echo $invoice_details['VARIFIED_ID']; ?>">
				</div>
				
				
				
				
				<div class="form-group col-sm-12" >
                  <label for="exampleInputEmail1">Address Of Farmer </label>
                   <input required type="text" class="form-control" name='ADD_OF_FARMER' id="ADD_OF_FARMER" 
				   placeholder="Enter Address Of Farmer"  value="<?php echo $invoice_details['VARIFIED_ID']; ?>">
				</div>
               
			   <div class="col-xs-12 box-footer"  align="right">
              
				
				<button type="submit"  name='submit' class="btn btn-primary">ADD TO CODE LIST</button>
			  <a  id='eia_report' href="DocAddReports.php?id=<?php echo $INVOICE_ID ?>" class="btn btn-primary">SUBMIT RESULT </a>
              </div>
            </form>



</div>


</div>

<div id="tdetails" class="table-responsive" >  
        <div class="box">
          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover">
              

    <thead>

      <tr>
	  
	  <th>Day Code</th>
	  <th>Variety</th>
	  <th>Type of Packing</th>
	  <th>Glaze Percentage</th>
	  <th>Fram Reg Number</th>
	  <th>Brand</th>
	  <th>Grades</th>
	  <th>Total Mcs</th>
	  	
      </tr>

    </thead>

    <tbody>

	<?php

	//echo $sql;

   while($row=$STMT_CODE_LIST->fetch_array()){

	?>
	<tr>
			
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['DAY_CODE']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['SHORT_NAME']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['PACKING']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['GLAZE_PERC']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['FARM_REG_ID']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['BRAND']; ?></td>
    <td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['GRADES']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['T_O_MCS']; ?></td>
       

        </tr>

	  <?php 

   }
	  ?>

     </tbody>

  </table>
</div>

</div>

    </section>
	
	</div>

    <!-- /.content -->
  <!-- /.content-wrapper -->

   <?php include "footer.php"; ?>

  </div>

</body>
<Script>

$('document').ready(function(){
    var arr=[];


$('#FULL_NAME').change(function() {
	var test=this.value;
		alert(test);
});

    	
    	$('#continuebtn').on('click',function(){
    
    		$('form').submit();
    	});
    });    
$('#VARITY').click(function(e) {
     //alert('clicked');
	 $('#myModal').modal('toggle');
//$('.modal fade').modal('show');
//$('#myModal').modal('hide');
 });
 $('#ar').click(function(e) {
     //alert('clicked');
	 $('#myModal1').modal('toggle');
//$('.modal fade').modal('show');
//$('#myModal').modal('hide');
 });
 
 
  var ar=[];
  var type=[];
	$('.checkdiv1 input').change(function() {
  if (this.checked) {
    ar.push(this.value);
	 type.push(this.id);
  }
  else {
   ar.splice(ar.indexOf(this.value), 1);
  type.splice(type.indexOf(this.value), 1);
  
 }
  $('#ar').val(ar + '');
 $('#type').val(type + '');

  });
</script>




<Script>

$('document').ready(function(){

  //alert('hiiiii');
    $('#FARM_REG_ID').on("keyup", function(){
        /* Get input value on change */
		//alert('hiiii');
        var inputVal = $(this).val();
		
		//alert(inputVal);
		var resultDropdown = $(this).siblings(".result");
        if(inputVal.length){
            $.get("AjaxFarmerSearch.php", {term: inputVal}).done(function(data){
                // Display the returned data in browser
				
				//console.log('test');
				//console.log(data);
				//alert(data);
                resultDropdown.html(data);
            });
        } else{
            resultDropdown.empty();
        }
		
    });
	
	    // Set search input value on click of result item
$(document).on("click", ".result p", function(){
        var iid=$(this).attr('id');
//alert(iid);
$(this).parents(".username").find('#cnameid').val($(this).text());
$(this).parents(".username").find('#cnameid1').val(iid);

        $(this).parent(".result").empty();
         //alert(cat);
		$.ajax({
			type:'POST',
			url:'AjaxFarmerDetails.php',
			data:{'id':iid},
		success:function(data){
			//alert(data);
			console.log(data);
var obj = JSON.parse(data);

var REG_NO=obj.Farm_ID;
//ALERT(REG_NO);
var res = REG_NO.split("/");
var F_NAME=obj.Farmer_Name;
var F_pond_id=obj.No_of_Ponds;
var F_ADD=obj.Address;
var F_ADD_C=obj.Address_continued;
//alert(F_ADD_C);

$('#FARM_REG_ID').val(res[0]+'/00'+F_pond_id);
$('#NAME_OF_FARMER').val(F_NAME);
$('#ADD_OF_FARMER').val(F_ADD+F_ADD_C); 

		} 		
	});
$(this).parent(".result").empty();
        
});

});   	

</script>

</html>
