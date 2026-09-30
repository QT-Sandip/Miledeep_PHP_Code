<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// echo "hii" ; exit;


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

//echo $cp;
// echo $_SESSION["SideBarPath"];
// exit;

$Type=$_POST['Type'];
$Month=$_POST['Month'];
$MonthName=$_POST['Month'];
$SpeciesType=$_POST['SpeciesType'];
if($Type==1){
$Month=sprintf("%02d", $Month);
$MonthName=date("F", mktime(0, 0, 0, $Month, 10));
}	
$Year=$_POST['Year'];

$PsID=$_POST['PsId'];
$AccessType=$_SESSION['AccessType'];
$DeptID=$_SESSION['DeptID'];

if($DeptID==8 || $DeptID==1){
 $ReportQuery="SELECT * FROM `RmAvalReports` where  Status=1 GROUP BY ReportName order by SortOrder Asc";  }
 else{
 $ReportQuery="SELECT * FROM `RmAvalReports` where DeptID='$DeptID' and Status=1 order by SortOrder Asc";  
	 
 }
 $ReportQuery="SELECT * FROM `RmAvalReports` where  Status=1 GROUP BY ReportName order by SortOrder Asc";  
//ECHO $ReportQuery;
$ReportExec=$iconn->query($ReportQuery);
?>

<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      
			
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
	$QueryParms='Month='.$Month.'&Year='.$Year .'&PsID='.$PsID.'&Type='.$Type.'&SType='.$SpeciesType;
	$QueryParms=urlEncode64($QueryParms);
	
	$MonthName = date("d-m-Y", strtotime($MonthName));
	$Year1 = date("d-m-Y", strtotime($Year));
	
	?>
			
				<tr>

                <td  align="left" width="10%"><font size="'.$text.'"><B><?php echo $RowReport['ReportName']; ?></B></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><B><?php echo $MonthName ; ?></B></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><B><?php echo $Year1; ?></B></font></td>			
  
                <td  align="left" width="5%"><font size="'.$text.'">
				<!-- <a  target="_blank" href="<?php echo $_SESSION["SideBarPath"]. 
				$RowReport['Url'].'?Q='.$QueryParms ;?>"class="btn btn-success">Print</a> -->
<!-- 								<a  target="_blank" href="<?php echo 
				$RowReport['Url'].'?Q='.$QueryParms ;?>"class="btn btn-success">Print</a> -->
				<a  target="_blank" href="<?php echo 
				'/v3/'.$RowReport['Url'].'?Q='.$QueryParms ;?>"class="btn btn-success">Print</a>
				</font></td>  
              
		</tr>


	<?php } ?>
	</tbody>
	</table>



	