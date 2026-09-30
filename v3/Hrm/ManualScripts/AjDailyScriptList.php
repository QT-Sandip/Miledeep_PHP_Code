<?php
//ini_set( 'display_errors', 1 );

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");


$DeptID=   $_POST['DeptID'];
$SubDeptID=$_POST['SubDeptID'];
// $Year=     $_POST['Year'];
// $Month=     $_POST['Month'];
$Date=     $_POST['Date'];
$Month=date("m",strtotime($Date));
$Year=date("Y",strtotime($Date));



$ReportQuery="SELECT * FROM `HrmDailyScripts` where  Status=1  and ReportType=2 ORDER by SortOrder Asc";   
$ReportExec=$iconn->query($ReportQuery);
?>

<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		

			<thead class="thead-light">
<tr>
                  <th align="CENTER">Report Name</th>
                 <!-- <th align="CENTER">Month</th>
				  <th align="CENTER">Year</th>-->
				  <th align="CENTER">Date</th>
				  <th align="CENTER">Actions</th>
				  </tr>
                </thead>
                <tbody>

			   

<?php	
while($RowReport=$ReportExec->fetch_array()){
	//$QueryParms='DeptID='.$DeptID.'&SubDeptID='.$SubDeptID .'&Month='.$Month.'&Year='.$Year;
	$QueryParms='DeptID='.$DeptID.'&SubDeptID='.$SubDeptID .'&Month='.$Month.'&Year='.$Year.'&Date='.$Date;
	$QueryParms=urlEncode64($QueryParms);
	
	?>
			
				<tr>

                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $RowReport['ReportName']; ?></font></td>
               <!-- <td  align="left" width="10%"><font size="'.$text.'"><B><?php echo date("F", mktime(0, 0, 0, $Month, 10)); ; ?></B></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $Year; ?></font></td>	-->		
                <td  align="left" width="10%"><font size="'.$text.'"><?php echo date('d-m-Y',strtotime($Date)); ?></font></td>			
  
                <td  align="left" width="5%"><font size="'.$text.'">
				<!--<a  target="_blank" href="<?php //echo $RowReport['Path']. 
				//$RowReport['Url'].'?Month='.$Month.'&Year='.$Year .'&PsID='.$PsID ;?>"class="btn btn-success">Print</a>
				-->
				<a  target="_blank" href="<?php echo  
				$RowReport['Url'].'?Q='.$QueryParms;?>"class="btn btn-success">Run</a>
				</font></td>  
              
		</tr>


	<?php } ?>
	</tbody>
	</table>



	