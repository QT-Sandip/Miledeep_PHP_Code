<?php


session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

extract($_POST);
extract($_GET);
$Report_id=$_GET['ReportID'];

if($Report_id==''){
	$PageTitle="IN HOUSE MICROBIOLOGY TEST REPORT" ;

}
else{
		$PageTitle="IN HOUSE MICROBIOLOGY TEST REPORT" ;

}
$PageTitleFolder="Quality Control";




$SqlPoData="SELECT * FROM `$TblScrmPoData` LEFT JOIN $TblScrmConsigneeDetails AS SCRM ON SCRM.ConID=ConsigneeID
 order by PoID DESC";
$StmtPoData=$iconn->query($SqlPoData);


$SqlLabUsersList="SELECT * FROM `QcLabTechnologist` where LbuStatus='1'";
$stmt_LAB_USERS=$iconn->query($SqlLabUsersList);
$stmt_LAB_USERS1=$iconn->query($SqlLabUsersList);


$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE TYPE='MB_PARAMETERS' ORDER BY SortOrder asc";
$stmt_OPTIONS=$iconn->query($SQL_OPTIONS);	

WHILE($ROW=$stmt_OPTIONS->FETCH_ARRAY()){
	 $AryReferenceValues[]=$ROW['OPTIONS'];
}

$Sql_Parameters="SELECT * FROM `QcIHMBParmResult` WHERE ReportID='$Report_id'"; 
$Stmt_Parameters=$iconn->query($Sql_Parameters);

 WHILE($Row_Parameters=$Stmt_Parameters->FETCH_ARRAY()){
	 $CheckValue=$Row_Parameters['Parameter'];
	 if(in_array($CheckValue,$AryReferenceValues,true)){
		 $ValueIndex= array_search($CheckValue,$AryReferenceValues);
		  unset($AryReferenceValues[$ValueIndex]);
	 }
	
}


$SqlMBResult="SELECT mb.ID as id ,mb.Parameter as Parameter ,mb.AnalysisDate as AnalysisDate ,mb.Result as Result ,mb.InvID as InvID ,mb.ReportID as ReportID ,
inv.PoNo as PoNo,scrm.ConsigneeName as ConsigneeName FROM `QcIHMBParmResult` as mb LEFT JOIN $TblScrmPoData as inv on inv.PoID=mb.InvID 
LEFT JOIN ScrmConsigneeDetails as scrm on scrm.ConID=inv.ConsigneeID where ReportID='$Report_id'";

$StmtMBResult=$iconn->query($SqlMBResult);
$RowMB=$StmtMBResult->fetch_array();
$StmtMBResult=$iconn->query($SqlMBResult);


$today_date=date('Y-m-d');

extract($_POST);
extract($_GET);


if(isset($_POST['submit'])){
	
      $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
      $TIME_STAMP=$dateTime->format("Y-m-d  H:i:s"); 
	 
if($Parameter=='TPC'){
$SortOrder=1;	
$AnalysCmplDates='1 DAY';
	
}
	ELSE if($Parameter=='E.COLI'){
$SortOrder=2;	
$AnalysCmplDates='1 DAY';
	
}


ELSE if($Parameter=='SALMONELLA'){
$SortOrder=3;	
$AnalysCmplDates='4 DAYS';
	
}

ELSE if($Parameter=='V.CHLOREA'){
$SortOrder=4;	
$AnalysCmplDates='1 DAY';
	
}

ELSE if($Parameter=='V.PARAHAEMOLYTICUS'){
$SortOrder=5;	
$AnalysCmplDates='1 DAY';
	
}

ELSE if($Parameter=='STAPHYLO COCCUS'){
$SortOrder=6;	
$AnalysCmplDates='1 DAY';
	
}

ELSE if($Parameter=='LISTERIA'){
$SortOrder=7;	
	$AnalysCmplDates='4 DAYS';
}
		
 $SqlInsert=" INSERT INTO `QcIHMBParmResult`( `ReportID`,`Parameter`, `AnalysisDate`, 
 `Result`, `InvID`, `SortOrder`,`AnalysCmplDates`)  VALUES ('$Report_id','$Parameter',
 '$AnalysisDate','$Result','$InvID','$SortOrder','$AnalysCmplDates')"; 
	$stmt1=$iconn->query($SqlInsert);
	$MB_REPORT_ID = $iconn->insert_id;

	if($stmt1){
		legacyAuditLogInsertById('QcIHMBParmResult', 'ID', $MB_REPORT_ID, $_POST, ['Remarks' => 'MB Report Result Added'], 'QualityControl');
	
		header('Location: IHAddMBResult.php?ReportID='.$Report_id);
	}
}

 ?>

<!DOCTYPE html>

<html lang="en">

    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php	include ($IncludesPath."PreLoader.php"); ?>

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
					
						 <?php
	if($Status !=''){
	//Show Alert 
	?>
	 <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="mdi mdi-check-all mr-2"></i>
                                                      <?php echo $Status;
$_SESSION['Status']='';	  ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>




    <!-- Main content -->



          <div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">

                 
		 	<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Po Number</label>
                   <select class="form-control" name="InvID" id="InvID">
				  <option value="">Select PO</option>
				   <?php while ($Row_PO=$StmtPoData->fetch_array()){ 
				   
				   if(isset($_GET['ReportID'])){
					 ?>	
				 		<option <?php if($RowMB['InvID']==$Row_PO['PoID']){ ?> selected="selected" <?php }?> 
						value="<?php echo $Row_PO['PoID'] ?>">
					 <?php echo $Row_PO['PoNo'].' - '.$Row_PO['ConsigneeName']?></option>
				
					<?php }else {
						?>
                        <option value="<?php echo $Row_PO['PoID'] ?>">
					 <?php echo $Row_PO['PoNo'].' - '.$Row_PO['ConsigneeName']?></option>				
					
                      <?php 				
                       	} }   ?>
				  </select>
                </div>
			
		   
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"> Parameter</label>
                  <select class="form-control" name="Parameter" id="Parameter">
                     
				  <option value="">Select Parameter</option>		 
           <?php foreach($AryReferenceValues as $value){			
						$OPTIONS= $value;
						$VALUE= $AryReferenceValues[$count1]; 
						?>
			 
					<option value="<?php echo $value; ?>"><?php echo $value; ?></option>
					<?php  }?>
					<select> 
                </div> 
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Analysis Date</label>
                  <input type="date" class="form-control" name='AnalysisDate' id="AnalysisDate" 
				  max="<?php echo $today_date; ?>"placeholder="" value="<?php echo $today_date; ?>">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Result</label>
                  <input required type="text" class="form-control" name='Result' id="Result" 
				  value="" placeholder="Enter Result">
                </div> 
          
               <div class="col-xs-12 box-footer"  align="center">
                <button type="submit"  name='submit' class="btn btn-primary">ADD</button>
              </div>
   
             
            </form>					

 <div class="col-xs-12 box-footer"  align="RIGHT">
	 <a  id='eia_report' href="IHMBReportList.php" class="btn btn-primary">SUBMIT RESULT </a>
 </div>        

	
<div id="tdetails" class="table-responsive col-sm-12" >  
    <div class="box">
          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover" ALIGN="CENTER">
    <thead >
      <tr >
	  
	  <th align='center' >S.No</th>
	  <th align='center'>PO No</th>
	  <th align='center'>Buyer Name</th>
	  <th align='center'>Parameter</th>
	  <th align='center'>Analysis Date</th>
	  <th align='center'>Result</th>
	  <th align='center'>Remove</th>

      </tr>
    </thead>
    <tbody>

	<?php

 

$S_NO=1;
	//echo $sql;
   while($RowMBResult=$StmtMBResult->fetch_array()){

	?>
	<tr >
			
	<td  id="<?php  echo $RowMBResult['id'];?>" > <?php echo $S_NO; ?>    </td>
	<td id="<?php  echo $RowMBResult['id'];?>" > <?php echo $RowMBResult['PoNo']; ?> </td>
	<td id="<?php  echo $RowMBResult['id'];?>" > <?php echo $RowMBResult['ConsigneeName']; ?> </td>
	<td id="<?php  echo $RowMBResult['id'];?>" > <?php echo $RowMBResult['Parameter']; ?> </td>
	<td id="<?php  echo $RowMBResult['id'];?>" > <?php echo $RowMBResult['AnalysisDate']; ?> </td>
	<td id="<?php  echo $RowMBResult['id'];?>" > <?php echo $RowMBResult['Result']; ?> </td>
	
	<TD>
	<button id="<?php  echo $RowMBResult['id'];?>" 
	onClick="reply_click(this.id)" class="glyphicon-remove btn btn-danger"> Delete</button>
	</TD>
	
	
    </tr>
	
	
	  <?php 
	  $S_NO++;

   }
	  ?>

     </tbody>  

  </table>
</div>
</div>

</div>
			

 </div>


    </div>
    </div>
    </div>
       
    </body>

	<?php include ($IncludesPath."footer.php");?>
		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		
	
	<script>
$('document').ready(function(){

  $("#InvID").select2();
});
  function reply_click(clicked_id) {

   		 
		 alert(clicked_id);		  
		$.ajax({
		  url:'AjaxIHDelete.php',
		  type:'POST',
		  data:{'TYPE':'IH_MB_REPORT','D_ID':clicked_id},
		  success:function(data){
			  console.log(data);
			  
			 $("#CODE_LIST").html(data);
			 alert("Deleted");
			location.reload();
			}
			});					  
  } 
</script>
</html>				
