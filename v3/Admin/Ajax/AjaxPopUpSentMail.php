<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	if(isset($_POST['INT_LOT_NUM'])){
	 $InvId=$_POST['INT_LOT_NUM'];
	 $ReportIds=$_POST['SelectedReportIds'];
	 
	 $ReportName=array();
			
  $SqlReports="SELECT ReportName FROM `ScrmExportAvalReports` WHERE ID in($ReportIds)"; 
  $StmtReports=$iconn->query($SqlReports);
  //$IdsCount=mysqli_num_rows($StmtReports);
  while($RowReports=$StmtReports->fetch_array()){
	  
		$ReportName[]=$RowReports['ReportName'];  
	 
  }
$ReportNames = implode(',', $ReportName);
	 $data .='<table  class="table table-bordered ">
	 <thead>
		
		
		<tr>
		<td>
		
		
                  <label for="exampleInputPassword1">From </label>
                  <input    type="text" class="form-control" name="FromEmailAddress"
				  id="FromEmailAddress" placeholder="Enter mail Id" value=""
				 </input>
				</td>
				
				<td>
		
		
                  <label for="exampleInputPassword1">To </label>
                  <input    type="text" class="form-control" name="ToEmailAddress"
				  id="ToEmailAddress" placeholder="Enter mail Id(use , for multiple mail ids)" value=""
				 </input>
				</td>
				</tr>
				
				<tr>
				<td>
				
				 
				 
                  <label for="exampleInputPassword1">Cc </label>
                  <input    type="text" class="form-control" name="CcHeader"
				  id="CcHeader" placeholder="Enter Cc mail Id" value="">
				 </input>
				 </td>
				 
				
				
				 <td>
				 
				 <label for="exampleInputPassword1">Subject</label>

				 <input    type="text" class="form-control" name="Subject"
				  id="Subject" placeholder="Enter Subject" value=""
				 </input>
				</td>
				</tr>
				
				<tr>
				<td>
				 <label for="exampleInputPassword1">Documents</label>

				 <input  readonly  type="text" class="form-control" name="DocumentsList"
				  id="DocumentsList" placeholder="Enter Subject" value="'.$ReportNames.'"
				 </input>
				 
				 </td>
              
						

				<td>
                <!-- <label for="exampleInputPassword1">Message</label>

				 <input type="textarea"  rows="4"  type="text" class="form-control" name="Message" style="white-space:pre"
				  id="Message" placeholder="Enter Message" value=""
				 </input>-->
				 
				 <textarea name="Message" id="Message" type="text"  rows="4" cols="40" placeholder="Enter Message" style="white-space:pre"></textarea>
				 
				 </td>
				 

</tr>
<tr>
				
      <td align="right">
		<Button type="text"  class="btn btn-success tomail" name="tomail" id="'.$InvId.'">Send</Button>
		</td>						
		</tr>
		</thead>
		
		<tbody>
		
		';
	

 		echo $data .='</tbody></table>';

		

		
		
	//	echo json_encode($OUT);
	}
	
?>