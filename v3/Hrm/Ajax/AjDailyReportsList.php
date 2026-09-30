<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");


$DeptID=   $_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];
$Date=     $_POST['Date'];

$year = date('Y', strtotime($Date));

$month = date('F', strtotime($Date));


 $ReportQuery="SELECT * FROM `AdminAvalReports` where ReportType=5 and  Status=1 ORDER by SortOrder Asc";   
$ReportExec=$iconn->query($ReportQuery);
?>

  <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">

                  <th align="CENTER">Report Name</th>
                  <th align="CENTER">Month</th>
				  <th align="CENTER">Year</th>
				  <th align="CENTER">Actions</th>
                </thead>
                <tbody>

			   

<?php	
while($RowReport=$ReportExec->fetch_array()){
	$QueryParms='DeptID='.$DeptID.'&SubDeptID='.$SubDeptID .'&Date='.$Date;
	$QueryParms=urlEncode64($QueryParms);
	
	?>
			
				<tr>

                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $RowReport['ReportName']; ?></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><B><?php echo $month; ; ?></B></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $year; ?></font></td>			
  
                <td  align="left" width="5%"><font size="'.$text.'">
				<!--<a  target="_blank" href="<?php //echo $RowReport['Path']. 
				//$RowReport['Url'].'?Month='.$Month.'&Year='.$Year .'&PsID='.$PsID ;?>"class="btn btn-success">Print</a>
				-->
				<a  target="_blank" href="<?php echo $RowReport['Path']. 
				$RowReport['Url'].'?Q='.$QueryParms;?>"class="btn btn-success">Print</a>
				</font></td>  
              
		</tr>


	<?php } ?>
	</tbody>
	</table>



	