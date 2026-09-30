<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$Type=$_POST['Type']; 
$DeptID=$_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];
$Month=$_POST['Month'];
$Year=$_POST['Year'];


if($Type=='Dept'){

	
}

else {
	
	if($SubDeptID!=''){
		$Query=' where  SubDeptID='.$SubDeptID;	
	}
	
	else if($DeptID!=''){
		$Query=' where DeptID='.$DeptID;
	}
	else{
	$Query='';	
	}
?>
	<table  id="scroll-vertical-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
											<tr>
                  <th>Bio Id </th>
				  <th>Name</th>
				  <th>Department</th>
				  <th>Mess Amount</th>
				  <th>Amount</th>
				  <th style="width: 20%">Tools</th>
				  </tr>
                </thead>
                <tbody>
				
<?php 


  $SqlMessDeductionList= "select * from HrmEmployeeDetails  $Query"; 

                    $StmtMessDeductionList = $iconn->query($SqlMessDeductionList);
					
					
                   
                    while($RowMessDeductionList = $StmtMessDeductionList->fetch_array()){
					
					$DeptID=$RowMessDeductionList['DeptID'];
					$Amount=$RowMessDeductionList['Amount'];
					$BioMetricID=$RowMessDeductionList['BioMetricID'];
					$EmpID=$RowMessDeductionList['EmpID'];
				
				$SqlMessDeduction="SELECT *  FROM `EmpMessDeductionDetails` WHERE Year='$Year' and Month='$Month' and BioID='$BioMetricID'";
				$StmtMessDeduction = $iconn->query($SqlMessDeduction);
                $RowMessDeduction = $StmtMessDeduction->fetch_array();
				$Amount=$RowMessDeduction['Amount'];


					if($Amount==''){
					$Amount=0;	
					}
					else{
						$Amount;
					}
					
				$SqlDept="SELECT * FROM HrmDepartments WHERE DeptID='$DeptID'";
					$StmtDept = $iconn->query($SqlDept);
					$RowDept = $StmtDept->fetch_array();
					$DepartmentName=$RowDept['DepartmentName'];
				 
					?>
						
						 <tr>
                          <td><?php echo $RowMessDeductionList['BioMetricID']; ?></td>
						  <td><?php echo $RowMessDeductionList['EmpName']; ?></td>
						  <td><?php echo $DepartmentName; ?></td>
						  <td><?php echo $Amount; ?></td>
						  
                          <td>
						  
						  <input  type='text' class='form-control'  name='Amount' id='Amount<?php echo $BioMetricID; ?>'
				  placeholder='' value='<?php echo $Amount; ?>'></td>
						
						
                          <td>
						<?php if($Amount=='') { ?>
						<!-- <a target="_blank" href="EmpAdvCreate?EmpID=<?php  echo $RowMessDeductionList['EmpID'];?>" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>-->
                         
						  <button  id='BtnAddMessDeduction'  value="<?php echo $BioMetricID; ?>" align='Center' class='btn btn-success btn-sm btn-flat'>
						 Add </button>

						<?php } else{ ?>
						<button  id='BtnAddMessDeduction'  value='<?php echo $BioMetricID; ?>' align='Center' class='btn btn-success btn-sm btn-flat'>
						 Edit </button>
						
						<?php }  ?>
                        
					
                      

			 						
				           </td>
				
				
				
				
				
				
                        </tr>
                      <?php
                    }
                  ?>
                </tbody>
              </table>
           <?php
}


?>