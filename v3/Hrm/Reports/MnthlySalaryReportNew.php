<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

//include "../includes/config.php";
$PageTitle="Purchase Code";
$PageTitleFolder="Admin";
$PageTitleLink="home";


extract($_POST);
extract($_GET);

$ClientID=$_SESSION["ClientID"];
 $QueryParms=$_GET['Q']; 

if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }

  $DeptID=$_GET['DeptID']; 
  $SubDeptID=$_GET['SubDeptID'];  
  $Month=$_GET['Month'];  
  $Year=$_GET['Year'];  
  $TotalDays = $_GET['Month'];
  $EmpCategory = (isset($_GET['EmpCategory']) & $_GET['EmpCategory']!='') ? $_GET['EmpCategory'] :  'Staff';
  $EmpSubCategory = $_GET['EmpSubCategory'];
  $MonthSegment=$_GET['MonthSegment'];
  $MonthSegmentData=getMonthSegments($Month,$Year,$MonthSegment);
  $MonthSegmentStart=$MonthSegmentData['StartDate'];
  $MonthSegmentEnd=$MonthSegmentData['EndDate'];
  $MonthName = date('F', mktime(0, 0, 0, $Month, 10)); 


  $sql = "
    SELECT
        id,
        frozen_by,
        frozen_at
    FROM salary_sheet_freeze
    WHERE salary_month = ?
      AND salary_year = ?

    LIMIT 1
";

$stmt = $iconn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $iconn->error);
}

$stmt->bind_param(
    "ii",
    $Month,
    $Year
);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();
$freeze = $result->fetch_assoc();
$freeze_btn="";
if ($freeze) {

    $freeze_btn= '<span class="label label-success">
            Already Frozen
          </span>';

} else {

    $freeze_btn =  '<button class="freeze-btn">❄ Freeze Salary</button>';
}

$stmt->close();




  $firstdateofmonth = date($Year."-".$Month."-"."01");
    $qury = "SELECT
      ED.EmpName,
      HD.DepartmentName,
      ED.BioMetricID,
      ED.EmpID,
      COALESCE(HCA.Remarks, '') AS Remarks
  FROM HrmEmployeeDetails AS ED
  LEFT JOIN HrmDepartments AS HD
      ON HD.DeptID = ED.DeptID
  LEFT JOIN HrmCashAdvance AS HCA
      ON HCA.EmpID = ED.EmpID
      AND HCA.AdvDate = '$firstdateofmonth'
where HCA.Remarks !=''
GROUP BY
    ED.EmpID,
    ED.EmpName,
    HD.DepartmentName,
    ED.BioMetricID,
    HCA.Remarks
ORDER BY
    ED.BioMetricID ASC ";
//echo $qury;
$SqlquryResult = $iconn->query($qury);
$remakslist = [];
 while($Rowqury = $SqlquryResult->fetch_array()){
  $remakslist[$Rowqury['EmpID']] = $Rowqury['Remarks'];
 }

 $SqlWorkerCategory = "SELECT * FROM `EmpWorkerSubCategories` WHERE CategoryID='$EmpSubCategory'";
 $SqlWorkerCategoryResult = $iconn->query($SqlWorkerCategory);
 $RowWorkerType = $SqlWorkerCategoryResult->fetch_array();

  if( $EmpSubCategory == 2 ){
    $date1= new DateTime($MonthSegmentStart);
    $date2= new DateTime($MonthSegmentEnd);
    $DateText = ($MonthSegment == 1 ? 'First Half ' : 'Second Half ') . "({$date1->format('Y M d')} - {$date2->format('d')})";
    $content1 .= '<div class="page-heading"><h3 align="center" class="pt-3">'.(($RowWorkerType['CategoryName']!='') ? $RowWorkerType['CategoryName'] : $EmpCategory ).' Salary  Report </h3><h2>'.$DateText.'</h2></div>';
  }else{
    $content1 .= '<h3 align="center" class="pt-3">'.(($RowWorkerType['CategoryName']!='') ? $RowWorkerType['CategoryName'] : $EmpCategory ).' Salary  Report -'.$MonthName.'('.$Year.')</h3>';
  }

  echo $content1;
?>
<style>
  .freeze-btn {
    padding: 10px 16px;
    border: 3px solid #8ab4c7;
    border-radius: 6px;
    background: linear-gradient(#e8f7fc, #c5e8f3);
    color: #17465a;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,.15);
  }

  .freeze-btn:hover {
    background: linear-gradient(#dff5fb, #addce9);
    box-shadow: 0 2px 6px rgba(0,0,0,.2);
  }

  .freeze-btn:active {
    transform: translateY(1px);
  }
  .download_btn {
    padding: 5px 10px;
    font-size: 16px;
    background-color: white;
    color: #217346;
    border: 2px solid #217346;
    border-radius: 5px;
    cursor: pointer;
    display: inline !important;
    align-items: center;
    margin-bottom: 20px;
}
</style>
<?php
  // $no_of_days = cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
  // if($no_of_days == 31){
  //   $TotalDays = 28;
  // }else{
  //   $TotalDays = 28;
  // }

$file_name='Salary Report.pdf';


if($SubDeptID!='' ){

$query=" AND HED.SubDeptID='$SubDeptID' ";
}elseIF($DeptID!=''){
  $query="AND HED.DeptID='$DeptID' ";
}








 //echo $SqlOnTimeEmpDetails;
 //exit;





function getP_TABLE($iconn,$SqlOnTimeEmpDetails){
global  $Month,$Year;//uma
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

        $StmtOnTimeEmpDetails=$iconn->query($SqlOnTimeEmpDetails);
        $RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array();

        $StmtOnTimeEmpDetails=$iconn->query($SqlOnTimeEmpDetails);



  $content='';


  $size=40;

  $size1=60;




        $S_NO=1;
        $width=120/14;
        $width_s=$width-5;
        $width_w=$width+5;


        $content .= '
    <table width=100% class="blue" border="1" cellspacing="0" cellpadding="3">

         <tr>
                <th  align="center"><B>S No.</B></th>
        <th  align="center"><B> Bio ID </B></th>
                <th  align="center"><B>Emp Name</B></th>
                <th  align="center"><B>Deptartment </B></th>
                <th  align="center"><B>SubDeptartment </B></th>
            <th  align="center"><B>Designation</B></th>
                <th  align="center"><B>Basic</B></th>
                <th  align="center"><B>Single Duty</B></th>
                <th  align="center"><B>Double Duty </B></th>
                <th  align="center"><B>On Duty </B></th>
                <th  align="center"><B>Week off</B></th>
                <th  align="center"><B>Half Day</B></th>
                <th  align="center"><B>Absent</B></th>
                <th  align="center"><B>Leave</B></th>
                <th  align="center"><B>Holiday</B></th>
                <th  align="center"><B>ML</B></th>
                <th  align="center"><B>TOTAL</B></th>
                <th  align="center"><B>PAYMENT</B></th>
                <th  align="center"><B>RESERVE</B></th>
                <th  align="center"><B>Short Days</B></th>
                <th  align="center"><B>GROSS SAL</B></th>
                <th  align="center"><B>Mess</B></th>
                <th  align="center"><B>PF & ESI</B></th>
                <th  align="center"><B>Wage Deduction</B></th>
                <th  align="center"> TDS<B></B></th>
                <th  align="center"> PT<B></B></th>
                <th  align="center"> Advance<B></B></th>
                <th  align="center"> Salary Arrars<B></B></th>
                <th  align="center"> <B>Travel</B></th>
                <th  align="center"> <B>Incentive</B></th>
                <th  align="center"> <B>Penality</B></th>
                <th  align="center"> <B>W.Days Salary</B></th>
                <th  align="center"> <B>Account No</B></th>
            <th  align="center"> <B>IFSC</B></th>
       </tr>

        ';


    while($RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array()){

$EmpID=$RowEmpCompltDtils['EmpID'];
$EmpName=$RowEmpCompltDtils['EmpName'];
$BioMetricID=$RowEmpCompltDtils['BioMetricID'];
$DeptName=$RowEmpCompltDtils['DepartmentName'];
$SubDeptName=$RowEmpCompltDtils['SubDeptName'];
$Designation=$RowEmpCompltDtils['Designation'];
$leavecount=$RowEmpCompltDtils['leavecount'];
$StartDate=$RowEmpCompltDtils['StartDate'];
$EndDate=$RowEmpCompltDtils['EndDate'];

$TotalPresents=$RowEmpCompltDtils['TotalPresents'];
$SingleDuty=$RowEmpCompltDtils['SingleDuty'];
$DoubleDuty=$RowEmpCompltDtils['DoubleDuty'];
$OnDuty=$RowEmpCompltDtils['DaysTotal'];
$HalfDay=$RowEmpCompltDtils['HalfDay'];
$Absent=$RowEmpCompltDtils['Absent'];
$WeekOff=$RowEmpCompltDtils['WeekOff'];
$Holidays=$RowEmpCompltDtils['Holidays'];
$Leaves=$RowEmpCompltDtils['Leaves'];
$ML=$RowEmpCompltDtils['ML'];
$WageDays=$RowEmpCompltDtils['WageDays'];
$Payement=$RowEmpCompltDtils['EarnedSalaryWithoutDud'];
$PF=$RowEmpCompltDtils['PF'];
$ESI=$RowEmpCompltDtils['ESI'];
$Advance=$RowEmpCompltDtils['Adavance'];
$NoOfShortDays=$RowEmpCompltDtils['NoOfShortDays'];
$BankIFSC=$RowEmpCompltDtils['IFSC'];
$AccNo=$RowEmpCompltDtils['AccNumber'];
$MessInDays=$RowEmpCompltDtils['MessInDays'];

$GrossSalary=$Payement;
$NetSalary=round($GrossSalary-(($GrossSalary*$NoOfShortDays)/30));


$NetSalary=$NetSalary-$PF-$ESI-$Advance-$MessInDays;

$SqlOnDuty="SELECT sum(DaysTotal) as onduty from HrmOnDutyRegister where EmpID='$EmpID' and
 Month(StartDate)='$Month' and Year(StartDate)='$Year' ";
$StmtOnDuty=$iconn->query($SqlOnDuty);
$RowOnDuty = $StmtOnDuty->fetch_array();

 $OnDuty=$RowOnDuty['onduty'];
$content .=

          '<tr>
    <td  align="center">'.$S_NO.'</td>
    <td  align="center">'.$BioMetricID.'</td>
    <td  align="left">'.$EmpName.'</td>
    <td  align="left">'.$DeptName.'</td>
    <td  align="left">'.$SubDeptName.'</td>
    <td  align="left">'.$Designation.'</td>
    <td  align="left">'.$Basic.'</td>
    <td  align="center">'.$SingleDuty.'</td>
    <td  align="center">'.$DoubleDuty.'</td>
    <td  align="center">'.$OnDuty.'</td>
    <td  align="center">'.$WeekOff.'</td>
    <td  align="center">'.$HalfDay.'</td>
    <td  align="center">'.$Absent.'</td>
    <td  align="center">'.$Leaves.'</td>
    <td  align="center">'.$Holidays.'</td>
    <td  align="center">'.$ML.'</td>
    <td  align="center">'.$WageDays.'</td>
    <td  align="center">'.$Payement.'</td>
    <td  align="center">'.$Reserve.'</td>
    <td  align="center">'.$NoOfShortDays.'</td>
    <td  align="center">'.$GrossSalary.'</td>
    <td  align="center">'.$MessInDays.'</td>
    <td  align="center">'.$PF.'</td>
    <td  align="center">'.$ESI.'</td>
    <td  align="center">'.$TDS.'</td>
    <td  align="center">'.$PT.'</td>
    <td  align="center">'.$Advance.'</td>
    <td  align="center">'.$SalaryArars.'</td>
    <td  align="center">0</td>
    <td  align="center">0</td>
    <td  align="center">0</td>
    <td  align="center">'.$NetSalary.'</td>
    <td  align="left">'.$AccNo.'</td>
    <td  align="left">'.$BankIFSC.'</td>

         </tr>';

 $S_NO++;
}


    $content .= '</table>';

    $Data['TABLE']=$content;

  return $Data;

}


function getStaff_TABLE(){
global  $iconn,$Month,$Year,$TotalDays,$remakslist,$freeze_btn;//uma
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

          $SqlOnTimeEmpDetails="
          SELECT
            *,
            AdminBankDetails.BankName,
            HMBD.AccNumber,
            HMBD.IFSC,
            HED.BioMetricID as BioMetricID,
            (SELECT COALESCE(NoOfShortDays,0)  FROM HrmMonthlyShortDaysList as HMSL WHERE HMSL.EmpID=HED.EmpID and HMSL.Month='$Month' and HMSL.Year='$Year' ) as NoOfShortDays
            from HrmEmployeeDetails HED
        LEFT JOIN HrmDepartments HD  ON HD.DeptID=HED.DeptID
        LEFT JOIN HrmSubDepartments HSD  ON HSD.SubDeptID=HED.SubDeptID
        LEFT JOIN HrmDesignations HDG  ON HDG.DesigID=HED.DesigID
        LEFT JOIN HrmEmpPersonalDetails HPD ON HPD.EmpID=HED.EmpID
        LEFT JOIN HrmEmpBankAccountDetails HMBD
        ON HMBD.BankAccID = (
            SELECT MAX(HMBD2.BankAccID)
            FROM HrmEmpBankAccountDetails HMBD2
            WHERE HMBD2.EmpID = HED.EmpID
        )
        LEFT JOIN HrmBanksMaster as AdminBankDetails ON AdminBankDetails.ID=HMBD.BankID
        LEFT JOIN HrmMonthlySalaryDetails HMSD ON HMSD.BioMetricID=HED.BioMetricID
        AND HMSD.Month='$Month' AND HMSD.Year='$Year' AND HMSD.ReportType=1

        WHERE  /*EmpStatus=1 AND*/ EmpCategory='Staff' and HMSD.Others >0
          $query order by CASE WHEN COALESCE(HMSD.Others, 0) < 10 THEN 1 ELSE 0 END, HED.BioMetricID asc";
        $StmtOnTimeEmpDetails=$iconn->query($SqlOnTimeEmpDetails);



  $content='';


  $size=40;

  $size1=60;




        $S_NO=1;
        $width=120/14;
        $width_s=$width-5;
        $width_w=$width+5;

        $content .= '<div class="download-container">
                      <button class="download_btn" data-report-type="1">
                        <img src="../../assets/images/icons8-excel.svg" alt="" width="30">Download
                      </button>
                      '.$freeze_btn.'
                    </div>';
        $content .= '<table border="1" style="border-collapse: collapse; width: 100%;">';
        $content .= '<thead>
  <tr>
    <th rowspan="2">S.No</th>
    <th rowspan="2">Bio.Code No</th>
    <th rowspan="2">Name of the Employe</th>
    <th rowspan="2">Date of Joining</th>
    <th rowspan="2">Designation</th>
    <th rowspan="2">Total Salary</th>
    <th rowspan="2">Per Day</th>
    <th colspan="4">Working days</th>
    <th rowspan="2">Arrear Amount</th>
    <th rowspan="2">Incentive</th>
    <th rowspan="2">Gross Salary</th>
    <th colspan="7">Deductions</th>
    <th rowspan="2">Net Salary</th>
    <th colspan="3">Bank Details</th>
    <th rowspan="2">Remarks</th>
  </tr>
  <tr>
    <th>No of Working Days</th>
    <th>Add. Offs</th>
    <th>Consider Days</th>
    <th>Total Days</th>

    <!-- Deductions -->
    <th>PF & ESI</th>
    <th>Advance</th>
    <th>Wage Deduction</th>
    <th>Late Coming Fine</th>
    <th>Helmet Deduction</th>
    <th>Mess</th>
    <th>Total Deductions</th>

    <!-- Bank Details -->
    <th>Bank Name</th>
    <th>Account Number</th>
    <th>IFSC Code</th>

  </tr>
</thead>';
    $content .='<tbody>';
    $TotalSalarySum = 0;
    $TotalWorkingDays = 0;
    $TotalWeekOffs=0;
    $TotalDaysSum=0;
    $ToalGrossSalarySum = 0;
    $TotalPFSum=0;
    $TotalESISum=0;
    $TotalAdvSum=0;
    $TotalLateFineSum=0;
    $TotalHelmetFineSum=0;
    $TotalMessChargesSum=0;
    $TotalDeductionsSum=0;
    $TotalNetSalarySum=0;
    $TotalArrearAmountSum=0;
    $TotalShipmentChargesSum=0;

    $monthPadded = str_pad($Month, 2, '0', STR_PAD_LEFT);
    $current = (int)($Year . $monthPadded);
    $threshold = 202507;
    $isHolidayAdded =false;
    if ($current > $threshold) {
        $isHolidayAdded =true;
    }

    while($RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array()){
      $EmpID=$RowEmpCompltDtils['EmpID'];
      $Remarks = $remakslist[$EmpID];
      $EmpName=$RowEmpCompltDtils['EmpName'];

      $BankName = $RowEmpCompltDtils['BankName'];
      $AccNumber = $RowEmpCompltDtils['AccNumber'];
      $IFSC = $RowEmpCompltDtils['IFSC'];

      $DOJ= ($RowEmpCompltDtils['JoiningDate'] != '') ? $RowEmpCompltDtils['JoiningDate'] : 'N/A';
      $BioMetricID=$RowEmpCompltDtils['BioMetricID'];
      $DeptName=$RowEmpCompltDtils['DepartmentName'];
      $SubDeptName=$RowEmpCompltDtils['SubDeptName'];
      $Designation= ($RowEmpCompltDtils['Designation']!='') ? $RowEmpCompltDtils['Designation'] : 'N/A';
      $leavecount=$RowEmpCompltDtils['leavecount'];
      $StartDate=$RowEmpCompltDtils['StartDate'];
      $EndDate=$RowEmpCompltDtils['EndDate'];

      $TotalPresents=$RowEmpCompltDtils['TotalPresents'];
      $SingleDuty=$RowEmpCompltDtils['SingleDuty'];
      $DoubleDuty=$RowEmpCompltDtils['DoubleDuty'];
      $OnDuty=$RowEmpCompltDtils['DaysTotal'];
      $HalfDay=$RowEmpCompltDtils['HalfDay'];
      $Absent=$RowEmpCompltDtils['Absent'];
      $WeekOff=$RowEmpCompltDtils['WeekOff'];
      $Holidays=$RowEmpCompltDtils['Holidays'];
      $Leaves=$RowEmpCompltDtils['Leaves'];
      if($isHolidayAdded){
          $Leaves=$RowEmpCompltDtils['Leaves'] + $Holidays;
      }
      $ML=$RowEmpCompltDtils['ML'];
      $WageDays=$RowEmpCompltDtils['WageDays'];
      $others=$RowEmpCompltDtils['Others'];
      $Payement=$RowEmpCompltDtils['EarnedSalaryWithoutDud'];
      $PF=$RowEmpCompltDtils['PF'];
      $ESI=$RowEmpCompltDtils['ESI'];
      $Advance=$RowEmpCompltDtils['Adavance'];
      $NoOfShortDays=$RowEmpCompltDtils['NoOfShortDays'];
      $BankIFSC=$RowEmpCompltDtils['IFSC'];
      $AccNo=$RowEmpCompltDtils['AccNumber'];
      $MessInDays=$RowEmpCompltDtils['MessInDays'];
      $LateLoginFine=$RowEmpCompltDtils['LateLoginFine'];
      $HelmetFine=$RowEmpCompltDtils['HelmetFine'];
      $TotalDays = $RowEmpCompltDtils['Total'];

      $ArrearAmount=$RowEmpCompltDtils['ArrearAmount'];
      $ShipmentCharges=$RowEmpCompltDtils['ShipmentCharges'];

      // $ReqWorkingDays=$TotalDays-$WeekOff;
      $PerDaySal = $Payement/$TotalDays;


      $TotalSalary=$Payement;
      //$DaysWithoutWeekOffs=$SingleDuty+($DoubleDuty * 2)+($HalfDay/2)+$Holidays;
      $DaysWithoutWeekOffs=$SingleDuty+($HalfDay/2)+$Leaves;
      if( $Year == 2026 && ($Month == "02" || $Month == 2)){
        if( ($DaysWithoutWeekOffs - $Leaves) >= 24 ){
            $WeekOff = 4;
        }
      }
      $EarnedPayDays=$DaysWithoutWeekOffs+$WeekOff;
      // $NetSalary=round($GrossSalary-(($GrossSalary*$NoOfShortDays)/30));
      // $TotalDeductions=$ESI+$MessInDays+$LateLoginFine+$HelmetFine;


      //$NetSalary=$NetSalary-$PF-$ESI-$Advance-$MessInDays;

      $SqlOnDuty="SELECT sum(DaysTotal) as onduty from HrmOnDutyRegister where EmpID='$EmpID' and
       Month(StartDate)='$Month' and Year(StartDate)='$Year' ";
      $StmtOnDuty=$iconn->query($SqlOnDuty);
      $RowOnDuty = $StmtOnDuty->fetch_array();

      $TotalCountedDays = 0;
      if( $EarnedPayDays >= $TotalDays  ){
        $TotalCountedDays = $TotalDays;
        $GrossSalary = $TotalSalary;
      }else{
        $TotalCountedDays = $TotalDays - ($TotalDays - $EarnedPayDays);
        $GrossSalary = ($Payement/$TotalDays)*$TotalCountedDays;
      }
      $GrossSalary += $ArrearAmount+$ShipmentCharges;
      $TotalDeductions=$PF+$ESI+$MessInDays+$LateLoginFine+$HelmetFine+$Advance;
      $NetSalary= $GrossSalary - $TotalDeductions;

      $OnDuty=$RowOnDuty['onduty'];
      $currentWageDaysGroup = ($others >= 10) ? 'high' : 'low';
  if ($prevWageDaysGroup !== null && $prevWageDaysGroup !== $currentWageDaysGroup) {
          $colCount = 14;
          $content .='<tr><td colspan="'.$colCount.'" style="border-bottom: 2px solid #000;"></td></tr>';
        }
        $prevWageDaysGroup = $currentWageDaysGroup;
      $content .='<tr>
                    <td>'.$S_NO.'</td>
                    <td>'.$BioMetricID.'</td>
                    <td>'.$EmpName.'</td>
                    <td>'.$DOJ.'</td>
                    <td>'.$Designation.'</td>
                    <td style="text-align:right;">'.formatWithDecimals($TotalSalary, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.formatWithDecimals($PerDaySal, 2, '.', ',').'</td>
                    <td>'.($DaysWithoutWeekOffs-$Leaves).'</td>
                    <td>'.$WeekOff.'</td>
                    <td>'.$Leaves.'</td>
                    <td>'.$EarnedPayDays.'</td>
                    <td style="text-align:right;">'.formatWithDecimals($ArrearAmount, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.formatWithDecimals($ShipmentCharges, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.formatWithDecimals($GrossSalary, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.formatWithDecimals($PF, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.formatWithDecimals($Advance, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.formatWithDecimals($ESI, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.formatWithDecimals($LateLoginFine, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.formatWithDecimals($HelmetFine, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.formatWithDecimals($MessInDays, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.formatWithDecimals($TotalDeductions, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.formatWithOutDecimals($NetSalary, 2, '.', ',').'</td>

                    <td style="text-align:left;">'.$BankName.'</td>
                    <td style="text-align:right;">'.$AccNumber.'</td>
                    <td style="text-align:left;">'.$IFSC.'</td>
                    <td style="text-align:left;">'.$Remarks.'</td>
                  </tr>';
       $S_NO++;
       $TotalSalarySum=round($TotalSalarySum+$TotalSalary,2);
       $ToalGrossSalarySum = $ToalGrossSalarySum+$GrossSalary;
       $ToalGrossSalarySum = round($ToalGrossSalarySum,2);
       //$ToalGrossSalarySum=number_format($ToalGrossSalarySum, 2, '.', ',');
       $TotalWorkingDays = $TotalWorkingDays+$WageDays;
       $TotalWeekOffs=$TotalWeekOffs+$WeekOff;
       $TotalDaysSum=$TotalDaysSum+$TotalDays;
       $TotalPFSum=round($TotalPFSum+$PF,2);
       $TotalESISum=round($TotalESISum+$ESI,2);
       $TotalAdvSum=round($TotalAdvSum+$Advance,2);
       $TotalLateFineSum = round($TotalLateFineSum+$LateLoginFine,2);
       $TotalHelmetFineSum=round($TotalHelmetFineSum+$HelmetFine,2);
       $TotalMessChargesSum=round($TotalMessChargesSum+$MessInDays,2);
       $TotalDeductionsSum=round($TotalDeductionsSum+$TotalDeductions,2);
       $TotalArrearAmountSum =round($TotalArrearAmountSum+$ArrearAmount,2);
       $TotalShipmentChargesSum =round($TotalShipmentChargesSum+$ShipmentCharges,2);
       $TotalNetSalarySum=round($TotalNetSalarySum+$NetSalary,2);
     }
      $content .='<tr>
                    <td></td>
                    <td style="text-align:right;font-weight:800;">Grand Total:</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($TotalSalarySum).'</td>
                    <td></td>
                    <td style="font-weight:800;">'.$TotalWorkingDays.'</td>
                    <td style="font-weight:800;">'.$TotalWeekOffs.'</td>
                    <td></td>
                    <td style="font-weight:800;">'.$TotalDaysSum.'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($TotalArrearAmountSum).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($TotalShipmentChargesSum).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($ToalGrossSalarySum).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($TotalPFSum).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($TotalAdvSum).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($TotalESISum).'</td><!-Wage Deduction->
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($TotalLateFineSum).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($TotalHelmetFineSum).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($TotalMessChargesSum).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($TotalDeductionsSum).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithOutDecimals($TotalNetSalarySum).'</td>
                  </tr>';
    $content .='</tbody>';
    $content .='</table>';
    $Data['TABLE']=$content;
    $test = "Camaroo";

  return $Data;

}


function MonthlyWorkers(){
global  $iconn,$Month,$Year,$TotalDays,$EmpSubCategory,$MonthSegment,$query,$freeze_btn;//uma
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

          $SqlOnTimeEmpDetails="SELECT *,HED.EmpID,HED.BioMetricID as BioMetricID,HPD.PresentAddress,HWBM.BatchName,HWSM.SectionName,HWVM.VillageName,HMSD.TravelAllowances,
         (SELECT COALESCE(NoOfShortDays,0)  FROM HrmMonthlyShortDaysList as HMSL WHERE HMSL.EmpID=HED.EmpID and HMSL.Month='$Month' and HMSL.Year='$Year' ) as NoOfShortDays
          from HrmEmployeeDetails HED
        LEFT JOIN HrmDepartments HD  ON HD.DeptID=HED.DeptID
        LEFT JOIN HrmSubDepartments HSD  ON HSD.SubDeptID=HED.SubDeptID
        LEFT JOIN HrmDesignations HDG  ON HDG.DesigID=HED.DesigID
        LEFT JOIN HrmEmpPersonalDetails HPD ON HPD.EmpID=HED.EmpID

        LEFT JOIN HrmEmpBankAccountDetails HMBD ON HMBD.EmpID=HED.EmpID
        LEFT JOIN HrmMonthlySalaryDetails HMSD ON HMSD.BioMetricID=HED.BioMetricID
        and HMSD.Month='$Month' and HMSD.Year='$Year' and HMSD.ReportType=2
        LEFT JOIN EmpWorkerBatchMaster HWBM on HED.BatchID = HWBM.BatchID
        LEFT JOIN EmpWorkerSectionsMaster HWSM on HED.SectionID = HWSM.SectionID
        LEFT JOIN EmpWorkerVillageMaster HWVM on HED.VillageID = HWVM.VID

        WHERE  EmpStatus=1 and EmpCategory='Worker' AND WorkerSubCategory=1
        $query order by DepartmentName,HED.BioMetricID asc";
        $StmtOnTimeEmpDetails=$iconn->query($SqlOnTimeEmpDetails);



  $content='';


  $size=40;

  $size1=60;




        $S_NO=1;
        $width=120/14;
        $width_s=$width-5;
        $width_w=$width+5;


        $content .= '<div class="download-container">
                      <button class="download_btn" data-report-type="2">
                        <img src="../../assets/images/icons8-excel.svg" alt="" width="30">Download
                      </button>
                      '.$freeze_btn.'
                    </div>';
        $content .= '<table border="1" style="border-collapse: collapse; width: 100%;">';
        $content .= '
                    <thead>
                      <tr>
                        <th rowspan="2">S.No</th>
                        <th rowspan="2">Bio ID</th>
                        <th rowspan="2">NAME OF THE WORKER</th>
                        <th rowspan="2">VILLAGE</th>
                        <th rowspan="2">BATCH NAME</th>
                        <th rowspan="2">SECTION</th>
                        <th rowspan="2">WAGE FOR MONTH</th>
                        <th rowspan="2">WAGE PER DAY</th>
                        <th colspan="4">WORKING DAYS</th>
                        <th rowspan="2">Earned Wage Amount</th>
                        <th colspan="3">Over Time Allowances</th>
                        <th colspan="2">Travel Allowances</th>
                        <th rowspan="2">Arear Amount</th>
                        <th rowspan="2">Shipment charges (per day)</th>
                        <th rowspan="2">Gross Wages</th>
                        <th colspan="7">Deductions</th>
                        <th rowspan="2">Commission</th>
                        <th rowspan="2">Net Amount</th>
                      </tr>
                      <tr>
                        <th>No Of Worked Days</th>
                        <th>Extra Days</th>
                        <th>Add. Offs</th>
                        <th>Total Days</th>
                        <th>No Of OT Hours</th>
                        <th>OT Per Hour</th>
                        <th>Earned O.T Amount</th>
                        <th>Charges Per Day</th>
                        <th>Travel Allowances</th>
                        <th>PF & ESI</th>
                        <th>Wage Deduction</th>
                        <th>Bus Charge Deduction</th>
                        <th>Mess</th>
                        <th>Fine</th>
                        <th>Dress Code Deduction</th>
                        <th>Total Deductions</th>
                      </tr>
                    </thead>';

    $content .='<tbody>';
    // Initialize all totals in a single array
    $Totals = [
        'Salary' => 0,
        'PerDaySal' => 0,
        'GrossSalary' => 0,
        'WorkingDays' => 0,
        'WeekOffs' => 0,
        'TotalDays' => 0,
        'PF' => 0,
        'ESI' => 0,
        'Advance' => 0,
        'LateFine' => 0,
        'HelmetFine' => 0,
        'MessCharges' => 0,
        'Deductions' => 0,
        'NetSalary' => 0,
        'BusCharges' => 0,
        'Fine' => 0,
        'DressCodeFine' => 0,
        'ArearAmount' => 0,
        'Commission' => 0,
        'ShipmentCharges' => 0,
        'OThours' => 0,
        'OTAmount' => 0
    ];
    while($RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array()){

      $EmpID=$RowEmpCompltDtils['EmpID'];
      $EmpName=$RowEmpCompltDtils['EmpName'];
      // echo $EmpID;
      // $iconn->query("DELETE FROM HrmEmpSalaryDetails WHERE EmpID='$EmpID'");
      // $randomBasic = array('12000', '9600', '10000', '8000', '15000');

      // // Get a random value from the array
      // $randomValue = $randomBasic[array_rand($randomBasic)];

      // // Now insert into the database
      // $query = "INSERT INTO HrmEmpSalaryDetails (EmpID, Basic) VALUES ('$EmpID', '$randomValue')";
      // // Run this with your DB connection
      // $iconn->query($query);

      $EmpLocation=$RowEmpCompltDtils['PresentAddress'];
      $BatchName=$RowEmpCompltDtils['BatchName'];
      $VillageName=$RowEmpCompltDtils['VillageName'];
      $SectionName=$RowEmpCompltDtils['SectionName'];
      $DOJ= ($RowEmpCompltDtils['JoiningDate'] != '') ? $RowEmpCompltDtils['JoiningDate'] : 'N/A';
      $BioMetricID=$RowEmpCompltDtils['BioMetricID'];
      $DeptName=$RowEmpCompltDtils['DepartmentName'];
      $SubDeptName=$RowEmpCompltDtils['SubDeptName'];
      $Designation= ($RowEmpCompltDtils['Designation']!='') ? $RowEmpCompltDtils['Designation'] : 'N/A';
      $PerDaySal = round($GrossSalary/$TotalDays,2);
      $PerDaySal = number_format($PerDaySal, 2);
      $leavecount=$RowEmpCompltDtils['leavecount'];
      $StartDate=$RowEmpCompltDtils['StartDate'];
      $EndDate=$RowEmpCompltDtils['EndDate'];

      $TotalPresents=$RowEmpCompltDtils['TotalPresents'];
      $SingleDuty=$RowEmpCompltDtils['SingleDuty'];
      $DoubleDuty=$RowEmpCompltDtils['DoubleDuty'];
      $OnDuty=$RowEmpCompltDtils['DaysTotal'];
      $HalfDay=$RowEmpCompltDtils['HalfDay'];
      $Absent=$RowEmpCompltDtils['Absent'];
      $WeekOff=$RowEmpCompltDtils['WeekOff'];
      $Holidays=$RowEmpCompltDtils['Holidays'];
      $Leaves=$RowEmpCompltDtils['Leaves'];
      $ML=$RowEmpCompltDtils['ML'];
      $WageDays=$RowEmpCompltDtils['WageDays'];
      $Payement=$RowEmpCompltDtils['EarnedSalaryWithoutDud'];
      $PF=$RowEmpCompltDtils['PF'];
      $ESI=$RowEmpCompltDtils['ESI'];
      $Advance=$RowEmpCompltDtils['Adavance'];
      $NoOfShortDays=$RowEmpCompltDtils['NoOfShortDays'];
      $BankIFSC=$RowEmpCompltDtils['IFSC'];
      $AccNo=$RowEmpCompltDtils['AccNumber'];
      $MessInDays=$RowEmpCompltDtils['MessInDays'];
      $LateLoginFine=$RowEmpCompltDtils['LateLoginFine'];
      $AutoChargePerDay=$RowEmpCompltDtils['AutoChargePerDay'];
      $DessCodeDeductions=$RowEmpCompltDtils['DessCodeDeductions'];
      $BusChargeDeductions=$RowEmpCompltDtils['BusChargeDeductions'];
      $ArearAmount=$RowEmpCompltDtils['ArrearAmount'];
      $ShipmentCharges=$RowEmpCompltDtils['ShipmentCharges'];
      $OThours=$RowEmpCompltDtils['OThours'];
      $OtPerHour=30;
      $OTAmount=  $OtPerHour*$OThours;
      $TotalDays = $RowEmpCompltDtils['Total'];
      $Commission = $RowEmpCompltDtils['Commission'];
      // $ReqWorkingDays=$TotalDays-$WeekOff;
      $PerDaySal = $Payement/$TotalDays;

      $TotalSalary=$Payement;
      //$DaysWithoutWeekOffs=$SingleDuty+($DoubleDuty * 2)+($HalfDay/2)+$Holidays;
      $DaysWithoutWeekOffs=$SingleDuty+($HalfDay/2)+$Leaves;
      $EarnedPayDays=$DaysWithoutWeekOffs+$WeekOff;


      $LoadingCharges=0;
      $TravelCharges=0;
      $TravelAllowances=0;
      $GrossWages=$EarnedWageAmt+$AreaAmount+$LoadingCharges+$TravelAllowances;

      $SqlOnDuty="SELECT sum(DaysTotal) as onduty from HrmOnDutyRegister where EmpID='$EmpID' and
       Month(StartDate)='$Month' and Year(StartDate)='$Year' ";
      $StmtOnDuty=$iconn->query($SqlOnDuty);
      $RowOnDuty = $StmtOnDuty->fetch_array();

      // $TotalCountedDays = 0;
      // if( $EarnedPayDays >= $TotalDays  ){
      //   $TotalCountedDays = $TotalDays;
      //   $GrossSalary = $TotalSalary;
      // }else{
      //   $TotalCountedDays = $TotalDays - ($TotalDays - $EarnedPayDays);
      //   $GrossSalary = ($Payement/$TotalDays)*$TotalCountedDays;
      // }
      $GrossSalary = ($Payement/$TotalDays)*$EarnedPayDays;
      $TotalDeductions=$PF+$ESI+$MessInDays+$LateLoginFine+$BusChargeDeductions+$DessCodeDeductions;
      $TravelAllowances = $AutoChargePerDay*$DaysWithoutWeekOffs;
      $GrossSalaryWithAllowances=$GrossSalary+$TravelAllowances+$ArearAmount+$ShipmentCharges+$OTAmount;
      $NetSalary= ($GrossSalaryWithAllowances+$Commission) - $TotalDeductions;

      $OnDuty=$RowOnDuty['onduty'];
      $content .='<tr>
                    <td>'.$S_NO.'</td>
                    <td>'.$BioMetricID.'</td>
                    <td>'.$EmpName.'</td>
                    <td>'.$VillageName.'</td>
                    <td>'.$BatchName.'</td>
                    <td>'.$SectionName.'</td>
                    <td style="text-align:right;">'.formatWithDecimals($TotalSalary).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($PerDaySal).'</td>
                    <td>'.$DaysWithoutWeekOffs.'</td>
                    <td></td>
                    <td>'.$WeekOff.'</td>
                    <td>'.$EarnedPayDays.'</td>
                    <td style="text-align:right;">'.formatWithDecimals($GrossSalary).'</td>
                    <td style="text-align:right;">'.$OThours.'</td>
                    <td style="text-align:right;">'.$OtPerHour.'</td>
                    <td style="text-align:right;">'.formatWithDecimals($OTAmount).'</td>
                    <td style="text-align:right;">'.$AutoChargePerDay.'</td>
                    <td style="text-align:right;">'.formatWithDecimals($TravelAllowances).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($ArearAmount).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($ShipmentCharges).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($GrossSalaryWithAllowances).'</td>
                    <td>'.formatWithDecimals($PF).'</td>
                    <td>'.formatWithDecimals($ESI).'</td>
                    <td>'.formatWithDecimals($BusChargeDeductions).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($MessInDays).'</td>
                    <td>'.formatWithDecimals($LateLoginFine).'</td>
                    <td>'.formatWithDecimals($DessCodeDeductions).'</td>
                    <td>'.formatWithDecimals($TotalDeductions).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($Commission).'</td>
                    <td style="text-align:right;">'.formatWithOutDecimals($NetSalary).'</td>
                 </tr>';
      $S_NO++;
      $Totals['Salary']+=$TotalSalary;
      $Totals['WorkingDays']+=$DaysWithoutWeekOffs;
      $Totals['WeekOffs']+=$WeekOff;
      $Totals['TotalDays']+=$EarnedPayDays;
      $Totals['GrossSalary']+=$GrossSalary;
      $Totals['TravelAllowances']+=$TravelAllowances;
      $Totals['TotalGrossSalary']+=$GrossSalaryWithAllowances;
      $Totals['PF']+=$PF;
      $Totals['ESI']+=$ESI;
      $Totals['BusCharges']+=$BusChargeDeductions;
      $Totals['MessCharges']+=$MessInDays;
      $Totals['Fine']+=$LateLoginFine;
      $Totals['DressCodeFine']+=$DessCodeDeductions;
      $Totals['NetSalary']+=$NetSalary;
      $Totals['Deductions']+=$TotalDeductions;
      $Totals['ArearAmount']+=$ArearAmount;
      $Totals['Commission']+=$Commission;
      $Totals['ShipmentCharges']+=$ShipmentCharges;
      $Totals['OThours']+=$OThours;
      $Totals['OTAmount']+=$OTAmount;
    }
      $content .='<tr>
                    <td></td>
                    <td style="text-align:right;font-weight:800;">Grand Total:</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['Salary']).'</td>
                    <td style="text-align:right;font-weight:800;"></td>
                    <td style="font-weight:800;">'.$Totals['WorkingDays'].'</td>
                    <td></td>
                    <td style="font-weight:800;">'.$Totals['WeekOffs'].'</td>
                    <td style="font-weight:800;">'.$Totals['TotalDays'].'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['GrossSalary']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['OThours']).'</td>
                    <td style="text-align:right;font-weight:800;"></td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['OTAmount']).'</td>
                    <td style="text-align:right;font-weight:800;"></td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['TravelAllowances']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['ArearAmount']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['ShipmentCharges']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['TotalGrossSalary']).'</td>
                    <td style="font-weight:800;">'.formatWithDecimals($Totals['PF']).'</td>
                    <td style="font-weight:800;">'.formatWithDecimals($Totals['ESI']).'</td>
                    <td style="font-weight:800;">'.formatWithDecimals($Totals['BusCharges']).'</td>
                    <td style="font-weight:800;">'.formatWithDecimals($Totals['MessCharges']).'</td>
                    <td style="font-weight:800;">'.formatWithDecimals($Totals['Fine']).'</td>
                    <td style="font-weight:800;">'.formatWithDecimals($Totals['DressCodeFine']).'</td>
                    <td style="font-weight:800;">'.formatWithDecimals($Totals['Deductions']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['Commission']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithOutDecimals($Totals['NetSalary']).'</td>
                  </tr>';
    $content .='</tbody>';
    $content .='</table>';
    $Data['TABLE']=$content;

    return $Data;

}

function FifteenDaysWorkers($iconn,$SqlOnTimeEmpDetails){
global  $iconn,$Month,$Year,$TotalDays,$EmpSubCategory,$MonthSegment,$query,$freeze_btn;//uma
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

        $SqlOnTimeEmpDetails="SELECT *,HED.EmpID,HED.BioMetricID as BioMetricID,HPD.PresentAddress,HWBM.BatchName,HWSM.SectionName,HWVM.VillageName,HWSM.SectionName,HMSD.TravelAllowances,
         (SELECT COALESCE(NoOfShortDays,0)  FROM HrmMonthlyShortDaysList as HMSL WHERE HMSL.EmpID=HED.EmpID and HMSL.Month='$Month' and HMSL.Year='$Year' ) as NoOfShortDays
          from HrmEmployeeDetails HED
        LEFT JOIN HrmDepartments HD  ON HD.DeptID=HED.DeptID
        LEFT JOIN HrmSubDepartments HSD  ON HSD.SubDeptID=HED.SubDeptID
        LEFT JOIN HrmDesignations HDG  ON HDG.DesigID=HED.DesigID
        LEFT JOIN HrmEmpPersonalDetails HPD ON HPD.EmpID=HED.EmpID

        LEFT JOIN HrmEmpBankAccountDetails HMBD ON HMBD.EmpID=HED.EmpID
        LEFT JOIN HrmMonthlySalaryDetails HMSD ON HMSD.BioMetricID=HED.BioMetricID
        and HMSD.Month='$Month' and HMSD.Year='$Year' and HMSD.ReportType=3
        LEFT JOIN EmpWorkerBatchMaster HWBM on HED.BatchID = HWBM.BatchID
        LEFT JOIN EmpWorkerSectionsMaster HWSM on HED.SectionID = HWSM.SectionID
        LEFT JOIN EmpWorkerVillageMaster HWVM on HED.VillageID = HWVM.VID

        WHERE  EmpStatus=1 and EmpCategory='Worker' AND WorkerSubCategory=2  AND HMSD.MonthSegment='$MonthSegment'
        $query order by DepartmentName,HED.BioMetricID asc";

        $StmtOnTimeEmpDetails=$iconn->query($SqlOnTimeEmpDetails);



  $content='';


  $size=40;

  $size1=60;




        $S_NO=1;
        $width=120/14;
        $width_s=$width-5;
        $width_w=$width+5;



        $content .= '<div class="download-container">
                      <button class="download_btn" data-report-type="3">
                        <img src="../../assets/images/icons8-excel.svg" alt="" width="30">Download
                      </button>
                      '.$freeze_btn.'
                    </div>';
        $content .= '<table border="1" style="border-collapse: collapse; width: 100%;">';
        $content .= '
                      <thead>
                        <tr>
                          <th rowspan="2">S.No</th>
                          <th rowspan="2">Bio ID</th>
                          <th rowspan="2">NAME OF THE WORKER</th>
                          <th rowspan="2">VILLAGE</th>
                          <th rowspan="2">BATCH NAME</th>
                          <th rowspan="2">DESIGNATION</th>
                          <th rowspan="2">WAGE PER DAY</th>
                          <th rowspan="2">WORKING DAYS</th>
                          <th rowspan="2">Earned Wage Amount</th>
                          <th colspan="2">Travel Allowances</th>
                          <th rowspan="2">Sunday Incentive</th>
                          <th rowspan="2">Gross Wages</th>
                          <th colspan="7">Deductions</th>
                          <th rowspan="2">Commission</th>
                          <th rowspan="2">Net</br/>Amount</th>
                        </tr>
                        <tr>
                          <th>Charges Per Day</th>
                          <th>Travel Allowances</th>
                          <th>PF & ESI</th>
                          <th>Wage Deduction</th>
                          <th>Bus Charge Deduction</th>
                          <th>Mess</th>
                          <th>Fine</th>
                          <th>Dress Code Deduction</th>
                          <th>Total Deductions</th>
                        </tr>
                    </thead>';
    $content .='<tbody>';
    // Initialize all totals in a single array
    $Totals = [
        'Salary' => 0,
        'PerDaySal' => 0,
        'GrossSalary' => 0,
        'WorkingDays' => 0,
        'WeekOffs' => 0,
        'TotalDays' => 0,
        'PF' => 0,
        'ESI' => 0,
        'Advance' => 0,
        'LateFine' => 0,
        'HelmetFine' => 0,
        'MessCharges' => 0,
        'Deductions' => 0,
        'NetSalary' => 0,
        'EarnedWage'=>0,
        'SundayIncentive'=>0,
        'Commission'=>0
    ];
     while($RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array()){
      // $EmpID=$RowEmpCompltDtils['EmpID'];
      // echo $EmpID;
      // $iconn->query("DELETE FROM HrmEmpSalaryDetails WHERE EmpID='$EmpID'");
      // $randomBasic = array('350');

      // // Get a random value from the array
      // $randomValue = $randomBasic[array_rand($randomBasic)];

      // // Now insert into the database
      // $query = "INSERT INTO HrmEmpSalaryDetails (EmpID, Basic) VALUES ('$EmpID', '$randomValue')";
      // // Run this with your DB connection
      // $iconn->query($query);
      $EmpName=$RowEmpCompltDtils['EmpName'];
      $EmpLocation=$RowEmpCompltDtils['PresentAddress'];
      $BatchName=$RowEmpCompltDtils['BatchName'];
      $VillageName=$RowEmpCompltDtils['VillageName'];
      $SectionName=$RowEmpCompltDtils['SectionName'];
      $TravelAllowances=$RowEmpCompltDtils['TravelAllowances'];
      $DOJ= ($RowEmpCompltDtils['JoiningDate'] != '') ? $RowEmpCompltDtils['JoiningDate'] : 'N/A';
      $BioMetricID=$RowEmpCompltDtils['BioMetricID'];
      $DeptName=$RowEmpCompltDtils['DepartmentName'];
      $SubDeptName=$RowEmpCompltDtils['SubDeptName'];
      $Designation= ($RowEmpCompltDtils['Designation']!='') ? $RowEmpCompltDtils['Designation'] : 'N/A';
      $PerDaySal = round($GrossSalary/$TotalDays,2);
      $PerDaySal = number_format($PerDaySal, 2);
      $leavecount=$RowEmpCompltDtils['leavecount'];
      $StartDate=$RowEmpCompltDtils['StartDate'];
      $EndDate=$RowEmpCompltDtils['EndDate'];

      $TotalPresents=$RowEmpCompltDtils['TotalPresents'];
      $SingleDuty=$RowEmpCompltDtils['SingleDuty'];
      $DoubleDuty=$RowEmpCompltDtils['DoubleDuty'];
      $OnDuty=$RowEmpCompltDtils['DaysTotal'];
      $HalfDay=$RowEmpCompltDtils['HalfDay'];
      $Absent=$RowEmpCompltDtils['Absent'];
      $WeekOff=$RowEmpCompltDtils['WeekOff'];
      $Holidays=$RowEmpCompltDtils['Holidays'];
      $Leaves=$RowEmpCompltDtils['Leaves'];
      $ML=$RowEmpCompltDtils['ML'];
      $WageDays=$RowEmpCompltDtils['WageDays'];
      $Payement=$RowEmpCompltDtils['EarnedSalaryWithoutDud'];
      $PF=$RowEmpCompltDtils['PF'];
      $ESI=$RowEmpCompltDtils['ESI'];
      $Advance=$RowEmpCompltDtils['Adavance'];
      $NoOfShortDays=$RowEmpCompltDtils['NoOfShortDays'];
      $BankIFSC=$RowEmpCompltDtils['IFSC'];
      $AccNo=$RowEmpCompltDtils['AccNumber'];
      $MessInDays=$RowEmpCompltDtils['MessInDays'];
      $LateLoginFine=$RowEmpCompltDtils['LateLoginFine'];
      $AutoChargePerDay=$RowEmpCompltDtils['AutoChargePerDay'];
      $DessCodeDeductions=$RowEmpCompltDtils['DessCodeDeductions'];
      $BusChargeDeductions=$RowEmpCompltDtils['BusChargeDeductions'];
      $SundayIncentive=$RowEmpCompltDtils['SundayIncentive'];
      $Commission=$RowEmpCompltDtils['Commission'];

      $GrossSalary=$Payement;
      $NetSalary=round($GrossSalary-(($GrossSalary*$NoOfShortDays)/30));
      $TotalDeductions=$ESI+$MessInDays+$LateLoginFine+$HelmetFine;


      $NetSalary=$NetSalary-$PF-$ESI-$Advance-$MessInDays;
      $LateLoginFine=$RowEmpCompltDtils['LateLoginFine'];
      $EarnedWageAmt=$PerDaySal*$TotalDays;
      $AreaAmount=0;
      $LoadingCharges=0;
      $TravelCharges=0;
      $TravelAllowances=0;
      //$GrossWages=$EarnedWageAmt+$AreaAmount+$LoadingCharges+$TravelAllowances+$SundayIncentive+$Commission;

      $TotalSalary=$Payement;
      $DaysWithoutWeekOffs=$SingleDuty+($DoubleDuty * 2)+($HalfDay/2)+$Holidays;
      $EarnedPayDays=$DaysWithoutWeekOffs+$WeekOff;
      $EarnedWageAmt = $GrossSalary*$DaysWithoutWeekOffs;

      $SqlOnDuty="SELECT sum(DaysTotal) as onduty from HrmOnDutyRegister where EmpID='$EmpID' and
       Month(StartDate)='$Month' and Year(StartDate)='$Year' ";
      $StmtOnDuty=$iconn->query($SqlOnDuty);
      $RowOnDuty = $StmtOnDuty->fetch_array();

      $TotalDeductions=$PF+$ESI+$MessInDays+$LateLoginFine+$BusChargeDeductions+$DessCodeDeductions;
      $OtPerHour=30;
      $TravelAllowances = $AutoChargePerDay*$DaysWithoutWeekOffs;
      $GrossSalaryWithAllowances=$EarnedWageAmt+$TravelAllowances+$SundayIncentive;
      $NetSalary= ($GrossSalaryWithAllowances+$Commission) - $TotalDeductions;

      $OnDuty=$RowOnDuty['onduty'];
      $content .='<tr>
                    <td>'.$S_NO.'</td>
                    <td>'.$BioMetricID.'</td>
                    <td>'.$EmpName.'</td>
                    <td>'.$VillageName.'</td>
                    <td>'.$BatchName.'</td>
                    <td>'.$Designation.'</td>
                    <td>'.formatWithDecimals($GrossSalary, 2, '.', ',').'</td>
                    <td>'.$DaysWithoutWeekOffs.'</td>
                    <td style="text-align:right;">'.formatWithDecimals($EarnedWageAmt, 2, '.', ',').'</td>
                    <td style="text-align:right;">'.$AutoChargePerDay.'</td>
                    <td style="text-align:right;">'.formatWithDecimals($TravelAllowances).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($SundayIncentive).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($GrossSalaryWithAllowances).'</td>
                    <td>'.formatWithDecimals($PF).'</td>
                    <td>'.formatWithDecimals($ESI).'</td>
                    <td>'.formatWithDecimals($BusChargeDeductions).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($MessInDays).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($LateLoginFine).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($DessCodeDeductions).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($TotalDeductions).'</td>
                    <td style="text-align:right;">'.formatWithDecimals($Commission).'</td>
                    <td style="text-align:right;">'.formatWithOutDecimals($NetSalary, 2, '.', ',').'</td>
                 </tr/>';
      $S_NO++;
      $Totals['EarnedWage']+=$EarnedWageAmt;
      $Totals['WorkingDays']+=$DaysWithoutWeekOffs;
      $Totals['WeekOffs']+=$WeekOff;
      $Totals['TotalDays']+=$EarnedPayDays;
      $Totals['GrossSalary']+=$GrossSalary;
      $Totals['TravelAllowances']+=$TravelAllowances;
      $Totals['SundayIncentive']+=$SundayIncentive;
      $Totals['Commission']+=$Commission;
      $Totals['TotalGrossSalary']+=$GrossSalaryWithAllowances;
      $Totals['PF']+=$PF;
      $Totals['ESI']+=$ESI;
      $Totals['BusCharges']+=$BusChargeDeductions;
      $Totals['MessCharges']+=$MessInDays;
      $Totals['Fine']+=$LateLoginFine;
      $Totals['DressCodeFine']+=$DessCodeDeductions;
      $Totals['NetSalary']+=$NetSalary;
      $Totals['Deductions']+=$TotalDeductions;

      }

      $content .='<tr>
                    <td></td>
                    <td style="text-align:right;font-weight:800;">Grand Total:</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align:right;font-weight:800;"></td>
                    <td style="font-weight:800;">'.$Totals['WorkingDays'].'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['EarnedWage']).'</td>
                    <td></td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['TravelAllowances']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['SundayIncentive']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['TotalGrossSalary']).'</td>
                    <td style="font-weight:800;">'.formatWithDecimals($Totals['PF']).'</td>
                    <td style="font-weight:800;">'.formatWithDecimals($Totals['ESI']).'</td>
                    <td style="font-weight:800;">'.formatWithDecimals($Totals['BusCharges']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['MessCharges']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['Fine']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['DressCodeFine']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['Deductions']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithDecimals($Totals['Commission']).'</td>
                    <td style="text-align:right;font-weight:800;">'.formatWithOutDecimals($Totals['NetSalary']).'</td>
                  </tr>';
      $content .='</tbody>';
      $content .='</table>';
      $Data['TABLE']=$content;
      return $Data;
}

// function formatWithDecimals($value) {
//     $formatted = convertValueToIndianFormat($value);

//     // Check if it already has decimals
//     if (strpos($formatted, '.') === false) {
//         $formatted .= '.00';
//     }
//     return $formatted;
// }

function formatWithDecimals($value) {
    // First: round the value to 2 decimals
    $value = number_format((float)$value, 2, '.', '');

    // Then: pass it to your Indian formatter
    return convertValueToIndianFormat($value);
}

function formatWithOutDecimals($value) {
    // First: round the value to 2 decimals
    $value = number_format(round($value), 0, '.', '');

    // Then: pass it to your Indian formatter
    return convertValueToIndianFormat($value);
}

?>

<!doctype html>
<html lang="en">

    <head>

         <?php include ($IncludesPath."title-meta.php"); ?>
         <?php include ($IncludesPath."head-css.php");?>

    </head>
    <style type="text/css">
    html, body {
      overflow-x: visible !important;
    }
    body {
      font-family: 'Inter', sans-serif;
    }
    table tr td{
      font-size: 14px;
      text-align: left;
    }

    table tr th{

      font-size: 14px;
      text-align: center;
      background: #A9D18E;
      position: sticky!important;
      text-shadow: none;
      font-weight: 800;
      top: 0;
    }

    table thead tr:first-child th {
      width: 500px;
    }

    table thead tr:nth-child(2) th {
      width: 500px;
      position: sticky;
      top: 34;
    }

    table {
      width: 100%;
      table-layout: auto;
      background: #fff;
    }

    th, td {
      white-space: nowrap;
      padding: 8px 8px 8px 8px;
    }
    .download_btn{
     padding: 5px 10px;
     font-size: 16px;
     background-color: white;
     color: #217346;
     border: 2px solid #217346;
     border-radius: 5px;
     cursor: pointer;
     display: inline-flex;
     align-items: center;
     margin-bottom: 20px;
    }
    .download-container{
      text-align: center;
    }
    .page-heading h2{font-size: 18px;text-align: center;}
    </style>
    <body>
 <?php  include ($IncludesPath."PreLoader.php"); ?>
        <div class="pt-3">
            <div class="">
                <div class="row">
                    <div class="col-lg-12">
                      <?php
                      if($EmpCategory == 'Staff'){
                        $table_data=getStaff_TABLE();
                        echo $table_data['TABLE'];
                      }else if($EmpCategory == 'Worker'){
                        if($EmpSubCategory=='1'){
                          $table_data=MonthlyWorkers();
                          echo $table_data['TABLE'];
                        }else{
                          $table_data=FifteenDaysWorkers($iconn,$SqlOnTimeEmpDetails);
                          echo $table_data['TABLE'];
                        }
                      }
                      ?>
                    </div>
                </div>
            </div>
        </div>

        <?php include ($IncludesPath."vendor-scripts.php");?>

        <script src="<?php echo $BaseDist?>assets/js/app.js"></script>

    </body>
</html>
<script>

$('.download_btn').on('click',function(){

    Month = "<?php echo $Month;?>";
    Year = "<?php echo $Year;?>";
    ReportType = $(this).attr('data-report-type');
    MonthSegment="<?php echo $MonthSegment;?>";
    MonthSegmentStart="<?php echo $MonthSegmentStart; ?>";
    MonthSegmentEnd="<?php echo $MonthSegmentEnd; ?>";

    var url = '../Ajax/AjaxSalarySheetExcelReports.php?Year=' + Year + '&Month=' + Month + '&ReportType=' + ReportType + '&MonthSegment=' + MonthSegment + '&MonthSegmentStart=' + MonthSegmentStart + '&MonthSegmentEnd=' + MonthSegmentEnd +'&excelDownload=T';
    window.location.href = url; // ✅ trigger download directly
});

$('.freeze-btn').on('click',function(){

   var data = {
      DeptID: '<?php echo $_GET['DeptID']; ?>',
      SubDeptID: '<?php echo $_GET['SubDeptID']; ?>',
      Month: '<?php echo $_GET['Month']; ?>',
      Year: '<?php echo $_GET['Year']; ?>',
      EmpCategory: '<?php echo $_GET['EmpCategory']; ?>',
      EmpSubCategory: '<?php echo $_GET['EmpSubCategory']; ?>',
      MonthSegment: '<?php echo $_GET['MonthSegment']; ?>',
      StartDate: '<?php echo $_GET['StartDate']; ?>',
      EndDate: '<?php echo $_GET['EndDate']; ?>'
  };

      $.ajax({
        url: '../Ajax/AjaxFreezeSalarySheet.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {

            if (response.status === 'success') {
                alert('Salary sheet frozen successfully.');

                // Disable button
                $('.freeze-btn').prop('disabled', true);

                // Show status
                $('#freezeStatus')
                    .removeClass('text-danger')
                    .addClass('text-success')
                    .html('Already Frozen');

            } else {
                alert(response.message);
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert('Unable to freeze salary sheet.');
        }
    });

})

</script>
