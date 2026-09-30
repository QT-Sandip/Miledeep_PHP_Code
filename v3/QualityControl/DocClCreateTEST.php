<?php
  $QueryParm=$_GET['Q'];  
  $QueryParms=$_GET['Q'];  

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

extract($_POST);
extract($_GET);


$ClientID=$_SESSION["ClientID"];


if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  
  
 $PoID=$_GET['PoID'];


$SqlTrace="SELECT * FROM $TblQcTracebilityList   ORDER by ABS(Tracebility)";
$StmtTrace=$iconn->query($SqlTrace);

$SqlCodeList="SELECT * FROM $TblQcCodeList where InvID='$PoID' ORDER BY CLID ASC";
$StmtCodeList=$iconn->query($SqlCodeList);

 $SqlLastInserted="select * from $TblQcCodeList where InvID='$PoID' order by CLID desc limit 1";
$StmtLastInserted=$iconn->query($SqlLastInserted);
$RowLastInserted=$StmtLastInserted->fetch_array();


$SqlPoList="SELECT *, SPV.ShortName as PoShortname FROM `ScrmPoGradeData` as SPD
  left join ScrmPIVarietyList As SPV on SPD.VarietyID=SPV.VarietyID
left join AdminPackingStyles as APS on SPD.PackingStyleID=APS.PCKID
WHERE  SPD.InvId='$PoID'";
$StmtPoList=$iconn->query($SqlPoList);
$StmtPoList1=$iconn->query($SqlPoList);
$StmtPoList2=$iconn->query($SqlPoList);


 $SqlInvoice="SELECT * FROM $TblScrmPoData where PoID='$PoID'";
$StmtInvoice=$iconn->query($SqlInvoice);
$InvoiceDetails=$StmtInvoice->fetch_array();

 $SqlLabUsers="SELECT * FROM $TblQcLabTechnologist where LbuStatus=1";
$StmtLabUsers=$iconn->query($SqlLabUsers);

 $SqlCount="SELECT count(*) as count FROM $TblQcCodeList WHERE InvID='$PoID'";
$StmtCount=$iconn->query($SqlCount);
$RowCount=$StmtCount->fetch_array();

 $Count=$RowCount['count']; 
$PageTitleFolder="Quality Control";
$PageTitle="CodeList - ".$InvoiceDetails['PoNo'];  


extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){ 
	$LoginId=$_SESSION["UserID"];

	$DtofAnalysis = date("d-m-Y", strtotime($AnalysisDate));
	$DtofCompletion = date("d-m-Y", strtotime($ReportDate));
	$PostedDate = date("Y-m-d");
	
	//$VarietyID;
	 $SqlQcvariety="SELECT QPV.VarietyID as QcVarietyID FROM `QcPoVarietyList` as QPV
	 left join `ScrmPoGradeData` as SPD on SPD.VarietyID=QPV.YieldID
	 WHERE QPV.InvID='$PoID' and ID='$VarietyID'";
	$StmtVarietyId=$iconn->query($SqlQcvariety);
	$RowVarietyId=$StmtVarietyId->fetch_array();
	 $QcVarietyID=$RowVarietyId['QcVarietyID'];
	
	
	 $sql_org_insert="INSERT INTO `$TblQcCodeList` (`InvID`, `VarietyID`, 
	`Brand`, `McsTotal`, `Grades`, `McsGrade`,  `GlazePerc`, `DayCode`, 
	`TracebilityID`,`VerifiedBy`,`Yeild`,`PackingStyleID`,`BuyerCode`,`JulianCode`)
	VALUES ('$PoID','$QcVarietyID','$Brand','$McsGrade','$Grade',
	'$McsGrade','$GlazePerc','$DayCode','$TracebilityID','$VerifiedBy','$Yeild','$PackingStyle','$BuyerCode','$JulianCode')";					

	$stmt1=$iconn->query($sql_org_insert);
	$M_T_REPORT_ID = $iconn->insert_id;
	if($stmt1){
		legacyAuditLogInsertById($TblQcCodeList, 'CLID', $M_T_REPORT_ID, $_POST, ['Remarks' => 'Code List Created'], 'QualityControl');
	}
			header('Location: DocClCreate.php?Q='.$QueryParm);
	}

 ?>



<!DOCTYPE html>

<html lang="en">
<link href="<?php echo  $href; ?>"  rel="stylesheet" type="text/css" />

    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark" > <!--class="vertical-collpsed"-->

	
	 <?php		include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">


            <?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>



 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
					

                        <?php include ($IncludesPath."page-title.php");?>

	<?php include "PopUpTable.php"; ?>

<div class="modal fade" id="myModal" role="dialog">

    <div class="modal-dialog">

        <!-- Modal content-->

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal">&times;</button>

                <h4 class="modal-title modheading"></h4>

            </div>

            <div class="modal-body">


			<div class="box-body" id="ATT_DATA">
              
            </div>




            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				
				

            </div>

        </div>

    </div>

</div>




          
	
       <div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form"  id="PalletForm" method="post" onsubmit="return validateForm()">
                                    <div class="card-body">
									
										<div class="row">
			
               <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Day Code</label>
                  <input required type="text" class="form-control"  name="DayCode" id="DayCode"
				  placeholder="Enter Day Code" value="<?php echo $RowLastInserted['DayCode'];?>">
                </div>
				
                    <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Variety</label>
                 
				  <select class="form-control select2" name="VarietyID" id="VarietyID" required>
				  				   <option value="">Select Variety</option>

				
				<?php while($RowPoList=$StmtPoList->fetch_array()){?>

				<option  value="<?php echo $RowPoList['ID'];?>"><?php echo $RowPoList['PoShortname'].'('.$RowPoList['Grade'].'-'.$RowPoList['PackingStyle'].')';?></option> 
				
				<?php } ?>				  
				
				</select>
				 
                </div>
				
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Grade</label>
                 <input hidden type="text" id="Grade" name="Grade"  class="form-control hidden" value="">
				  <select class="form-control select2" name="Grades" id="Grades" required>
				  				   <option value="">Select Grade</option>

				
				<?php while($RowPoList=$StmtPoList1->fetch_array()){	
					?>

				<option value="<?php echo $RowPoList['Grade'];?>"><?php echo $RowPoList['Grade'];?></option> 
				
				<?php } ?>				  
				
				</select>
				 
                </div>

				  
				<!--<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Grades</label>
                  <input required type="text" class="form-control" name="Grades" id="Grades" 
				  placeholder="Enter Grades" value="<?php echo $RowLastInserted['Grades']?>">
                </div>-->
				

				
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Packing Style</label>
				  <input hidden type="text" id="PackingStyle" name="PackingStyle"  class="form-control hidden" value="">
                   <select class="form-control select2" name="PackStyle" id="PackStyle" required>
				   
		         <option value="">Select Packing Style</option>

				   <?php while($RowPacking=$StmtPoList2->fetch_array()){ ?>
				    
				   <option  value="<?php echo $RowPacking['PCKID']; ?>"><?php echo $RowPacking['PackingStyle']; ?></option>
				   
				   <?php } ?>
		  
				  </select>
                </div>
				
							
								
								<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Brand</label>
                  <input required type="text" class="form-control" name="Brand" id="Brand" 
				  placeholder="Enter Brand" >
                </div>
					
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Yield</label>
                  <input required type="text" class="form-control" name="Yeild" id="Yeild" 
				  placeholder="Enter Yield" value="<?php echo $RowLastInserted['Yeild']?>" value="<?php echo $RowLastInserted['Yeild'];?>">
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Glaze(%)</label> 
                  <input required type="number" class="form-control" name='GlazePerc' id="GlazePerc"
				  placeholder="Enter Glaze Percentage" >
                </div>
				
			
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Mcs</label>
                  <input required type="Number" class="form-control"  name="McsGrade" id="McsGrade" 
				 
				  placeholder="Enter Mcs" >
                </div>
				
				
				<!--<div class="form-group col-lg-3" >
                  
				  <label for="exampleInputEmail1">Total No of MCS</label>
                  <input readonly required type="Number" class="form-control" name="McsTotal" id="McsTotal" 
				   placeholder="Enter Total No of MCS" value="<?php echo $RowLastInserted['McsTotal'];?>">
                </div>-->
				
				
 <?php  if($Count==0){?>
               
               <!-- <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Verified By </label>
				   <select class="form-control select2" name="VerifiedBy" id="VerifiedBy" required>
				   <option value="">Select Lab User Name</option>
				   <?php while ($RowLabUsers=$StmtLabUsers->fetch_array()){ 
					   if($RowLabUsers['LbuName']==$RowLastInserted['VerifiedBy']){
					?>
									
				<option <?php  ?> selected=="selected" value="<?php echo $RowLabUsers['LbuName'];?>"><?php echo $RowLabUsers['LbuName'];?></option> 
				<?php }else{ ?>	
				<option <?php  ?> selected=="selected" value="<?php echo $RowLabUsers['LbuName'];?>"><?php echo $RowLabUsers['LbuName'];?></option> 
				
				<?php }?>	
				       <?php } ?>
				
						  
				  </select>
               
                </div>-->
 <?php } ?>	
 
 
 				<div class="form-group col-lg-3">  

                  <label for="exampleInputPassword1">Traceability</label>
				      <button href="#" id="AddTraceability" class="btn btn-success"><b> NEW </b></button>

                   <select class="form-control select2" name="TracebilityID" id="TracebilityID" required>
				  <option value="">Select Farm Reg Number</option>
				   <?php while ($RowTrace=$StmtTrace->fetch_array()){ 	 ?>
				     
						<option value="<?php echo $RowTrace['TraceID'] ?>">
					 <?php echo $RowTrace['Tracebility']?></option>
				   				   
				   <?php  }?>	  
				  </select>
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Name of Farmer </label>
                   <input required readonly type="text" class="form-control" name='NAME_OF_FARMER' id="NAME_OF_FARMER" 
				   placeholder="Enter Name Of Farmer"  >
				</div>
				
				
				<div class="form-group col-lg-6" >
                  <label for="exampleInputEmail1">Address of Farmer </label>
                   <input required readonly type="text" class="form-control" name='ADD_OF_FARMER' id="ADD_OF_FARMER" 
				   placeholder="Enter Address Of Farmer"  >
				</div>
				</div>
               
			           
	           <div class="form-group col-lg-12">
			  
			    <hr>
			    <div class="row">  
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Buyer Code</label>
                  <input  type="text" class="form-control"  name="BuyerCode" id="BuyerCode"
				  placeholder="Enter Buyer Code" >
                </div>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Julian Code & Internal Code</label>
                  <input  type="text" class="form-control"  name="JulianCode" id="JulianCode"
				  placeholder="Enter Julian Code & (Internal code)" >
                </div>
                  </div>
                  </div>
                  
  <div class="col-xs-12 box-footer"  align="right">
                
			 <!-- <a  id='btn_close' class="btn btn-danger">Close</a>-->
			  <a  href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>
          				<button type="submit"  name='submit' class="btn btn-success">Save</button>




				</div>
              <!-- /.box-body -->


            </form>
			</div>
			</div>
			<div class="row">
			 <div class="col-12">
			 <div class="card">
			 <div class="card-body" id="ShowDataTable">
			 
			 <table  id="key-datatable" data-paging="false" 
	   class="table table-bordered  nowrap w-100" >
	   <thead class="thead-light">

      <tr>
	  
	  <th>Day Code</th>
	  <th>Variety</th>
	  <th>Grade</th>
	  <th>Packing Style</th>
	  <th>Brand</th>
	  <th>Glaze %</th>
	  <th>Total Mcs</th>
	  <th>Farm Reg Number</th>
	  <th>Buyer Code</th>
	  <th>Actions</th>
	  
	
	  	
      </tr>

    </thead>

    <tbody>

	<?php
while($RowCodeList=$StmtCodeList->fetch_array()){
	$CLID=$RowCodeList['CLID'];

$CodeListId=$RowCodeList['TracebilityID'];
$PackingStyleID=$RowCodeList['PackingStyleID'];
$VarietyID=$RowCodeList['VarietyID'];
 
 $SqlVariety="SELECT * FROM $TblQcPoVarityList where VarietyID='$VarietyID'";
 $StmtVariety=$iconn->query($SqlVariety);
$RowVariety=$StmtVariety->fetch_array();
 $ShortName=$RowVariety['ShortName'];
 
 
 $SqlPackingStyle="SELECT * FROM $TblAdminPackingStyles where PCKID='$PackingStyleID'";
 $StmtPackingStyle=$iconn->query($SqlPackingStyle);
$RowPackingStyle=$StmtPackingStyle->fetch_array();
 $PackingStyle=$RowPackingStyle['PackingStyle'];
 
 
 
 $SqlTracebility="SELECT * FROM `$TblQcTracebilityList` WHERE TraceID='$CodeListId'";
$StmtTracebility=$iconn->query($SqlTracebility);
$RowTrace=$StmtTracebility->fetch_array();
 
 
//$FarmTracebility=mb_strimwidth($RowTrace['Tracebility'], 0, 10, "...");
$FarmTracebility=$RowTrace['Tracebility'];
	$QueryParms='PoID='.$PoID.'&CLID='.$CLID;
$QueryParm=urlEncode64($QueryParms);
	?>
	
	
	<tr>
   <td> <?php echo $RowCodeList['DayCode']; ?></td>
   <td> <?php echo $ShortName; ?></td>
   <td> <?php echo $RowCodeList['Grades']; ?></td>
   <td> <?php echo $PackingStyle; ?></td>
   <td> <?php echo $RowCodeList['Brand']; ?></td>
   <td> <?php echo $RowCodeList['GlazePerc']; ?></td>
   <td> <?php echo $RowCodeList['McsGrade']; ?></td>
	<td> <?php echo $FarmTracebility; ?></td>
	<td> <?php echo $RowCodeList['BuyerCode']; ?></td>
	
	<td> <a target="_blank" href="DocClEdit?Q=<?php echo $QueryParm; ?>" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Edit" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i></a>
	 <button class="btn btn-link text-danger Delete" data-id="<?php echo $CLID; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button></td> 						

     

        </tr>

	  <?php 

   }
	  ?>

     </tbody>

  </table>

			
			 </div>
			 </div>
			 </div>



             </div> <!-- end row -->
    
	</div> <!-- end card body-->
	
	
  
  
    </section>
	
	</div>

                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		<?php include ($IncludesPath."ShowDataTable.php");?>
		
  
       
    </body>
</html>	
<Script>

$('document').ready(function(){
showData(data);
   }); 


$(document).on('click', '.Delete', function(e){
		e.preventDefault();

  var PoID='<?php echo $PoID?>';
	 var ClickedId =$(this).data('id');
  
		// alert("Are you sure to delete this Daycode?");
 var r = confirm("Are You Sure Do You Want To Delete?");
  if (r == true) {		
		$.ajax({
		  url:'Ajax/AjaxBDdelete.php',
		  type:'POST',
		  data:{'Type':'CodeListDelete','D_ID':ClickedId,'INVOICE_ID':PoID},
		  success:function(data){
			  var obj = JSON.parse(JSON.stringify(data));  
		 if(obj.error == null){
			 alert("Deleted Successfully");
						 //showData(data);

			location.reload();
		 }else{
			 alert(obj.error);
		 }
			 
			}
			});	
}else{}			
  });

   /*$('#McsGrade').change(function(){
	 var array = $('#McsGrade').val().split(",");
	  for (var i = 0, sum = 0; i < array.length; sum += parseInt(array[i++]));
	  $('#McsTotal').val(sum);
	  
	   
});*/


  $('#TracebilityID').change(function(){
	document.getElementById("NAME_OF_FARMER").disabled=true;
	document.getElementById("ADD_OF_FARMER").disabled=true;
	  		
	
var FarmId= $('#TracebilityID').val();

   $.ajax({
    type: 'POST',
    url: 'Ajax/AjaxFarmerList.php',
	dataType: 'json',
    data: {'Type':'FARMERS','FarmId' : FarmId},  
    success: function(response){
		 var myJSON = JSON.parse(JSON.stringify(response));
		// console.log(myJSON);
		 //alert(myJSON['Farmer_Name']); 
		 $("#NAME_OF_FARMER").val(myJSON['FarmerName']);
		 $("#ADD_OF_FARMER").val(myJSON['AddressContinued']);
		
	 }
  });
  
  
 });
 
 
 $('#McsGrade').change(function(){

var McsGrade= $('#McsGrade').val(); 
var PoBreakupID= $('#VarietyID').val(); 
var Grades= $('#Grades').val(); 
//alert(VarietyID);
var PoId='<?php echo $PoID; ?>';

   $.ajax({
    type: 'POST',
    url: 'Ajax/AjaxDependantDropDowns.php',
	data: {'Type':'McsGradeValue','McsGrade' : McsGrade,'PoId' : PoId,'PoBreakupID' : PoBreakupID,'Grades' : Grades},  
    success: function(response){
var data = JSON.parse(response);
			if(data['sts'] == 'failed'){
			alert(data['msg']);
			$('#McsGrade').focus();
			
			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
							location.reload();

				
			}
		
	 }
  });
  
  });


 $('#VarietyID').change(function(){
var VarietyID= $('#VarietyID').val(); 
//alert(VarietyID);
var PoId='<?php echo $PoID; ?>';
document.getElementById("Grades").disabled=true;
	document.getElementById("PackStyle").disabled=true;
	document.getElementById("GlazePerc").readOnly=true;
	

   $.ajax({
    type: 'POST',
    url: 'Ajax/AjaxDependantDropDowns.php',
	dataType: 'json',
    data: {'Type':'CodeListAutoFillValues','VarietyID' : VarietyID,'PoId' : PoId},  
    success: function(response){
	 var myJSON = JSON.parse(JSON.stringify(response));

		
	
		 $("#Grade").val(myJSON['Grade']);		 
		 $("#Grades").val(myJSON['Grade']).change();		 
		 $("#PackStyle").val(myJSON['PackingStyleID']).change();
		 $("#PackingStyle").val(myJSON['PackingStyleID']);
		 $("#GlazePerc").val(myJSON['GlazePerc']);
		 $("#McsGrade").val(myJSON['Mcs']);
		 $("#Brand").val(myJSON['Brand']);
		
	 }
  });
  });


 	

</script> 


<Script>


$(document).on('click','#AddTraceability',function(e){			
			e.preventDefault();
			
		$.ajax({
		   url:'Ajax/AjaxPopupAddTraceability.php',
		  type:'POST',
		  data:{'Type':'ADDTRACEABILITY',},
		  success:function(data){
			 
			$("#PopUpTable").html(data);
			$('#myModal').modal('show');
					}
			});
			//}
    	});	
		
			  	
$(document).on('click','.ButtonAddTrace',function(e){
	e.preventDefault();
		var Tracebility=$('#Tracebility').val();
		var FarmerName=$('#FarmerName').val();	
		var AddressContinued=$('#AddressContinued').val();
		var RegAuthority=$('#RegAuthority').val();
		var TracebilityID=$('#TracebilityID').val();
	
		if(Tracebility==''){
			alert('Please Enter Tracebility');
			return;	
		}if(FarmerName==''){
			alert('Please Enter FarmerName');
			return;	
		}if(AddressContinued==''){
			alert('Please Enter AddressContinued');
			return;	
		}
		
		
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxNewFarmer.php',
    data: {'Tracebility':Tracebility,'FarmerName':FarmerName,'AddressContinued':AddressContinued,
	'RegAuthority':RegAuthority,'Type':'postNewTracebility'},
	
    
    success: function(response){	
		var data=JSON.parse(response);
			if(data['Error']==null){
			$('#myModal').modal('hide');
			alert(data['Msg']);
			$("#TracebilityID").html(data['TracebilityList']);
			}else{
			
			alert(data['Error']);
			}
			
			
	 }
  });

	
});	

$('document').ready(function(){

  //alert('hiiiii');
    $('#FARM_REG_IDd').on("keyup", function(){
        /* Get input value on change */
		//alert('hiiii');
        var inputVal = $(this).val();
		
		//alert(inputVal);
		var resultDropdown = $(this).siblings(".result");
        if(inputVal.length){
            $.get("AjaxFarmerList.php", {term: inputVal}).done(function(data){
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
			url:'AjaxFarmerList.php',
			data:{'id':iid,type:'detils'},
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

$('#FARM_REG_ID').val(res[0]+'/00');
$('#NAME_OF_FARMER').val(F_NAME);
$('#ADD_OF_FARMER').val(F_ADD+F_ADD_C); 

		} 		
	});
$(this).parent(".result").empty();
        
});

});   	

</script>

