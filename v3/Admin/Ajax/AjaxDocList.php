<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

  $OptionsType=$_POST['Type'];
 $DocType=$_POST['DocType'];
$InvID=$_POST['InvId'];
  $AccessType=$_SESSION["ACCESS_TYPE"]; 


$SqlPiStatus="SELECT Status from  $TblScrmPIData  WHERE PoID='$InvID' ";  
$StmtPiStatus=$iconn->query($SqlPiStatus);
$RowPiStatus=$StmtPiStatus->fetch_array(); 

$Status=$RowPiStatus['Status'];

$SqlInv="SELECT *,(SELECT COUNT(*) from $TblScrmPoGradeData WHERE InvID='$InvID') as  Breakuplist from 
  $TblScrmPIData  WHERE PoID='$InvID' ";  
$stmt_inv=$iconn->query($SqlInv);
$row_inv=$stmt_inv->fetch_array();
$ReportCat=$row_inv['TypeCountry'];
$ReportCat=1;

if($OptionsType=='DocList'){

  $data='<!--<table id="example1" class="table table-bordered table-hover">-->
<table id="scroll-vertical-datatable" 
										class="table table-striped 
										table-bordered dt-responsive nowrap">
		
    <thead>
	<tr>
		<th>Available Reports</th>
		<th>Actions</th>
	</tr>
    </thead>

    <tbody>';
		 if( $Status==0){

     if($row_inv['Breakuplist']==0){
	$data.='
	<tr>
	<tr>
  		<td> BreakUp</td>
  		<td>
  	 <a target="" href="PoBreakUp.php?id='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
      
  		</td>
	 </tr>
	 
	 <tr>
  		<td>Po Shipment Details</td>
  		<td>
  	 <a target="" href="PoShipmentDetails.php?PIID='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
      
  		</td>
	 </tr>';}
	 // else if($row_inv['Breakuplist']!=0){
	  else {
		  $data.='
		  	<tr>

	<tr>
  		<td> BreakUp</td>
  		<td>
  	 <a target="" href="PoBreakUp.php?id='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
      
  		</td>
      </tr>
	  <tr>
  		<td>Po Shipment Details</td>
  		<td>
  	 <a target="" href="PoShipmentDetails.php?PIID='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
      
  		</td>
	 </tr>
	  ';
	  
	   $SqlAvalRep="SELECT * FROM `$ScrmExportAvalReports` where ReportType=$DocType 
and ReportCat='$ReportCat' and OptionType !=1 and ReportStatus=1 Order by SortOrder asc";   
$StmtAval=$iconn->query($SqlAvalRep);  

   while($RowAvalRep=$StmtAval->fetch_array()){
     $TableName=$RowAvalRep['TableName'];
     $QueryID=$RowAvalRep['QueryID'];
     $PoID=$RowAvalRep['PoID'];
     $ReportName=$RowAvalRep['ReportName'];
     $DisplayId=$RowAvalRep['DisplayParam'];
    
	 $SqlReport="select * from $TableName where $PoID='$InvID'"; 
     $StmtReports=$iconn->query($SqlReport); 
    while($RowRep=$StmtReports->fetch_array()){
		
		$QueryParamVal=$RowRep["$QueryID"];
		if($DisplayId!=''){
		 $DisplayName=$RowRep["$DisplayId"];
		}else{
		$DisplayName='';	
		}
	
		$RowAval['Count']=1;
		
		if($DisplayName!=''){
			$DisplayName=' ('.$DisplayName.')';
		}
	 
	 
	 
		 if($RowAval['Count'] > 0 ){
      $data.='
	  	  
	  <tr>
  		<td>'.$ReportName.$DisplayName.'</td>
  		
	  <td >
	  
		 <!-- <a target="" href="'.$RowAvalRep['EditLink'].'PIID='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>-->
          <a target="" href="PIEdit.php?id='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
           
		   <a target="_blank" href="Reports/'.$RowAvalRep['PrintLink'].'id='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Print"><i class="mdi mdi-printer font-size-18"></i></a>
           
	  
	  
  	 	
  	  </td>
         </tr>
  	   <tr>';
	 }	
	 }
	}
    


		 }	}  else if($AccessType==1&&$Status!=0){
		  $data.='
		  	<tr>

	<tr>
  		<td> BreakUp</td>
  		<td>
  	 <a target="" href="PoBreakUp.php?id='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
      
  		</td>
      </tr>
	  
	  <tr>
  		<td>Po Shipment Details</td>
  		<td>
  	 <a target="" href="PoShipmentDetails.php?PIID='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
      
  		</td>
	 </tr>
	  
	  ';
	  
	   $SqlAvalRep="SELECT * FROM `$ScrmExportAvalReports` where ReportType=$DocType 
and ReportCat='$ReportCat' and OptionType !=1 and ReportStatus=1 Order by SortOrder asc";   
$StmtAval=$iconn->query($SqlAvalRep);  

   while($RowAvalRep=$StmtAval->fetch_array()){
     $TableName=$RowAvalRep['TableName'];
     $QueryID=$RowAvalRep['QueryID'];
     $PoID=$RowAvalRep['PoID'];
     $ReportName=$RowAvalRep['ReportName'];
     $DisplayId=$RowAvalRep['DisplayParam'];
    
	 $SqlReport="select * from $TableName where $PoID='$InvID'"; 
     $StmtReports=$iconn->query($SqlReport); 
    while($RowRep=$StmtReports->fetch_array()){
		
		$QueryParamVal=$RowRep["$QueryID"];
		if($DisplayId!=''){
		 $DisplayName=$RowRep["$DisplayId"];
		}else{
		$DisplayName='';	
		}
	
		$RowAval['Count']=1;
		
		if($DisplayName!=''){
			$DisplayName=' ('.$DisplayName.')';
		}
	 
	 
	 
		 if($RowAval['Count'] > 0 ){
      $data.='
	  	  
	  <tr>
  		<td>'.$ReportName.$DisplayName.'</td>
  		
	  <td >
	  
		  <a target="" href="'.$RowAvalRep['EditLink'].'PIID='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
           
		   <a target="_blank" href="Reports/'.$RowAvalRep['PrintLink'].'id='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Print"><i class="mdi mdi-printer font-size-18"></i></a>
           
	  
	  
  	 	
  	  </td>
         </tr>
  	   <tr>';
	 }	
	 }
	}
    


	  }
		 
		 
		 
		 
else{

	
      $SqlAvalRep="SELECT * FROM `$ScrmExportAvalReports` where ReportType=$DocType 
and ReportCat='$ReportCat' and OptionType !=1 and ReportStatus=1 Order by SortOrder asc";   
$StmtAval=$iconn->query($SqlAvalRep);  

   while($RowAvalRep=$StmtAval->fetch_array()){
     $TableName=$RowAvalRep['TableName'];
     $QueryID=$RowAvalRep['QueryID'];
     $PoID=$RowAvalRep['PoID'];
     $ReportName=$RowAvalRep['ReportName'];
     $DisplayId=$RowAvalRep['DisplayParam'];
    
	 $SqlReport="select * from $TableName where $PoID='$InvID' LIMIT 1"; 
     $StmtReports=$iconn->query($SqlReport); 
    while($RowRep=$StmtReports->fetch_array()){
		
		$QueryParamVal=$RowRep["$QueryID"];
		if($DisplayId!=''){
		 $DisplayName=$RowRep["$DisplayId"];
		}else{
		$DisplayName='';	
		}
	
		$RowAval['Count']=1;
		
		if($DisplayName!=''){
			$DisplayName=' ('.$DisplayName.')';
		}
	 
	 
	 
		 if($RowAval['Count'] > 0 ){
      $data.='
	  	  
	  <tr>
  		<td>'.$ReportName.$DisplayName.'</td>
  		
	  <td >
	  
		  <a target="" href="'.$RowAvalRep['EditLink'].'PIID='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
           
		   <a target="_blank" href="Reports/'.$RowAvalRep['PrintLink'].$QueryID.'='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Print"><i class="mdi mdi-printer font-size-18"></i></a>
           
	  
	  
  	 	
  	  </td>
         </tr>
  	   <tr>';
	 }	
	 }
	}
    


     }


		 

/**uma**/

/**uma**/







  echo  $data.= '</tbody>

  </table>';

}


else if($OptionsType=='DocCreate'){
	
	
$SqlInv="SELECT *,(SELECT COUNT(*) from $TblScrmPoGradeData WHERE InvID='$InvID') as  Breakuplist from 
  $TblScrmPIData  WHERE PoID='$InvID'";
 
$stmt_inv=$iconn->query($SqlInv);
$row_inv=$stmt_inv->fetch_array();

   $SqlAvalRep="SELECT * FROM `$ScrmExportAvalReports` 
where ReportType=$DocType  and ReportCat='$ReportCat' and OptionType !=2 
and ReportStatus=1 Order by SortOrder asc";
$StmtAval=$iconn->query($SqlAvalRep);


IF($Status ==0 || $Status ==1 || $Status ==2 || $Status ==4 ){
	
	
	$data='<table id="example1" class="table table-bordered table-hover">
	<thead>
	<tr> 
	<th ><font color="red">
	<B> Note: P.O not received yet</B></font><th>
	</tr>
	</thead>';
	
	
	
}else{

  $data='<table id="example1" class="table table-bordered table-hover">

    
	<tr>
		<th>Reports Name</th>
		<th>Actions</th>
	</tr>
    </thead>

    <tbody>';

   while($RowAvalRep=$StmtAval->fetch_array()){
     $TableName=$RowAvalRep['TableName'];
    // $ColumnName=$RowAvalRep['ReportID'];
	// $QueryID=$RowAvalRep['QueryID'];
     $ReportName=$RowAvalRep['ReportName'];
    $data.='<tr>
  		<td id="p_number'. $row['C_ID'].'">'.$ReportName.'</td>
  		
		
		<td >
  	    
	   <a target="" href="'.$RowAvalRep['AddLink']. 'PIID='.$InvID.'" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
          
  		</td>
         </tr>
  	   <tr>';

     }



}
  echo  $data.= '</tbody>

  </table>';


}



?>
