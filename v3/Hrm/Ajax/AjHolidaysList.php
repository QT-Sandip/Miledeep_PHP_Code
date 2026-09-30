<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $Year=$_POST['Year']; 


?>
	
	
	
           <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
											<tr>
                  <th>ID </th>
				  <th>Name</th>
				  <th>Date</th>
				  <th style="width: 20%">Tools</th>
				  </tr>
                </thead>
                <tbody>
				
<?php 

	
 $SqlHolidayList = "SELECT * FROM `HrmHolidays` WHERE Year(Date)='$Year'";
                  $StmtHolidayList = $iconn->query($SqlHolidayList);
                   
                    while($RowHoliday = $StmtHolidayList->fetch_array()){ ?>
						
						 <tr>
                          <td><?php echo $RowHoliday['ID']; ?></td>
						  <td><?php echo $RowHoliday['Name']; ?></td>
						  <td><?php echo $RowHoliday['Date']; ?></td>
						
						
                          <td>
						
					 
           <a target="_blank" href="CreateHolidays.php?ID=<?php  echo $RowHoliday['ID'];?>" 
		 class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
		title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
                        
	<button class="btn btn-link text-danger Delete" data-id="<?php echo $RowHoliday['ID']; ?>" 		
data-toggle="tooltip" data-placement="top" title="" value=""
data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> 						
								 
	
				</td>
				
				
				
				
				
				
                        </tr>
                      <?php
                    }
                  ?>
                </tbody>
              </table>
           <?php



?>