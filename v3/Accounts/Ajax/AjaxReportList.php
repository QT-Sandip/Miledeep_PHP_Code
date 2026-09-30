<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");

$Month=$_POST['Month'];
//$Month=sprintf("%02d", $Month);
$MonthName=$_POST['Month'];
if($Type==1){
//$Month=sprintf("%02d", $Month);
$MonthName=date("F", mktime(0, 0, 0, $Month, 10));
}


$Year=$_POST['Year'];
$Type=$_POST['Type'];
$PsID=$_POST['PsId'];
$AccessType=$_SESSION['AccessType'];
$DeptID=$_SESSION['DeptID'];


 $ReportQuery="SELECT * FROM `CostingAvalReports` where DeptID='$DeptID' and Status=1 ORDER by SortOrder Asc";   
 $ReportQuery="SELECT * FROM `CostingAvalReports` where Status=1 ORDER by SortOrder Asc";   
$ReportExec=$iconn->query($ReportQuery);
?>

<table id="datatable" 
										class="table table-striped 
										table-bordered dt-responsive nowrap" >


			<thead>

                 <?php if($Type==1){ ?>

                  <th align="CENTER">Report Name</th>
                  <th align="CENTER">Month</th>
				  <th align="CENTER">Year</th>
				  <th align="CENTER">Actions</th>
				  
			<?php }else { ?>
			
				  <th align="CENTER">Report Name</th>
                  <th align="CENTER">Start Date</th>
				  <th align="CENTER">End Date</th>
				  <th align="CENTER">Actions</th>
				
			<?php } ?>
                </thead>
                <tbody>

			   

<?php	
while($RowReport=$ReportExec->fetch_array()){
	$QueryParms='Month='.$Month.'&Year='.$Year .'&PsID='.$PsID.'&Type='.$Type;
	$QueryParms=urlEncode64($QueryParms);
	//echo $RowReport['Path'].$RowReport['Url'];exit;
	
	?>
			
				<tr>

                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $RowReport['ReportName']; ?></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><B><?php echo $MonthName ; ?></B></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $Year; ?></font></td>			
  
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



	