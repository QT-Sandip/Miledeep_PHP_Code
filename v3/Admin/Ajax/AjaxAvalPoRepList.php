<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

  $OptionsType=$_POST['Type'];
 $DocType=$_POST['DocType'];
$InvID=$_POST['InvId'];

  $SqlInv="SELECT *,(SELECT COUNT(*) from $TblScrmPoGradeData WHERE InvID='$InvID') as  Breakuplist from 
  $TblScrmPIData  WHERE PoID='$InvID' ";  
$stmt_inv=$iconn->query($SqlInv);
$row_inv=$stmt_inv->fetch_array();
$ReportCat=$row_inv['TypeCountry'];

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
	
if($row_inv['Breakuplist']==0){
	$data.='
	
	<tr>
  		
  	 <td align=center width="100%">	No Reports Found </td>
  		
      </tr>';
}else{

	
      $SqlAvalRep="SELECT * FROM `$ExportAvalReports` where ReportType=$DocType 
and ReportCat='$ReportCat' and OptionType !=1 and ReportStatus=1 Order by ReportName asc";   
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
 
      $data.='<tr>
  		<td>'.$ReportName.$DisplayName.'</td>
  		
	  <td >
	  
		   
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




  echo  $data.= '</tbody>

  </table>';

}





?>
