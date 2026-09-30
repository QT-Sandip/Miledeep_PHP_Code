<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PoId=$_POST['PoId'];
$SqlDocumentsList="SELECT * FROM `ScrmDocumentsList` 
where DocStatus=1 
 Order By SortOrder asc;";
$StmtDocumentsList=$iconn->query($SqlDocumentsList);
	
	
	 $data ='<thead>
		
		
		<tr>
		<form role="form" method="post">
		<th>Certificates</th>
		<th>Originals</th>
		<th>Copies</th>
		</tr>
		</thead>	
		<tbody>';
		$Counter=0;
	 while($RowDocumentsList=$StmtDocumentsList->fetch_array()){
		 
		 	$Counter++;
			$DocName=$RowDocumentsList['DocName'];	
			$DocId=$RowDocumentsList['DocId'];	
		 
		 $SqlDocSelectedList="SELECT DocID,COALESCE(Originals,'') as Originals ,
		 COALESCE(Copies,'') as Copies FROM `ScrmDocListData` 
		 where DocId='$DocId' and PoRefID='$PoId';";
		$StmtDocSelectedList=$iconn->query($SqlDocSelectedList);
		$RowSelectedList=$StmtDocSelectedList->fetch_array();
		$Originals=$RowSelectedList['Originals'];
		$Copies=$RowSelectedList['Copies'];
		 

			
				$data .='
				<tr>
		
	
		
		<td  >'.$DocName.'</td>
		<td hidden >  <input   type="text"  name="Original" value="'.$DocId.'"  id=DocId'.$Counter.'
				   placeholder="Enter Original" ></td>
		<td >  <input   type="text"  name="Original" value="'.$Originals.'"  id="Original'.$Counter.'"
				   placeholder="Enter Original" ></td>
		<td >   <input   type="text" name="Copies" value="'.$Copies.'"  id="Copies'.$Counter.'"
				   placeholder="Enter Copy" >
                </td>
		 </div> 
		
	 
			</tr>';
			
					  
		} 
		
		$data .='<td align="right">
		<div  class="form-group col-sm-12">

		<Button type="text" class="btn btn-success StatusBulkUpdate"
		name="StatusBulkUpdate" id="'.$PoId.'" value="'.$Counter.'">Submit</Button>
		
		                </div></td>  
						
						      
						   
';
					
		echo $data .='</tbody>';
	
	
?>