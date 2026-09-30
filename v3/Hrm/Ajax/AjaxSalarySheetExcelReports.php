<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include_once ROOT . 'config/api-call.php';
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php';


ob_start();


function formatWithDecimals($value) {
    // First: round the value to 2 decimals
    $value = number_format((float)$value, 2, '.', '');

    // Then: pass it to your Indian formatter
    return convertValueToIndianFormat($value);
}

function cleanNumber($num) {
    return floatval(str_replace([',', "'"], '', $num)); // Remove comma & apostrophe, convert to number
}


if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $Year = trim($_GET["Year"]);
    $Month = trim($_GET["Month"]);
    $ReportType = trim($_GET["ReportType"]);
    $MonthSegment=trim($_GET["MonthSegment"]);
    $MonthSegmentStart=trim($_GET["MonthSegmentStart"]);
    $MonthSegmentEnd=trim($_GET["MonthSegmentEnd"]);
}

if($ReportType == 1){

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

$SqlquryResult = $iconn->query($qury);
$remakslist = [];
 while($Rowqury = $SqlquryResult->fetch_array()){
  $remakslist[$Rowqury['EmpID']] = $Rowqury['Remarks'];
 }

      $SqlOnTimeEmpDetails="SELECT *,AdminBankDetails.BankName,
            HMBD.AccNumber,
            HMBD.IFSC,HED.BioMetricID as BioMetricID,
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

    $timestamp = date("YmdHis");
    $spreadsheet = new PHPExcel();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Font Size
    $spreadsheet->getDefaultStyle()->getFont()->setSize(10);

    // Bold for header rows
    $sheet->getStyle('1')->getFont()->setBold(true);
    $sheet->getStyle('2')->getFont()->setBold(true);

    // --- Row 1 Headers ---
    $sheet->setCellValue('A1', 'S.No');
    $sheet->setCellValue('B1', 'Bio.Code No');
    $sheet->setCellValue('C1', 'Name of the Employe');
    $sheet->setCellValue('D1', 'Date of Joining');
    $sheet->setCellValue('E1', 'Designation');
    $sheet->setCellValue('F1', 'Total Salary');
    $sheet->setCellValue('G1', 'Per Day');

    // Working days group
    $sheet->setCellValue('H1', 'Working days');
    $sheet->mergeCells('H1:K1');

    // New columns
    $sheet->setCellValue('L1', 'Arrear Amount');
    $sheet->mergeCells('L1:L2');
    $sheet->setCellValue('M1', 'Shipment Charges');
    $sheet->mergeCells('M1:M2');

    // Gross Salary
    $sheet->setCellValue('N1', 'Gross Salary');
    $sheet->mergeCells('N1:N2');

    // Deductions group
    $sheet->setCellValue('O1', 'Deductions');
    $sheet->mergeCells('O1:U1');

    // Net Salary
    $sheet->setCellValue('V1', 'Net Salary');
    $sheet->mergeCells('V1:V2');

    // Bank Details
    $sheet->setCellValue('W1', 'Bank Details');
    $sheet->mergeCells('W1:Y1');
    $style = $sheet->getStyle('W1:Y1');
    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $style->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    // --- Row 2 Subheaders ---
    $sheet->setCellValue('H2', 'No of Working Days');
    $sheet->setCellValue('I2', 'Add.Offs');
    $sheet->setCellValue('J2', 'Consider Days');
    $sheet->setCellValue('K2', 'Total Days');

    $sheet->setCellValue('O2', 'PF & ESI');
    $sheet->setCellValue('P2', 'Wage Deduction');
    $sheet->setCellValue('Q2', 'Advance');
    $sheet->setCellValue('R2', 'Late Coming Fine');
    $sheet->setCellValue('S2', 'Helmate Deduction');
    $sheet->setCellValue('T2', 'Mess');
    $sheet->setCellValue('U2', 'Total Deductions');

    $sheet->setCellValue('W2', 'Bank Name');
    $sheet->setCellValue('X2', 'Account Number');
    $sheet->setCellValue('Y2', 'IFSC Code');
    $sheet->setCellValue('Z1', 'Remarks');
    $sheet->mergeCells('Z1:Z2');

    // --- Styling ---
    $styleArray = [
        'fill' => [
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => ['rgb' => 'D9EAD3']
        ],
        'alignment' => [
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap'       => true
        ],
        'font' => [
            'bold' => true,
        ],
        'borders' => [
            'allborders' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];

    // Apply style to merged header cells (vertically spanned)
    $columnsToRowspan = ['A','B','C','D','E','F','G','L','M','N','V','Z'];
    foreach ($columnsToRowspan as $col) {
        $sheet->mergeCells("{$col}1:{$col}2");
        $sheet->getStyle("{$col}1:{$col}2")->applyFromArray($styleArray);
    }

    // Group headers
$sheet->getStyle('H1:K1')->applyFromArray($styleArray);
$sheet->getStyle('O1:U1')->applyFromArray($styleArray);
$sheet->getStyle('W1:Y1')->applyFromArray($styleArray);

// Sub headers
$sheet->getStyle('H2:K2')->applyFromArray($styleArray);
$sheet->getStyle('O2:U2')->applyFromArray($styleArray);
$sheet->getStyle('W2:Y2')->applyFromArray($styleArray);

    // --- Column Widths ---
    foreach (range('A', 'Y') as $columnID) {
        if ($columnID === 'A') {
            $sheet->getColumnDimension($columnID)->setWidth(8);
        } elseif ($columnID === 'B') {
            $sheet->getColumnDimension($columnID)->setWidth(10);
        } elseif ($columnID === 'C') {
            $sheet->getColumnDimension($columnID)->setWidth(30);
        } else {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    // --- Align Numbers to Right ---
    $columnsToAlign = ['F','G','J','L','M','N','O','P','Q','R','S','T','U','V','X'];
    foreach ($columnsToAlign as $col) {
        $sheet->getStyle($col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    }

    // --- Data Row Start ---
    $row = 3;

    $S_NO=1;
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
        'ArrearAmount' => 0,
        'ShipmentCharges' => 0
    ];
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
        $Others=$RowEmpCompltDtils['Others'];
        $Payement=$RowEmpCompltDtils['EarnedSalaryWithoutDud'];
        $PF=$RowEmpCompltDtils['PF'];
        $ESI=$RowEmpCompltDtils['ESI'];//Wage Deduction
        $Advance=$RowEmpCompltDtils['Adavance'];
        $NoOfShortDays=$RowEmpCompltDtils['NoOfShortDays'];
        $BankIFSC=$RowEmpCompltDtils['IFSC'];
        $AccNo=$RowEmpCompltDtils['AccNumber'];
        $MessInDays=$RowEmpCompltDtils['MessInDays'];
        $LateLoginFine=$RowEmpCompltDtils['LateLoginFine'];
        $HelmetFine=$RowEmpCompltDtils['HelmetFine'];
        $ArrearAmount=$RowEmpCompltDtils['ArrearAmount'];
        $ShipmentCharges=$RowEmpCompltDtils['ShipmentCharges'];
        $TotalDays = $RowEmpCompltDtils['Total'];
        // $ReqWorkingDays=$TotalDays-$WeekOff;
        $PerDaySal = $Payement/$TotalDays;


        $TotalSalary=$Payement;
        $DaysWithoutWeekOffs=$SingleDuty+($HalfDay/2)+$Leaves;
        if( $Year == 2026 && ($Month == "02" || $Month == 2)){
            if( ($DaysWithoutWeekOffs - $Leaves) >= 24 ){
                $WeekOff = 4;
            }
          }
        $EarnedPayDays=$DaysWithoutWeekOffs+$WeekOff;

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
        $currentWageDaysGroup = ($Others >= 10) ? 'high' : 'low';
        if ($prevWageDaysGroup !== null && $prevWageDaysGroup !== $currentWageDaysGroup) {
          for ($col = 'A'; $col <= 'Z'; $col++) {
            $sheet->getStyle("{$col}{$row}")
              ->getBorders()
              ->getBottom()
              ->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
          }
          $row++;
        }
        $prevWageDaysGroup = $currentWageDaysGroup;
        $sheet->setCellValue("A$row", $S_NO);
        $sheet->setCellValue("B$row", $BioMetricID);
        $sheet->setCellValue("C$row", $EmpName);
        $sheet->setCellValue("D$row", $DOJ);
        $sheet->setCellValue("E$row", $Designation);


        $sheet->setCellValue("F$row", cleanNumber($TotalSalary));
        $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $PerDaySal = formatWithDecimals($PerDaySal, 2, '.', ',');
        $sheet->setCellValue("G$row", cleanNumber($PerDaySal));
        $sheet->getStyle("G$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("H$row", formatWithDecimals(($DaysWithoutWeekOffs-$Leaves), 2, '.', ','));
        $sheet->setCellValue("I$row", $WeekOff);
        $sheet->setCellValue("J$row", $Leaves);
        $sheet->setCellValue("K$row", $EarnedPayDays);

        $sheet->setCellValue("L$row", cleanNumber($ArrearAmount));
        $sheet->getStyle("L$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("M$row", cleanNumber($ShipmentCharges));
        $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("N$row", cleanNumber($GrossSalary));
        $sheet->getStyle("N$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("O$row", cleanNumber($PF));
        $sheet->getStyle("O$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("P$row", cleanNumber($ESI));
        $sheet->getStyle("P$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("Q$row", cleanNumber($Advance));
        $sheet->getStyle("Q$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("R$row", cleanNumber($LateLoginFine));
        $sheet->getStyle("R$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("S$row", cleanNumber($HelmetFine));
        $sheet->getStyle("S$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("T$row", cleanNumber($MessInDays));
        $sheet->getStyle("T$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("U$row", cleanNumber($TotalDeductions));
        $sheet->getStyle("U$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("V$row", cleanNumber(round($NetSalary)));
        $sheet->getStyle("V$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("W$row", $BankName);
        //$sheet->setCellValue("X$row", $AccNumber);
        $sheet->setCellValueExplicit(
            "X$row",
            $AccNumber,
            PHPExcel_Cell_DataType::TYPE_STRING
        );
        $sheet->setCellValue("Y$row", $IFSC);
        $sheet->setCellValue("Z$row", $Remarks);

        $row++;
        $S_NO++;
        $Totals['Salary']+=round($TotalSalary,2);
        $Totals['GrossSalary'] += round($GrossSalary,2);  
        $Totals['WorkingDays'] +=$WageDays;
        $Totals['WeekOffs'] +=$WeekOff;
        $Totals['TotalDays'] +=$TotalDays;
        $Totals['PF'] +=round($PF,2);
        $Totals['ESI'] +=round($ESI,2);//Wage Deduction
        $Totals['Advance'] +=round($Advance,2);
        $Totals['LateFine'] +=round($LateLoginFine,2);
        $Totals['HelmetFine'] +=round($HelmetFine,2);
        $Totals['MessCharges'] +=round($MessInDays,2);
        $Totals['Deductions'] +=round($TotalDeductions,2);
        $Totals['NetSalary'] +=round($NetSalary,2);
        $Totals['ArrearAmount'] +=round($ArrearAmount,2);
        $Totals['ShipmentCharges'] +=round($ShipmentCharges,2);
    }


    // Footer Totals Row
    if ($Totals) {
        $sheet->setCellValue("A$row", "Grand Total:");
        $sheet->setCellValue("F$row", cleanNumber($Totals['Salary']));
        $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue("H$row", cleanNumber($Totals['WorkingDays']));

        $sheet->setCellValue("I$row", cleanNumber($Totals['WeekOffs']));
        $sheet->setCellValue("K$row", cleanNumber($Totals['TotalDays']));

        $sheet->setCellValue("L$row", cleanNumber($Totals['ArrearAmount']));
        $sheet->getStyle("L$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);  

        $sheet->setCellValue("M$row", cleanNumber($Totals['ShipmentCharges']));
        $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);  

        $sheet->setCellValue("N$row", cleanNumber($Totals['GrossSalary']));
        $sheet->getStyle("N$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);  

        $sheet->setCellValue("O$row", cleanNumber($Totals['PF']));
        $sheet->getStyle("O$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);        

        $sheet->setCellValue("P$row", cleanNumber($Totals['ESI']));
        $sheet->getStyle("P$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("Q$row", cleanNumber($Totals['Advance']));
        $sheet->getStyle("Q$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("R$row", cleanNumber($Totals['LateFine']));
        $sheet->getStyle("R$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("S$row", cleanNumber($Totals['HelmetFine']));
        $sheet->getStyle("S$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("T$row", cleanNumber($Totals['MessCharges']));
        $sheet->getStyle("T$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("U$row", cleanNumber($Totals['Deductions']));
        $sheet->getStyle("U$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("V$row", cleanNumber(round($Totals['NetSalary'])));
        $sheet->getStyle("V$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        
        // $sheet->setCellValue("R$row", cleanNumber($Totals->TotalAmountt));
        // $sheet->getStyle("R$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);     
        
        // Set footer cells to bold
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $sheet->getStyle("F$row")->getFont()->setBold(true);
        $sheet->getStyle("H$row")->getFont()->setBold(true);
        $sheet->getStyle("I$row")->getFont()->setBold(true);
        $sheet->getStyle("K$row")->getFont()->setBold(true);
        $sheet->getStyle("L$row")->getFont()->setBold(true);
        $sheet->getStyle("M$row")->getFont()->setBold(true);
        $sheet->getStyle("N$row")->getFont()->setBold(true);
        $sheet->getStyle("P$row")->getFont()->setBold(true);
        $sheet->getStyle("O$row")->getFont()->setBold(true);
        $sheet->getStyle("Q$row")->getFont()->setBold(true);
        $sheet->getStyle("R$row")->getFont()->setBold(true);
        $sheet->getStyle("S$row")->getFont()->setBold(true);
        $sheet->getStyle("T$row")->getFont()->setBold(true);        
    }    

    // Output to browser
    // ob_clean();
    // flush();

    header("Content-type: application/vnd.ms-excel; charset=UTF-8");
    header('Content-Disposition: attachment; filename=Staff-Salary-Sheet-' . date("d-m-Y") . '.xlsx');

    $objWriter = new PHPExcel_Writer_Excel2007($spreadsheet); // ✅ use $spreadsheet
    $objWriter->save('php://output');
    exit;
} else if($ReportType == 2){
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

   $timestamp = date("YmdHis");
   $spreadsheet = new PHPExcel();
   $sheet = $spreadsheet->getActiveSheet();
   // Set Font Size
   $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
   // Set table heading bold text
   $sheet->getStyle('1')->getFont()->setBold(true);
   $sheet->getStyle('2')->getFont()->setBold(true);

// Row 1 - Main headers
$sheet->setCellValue('A1', 'S.No');
$sheet->setCellValue('B1', 'Bio ID');
$sheet->setCellValue('C1', 'NAME OF THE WORKER');
$sheet->setCellValue('D1', 'VILLAGE');
$sheet->setCellValue('E1', 'BATCH NAME');
$sheet->setCellValue('F1', 'SECTION');
$sheet->setCellValue('G1', 'WAGE FOR MONTH');
$sheet->setCellValue('H1', 'WAGE PER DAY');

// WORKING DAYS (I to L)
$sheet->setCellValue('I1', 'WORKING DAYS');
$sheet->mergeCells('I1:L1');

// Earned Wage Amount (M1)
$sheet->setCellValue('M1', 'Earned Wage Amount');
$sheet->mergeCells('M1:M2');

// Over Time Allowances (N to P)
$sheet->setCellValue('N1', 'Over Time Allowances');
$sheet->mergeCells('N1:P1');

// Travel Allowances (Q to R)
$sheet->setCellValue('Q1', 'Travel Allowances');
$sheet->mergeCells('Q1:R1');

$sheet->setCellValue('S1', 'Arear Amount');
$sheet->mergeCells('S1:S2');

$sheet->setCellValue('T1', 'Shipment charges (per day)');
$sheet->mergeCells('T1:T2');

$sheet->setCellValue('U1', 'Gross Wages');
$sheet->mergeCells('U1:U2');

// Deductions (V to AB)
$sheet->setCellValue('V1', 'Deductions');
$sheet->mergeCells('V1:AB1');

$sheet->setCellValue('AC1', 'Commission');
$sheet->mergeCells('AC1:AC2');

$sheet->setCellValue('AD1', 'Net Amount');
$sheet->mergeCells('AD1:AD2');

// Merge rowspan (2 rows)
foreach (['A','B','C','D','E','F','G','H','M','S','T','U','AC','AD'] as $col) {
    $sheet->mergeCells("{$col}1:{$col}2");
}

// Row 2 - Sub headers
$sheet->setCellValue('I2', 'No Of Worked Days');
$sheet->setCellValue('J2', 'Extra Days');
$sheet->setCellValue('K2', 'Add. Offs');
$sheet->setCellValue('L2', 'Total Days');

$sheet->setCellValue('N2', 'No Of OT Hours');
$sheet->setCellValue('O2', 'OT Per Hour');
$sheet->setCellValue('P2', 'Earned O.T Amount');

$sheet->setCellValue('Q2', 'Charges Per Day');
$sheet->setCellValue('R2', 'Travel Allowances');

$sheet->setCellValue('V2', 'PF & ESI');
$sheet->setCellValue('W2', 'Wage Deductions');
$sheet->setCellValue('X2', 'Bus Charge Deduction');
$sheet->setCellValue('Y2', 'Mess');
$sheet->setCellValue('Z2', 'Fine');
$sheet->setCellValue('AA2', 'Dress Code Deduction');
$sheet->setCellValue('AB2', 'Total Deductions');

// Style the full header
$headerRange = 'A1:AD2';
$styleArray = [
    'alignment' => [
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'wrap'       => true,
    ],
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => ['rgb' => 'D9EAD3'],
    ],
    'borders' => [
        'allborders' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN,
        ]
    ],
];

$sheet->getStyle($headerRange)->applyFromArray($styleArray);



   // Column Widths
   foreach (range('A', 'T') as $columnID) {
       if ($columnID === 'A') {
           $sheet->getColumnDimension($columnID)->setWidth(8);
       } else if ($columnID === 'B') {
           $sheet->getColumnDimension($columnID)->setWidth(10);
       } else if ($columnID === 'C') {
           $sheet->getColumnDimension($columnID)->setWidth(30);
       }else if ($columnID === 'D' || $columnID === 'E' || $columnID === 'F') {
           $sheet->getColumnDimension($columnID)->setWidth(20);
       }else if ($columnID === 'H' || $columnID === 'H') {
           $sheet->getColumnDimension($columnID)->setWidth(12);
       }else if ($columnID === 'S' || $columnID === 'T') {
           $sheet->getColumnDimension($columnID)->setWidth(15);
       }else if ($columnID === 'U' || $columnID === 'AD') {
            $sheet->getColumnDimension($columnID)->setWidth(20);
       }else {
           $sheet->getColumnDimension($columnID)->setAutoSize(true);
       }
   }
    // Define the columns involved in the merged cells
    $mergedColumns = [ 'U','V', 'W', 'X', 'AA', 'AB', 'AD'];

    // Set the width for each column
    foreach ($mergedColumns as $columnID) {
        $sheet->getColumnDimension($columnID)->setWidth(25);
    }

   // Align Specific Columns to Right
   $columnsToAlign = ['F','G','J', 'L' ,'M', 'N', 'O', 'P', 'Q', 'R','S','T'];
   foreach ($columnsToAlign as $col) {
       $sheet->getStyle($col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
   }

   // Fill Data
   $row = 3;
   $S_NO=1;
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
       'OThours'=>0,
       'OTAmount'=>0
   ];
   while($RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array()){
       $EmpID=$RowEmpCompltDtils['EmpID'];
       $EmpName=$RowEmpCompltDtils['EmpName'];
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
       $GrossWages=$EarnedWageAmt+$AreaAmount+$LoadingCharges+$TravelAllowances;
 
       $SqlOnDuty="SELECT sum(DaysTotal) as onduty from HrmOnDutyRegister where EmpID='$EmpID' and
        Month(StartDate)='$Month' and Year(StartDate)='$Year' ";
       $StmtOnDuty=$iconn->query($SqlOnDuty);
       $RowOnDuty = $StmtOnDuty->fetch_array();  
 
    //    $TotalCountedDays = 0; 
    //    if( $EarnedPayDays >= $TotalDays  ){
    //      $TotalCountedDays = $TotalDays;
    //      $GrossSalary = $TotalSalary;
    //    }else{
    //      $TotalCountedDays = $TotalDays - ($TotalDays - $EarnedPayDays);
    //      $GrossSalary = ($Payement/$TotalDays)*$TotalCountedDays;
    //    }
       $GrossSalary = ($Payement/$TotalDays)*$EarnedPayDays;
       $TotalDeductions=$PF+$ESI+$MessInDays+$LateLoginFine+$BusChargeDeductions+$DessCodeDeductions;
       $OtPerHour=30;
       $TravelAllowances = $AutoChargePerDay*$DaysWithoutWeekOffs;
       $GrossSalaryWithAllowances=$GrossSalary+$TravelAllowances+$ArearAmount+$ShipmentCharges+$OTAmount;
       $NetSalary= ($GrossSalaryWithAllowances+$Commission) - $TotalDeductions; 
       $sheet->setCellValue("A$row", $S_NO);
       $sheet->setCellValue("B$row", $BioMetricID);
       $sheet->setCellValue("C$row", $EmpName);
       $sheet->setCellValue("D$row", $VillageName);
       $sheet->setCellValue("E$row", $BatchName);
       $sheet->setCellValue("F$row", $SectionName);       

       $sheet->setCellValue("G$row", cleanNumber($TotalSalary));
       $sheet->getStyle("G$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("H$row", cleanNumber($PerDaySal));
       $sheet->getStyle("H$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("I$row", $DaysWithoutWeekOffs);
       $sheet->setCellValue("J$row", "");
       $sheet->setCellValue("K$row", $WeekOff);
       $sheet->setCellValue("L$row", $EarnedPayDays);


       $sheet->setCellValue("M$row", cleanNumber($GrossSalary));
       $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("N$row", cleanNumber($OThours));
       $sheet->getStyle("N$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
       $sheet->setCellValue("O$row", $OtPerHour);
       $sheet->setCellValue("P$row", cleanNumber($OTAmount));
       $sheet->getStyle("P$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
       
       $sheet->setCellValue("Q$row", cleanNumber($AutoChargePerDay));
       $sheet->getStyle("Q$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
       
       $sheet->setCellValue("R$row", cleanNumber($TravelAllowances));
       $sheet->getStyle("R$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
       
       $sheet->setCellValue("S$row", cleanNumber($ArearAmount));
       $sheet->getStyle("S$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("T$row", cleanNumber($ShipmentCharges));
       $sheet->getStyle("T$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("U$row", cleanNumber($GrossSalaryWithAllowances));
       $sheet->getStyle("U$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);       

       $sheet->setCellValue("V$row", cleanNumber($PF));
       $sheet->getStyle("V$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("W$row", cleanNumber($ESI));
       $sheet->getStyle("W$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("X$row", cleanNumber($BusChargeDeductions));
       $sheet->getStyle("X$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("Y$row", cleanNumber($MessInDays));
       $sheet->getStyle("Y$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("Z$row", cleanNumber($LateLoginFine));
       $sheet->getStyle("Z$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("AA$row", cleanNumber($DessCodeDeductions));
       $sheet->getStyle("AA$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("AB$row", cleanNumber($TotalDeductions));
       $sheet->getStyle("AB$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);  
       
       $sheet->setCellValue("AC$row", cleanNumber($Commission));
       $sheet->getStyle("AC$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);  

       $sheet->setCellValue("AD$row", cleanNumber(round($NetSalary)));
       $sheet->getStyle("AD$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);  

       $row++;
       $S_NO++;
       $Totals['EarnedWage']+=$EarnedWageAmt;
       $Totals['WorkingDays']+=$DaysWithoutWeekOffs;
       $Totals['WeekOffs']+=$WeekOff;
       $Totals['TotalDays']+=$EarnedPayDays;
       $Totals['TotalSalary']+=$TotalSalary;
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
       $Totals['OThours']+=$OThours;
       $Totals['OTAmount']+=$OTAmount;
       $Totals['ShipmentCharges']+=$ShipmentCharges;
       $Totals['Commission']+=$Commission;
   }


   // Footer Totals Row
   if ($Totals) {
       $sheet->setCellValue("A$row", "Grand Total:");
       $sheet->setCellValue("G$row", cleanNumber($Totals['TotalSalary']));
       $sheet->getStyle("G$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1); 
         
       $sheet->setCellValue("I$row", cleanNumber($Totals['WorkingDays']));
       $sheet->getStyle("I$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);     

       $sheet->setCellValue("K$row", cleanNumber($Totals['WeekOffs']));
       $sheet->getStyle("K$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1); 

       $sheet->setCellValue("L$row", cleanNumber($Totals['TotalDays']));
       $sheet->getStyle("L$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);     
       
       $sheet->setCellValue("M$row", cleanNumber($Totals['GrossSalary']));
       $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);   

       $sheet->setCellValue("N$row", cleanNumber($Totals['OThours']));
       $sheet->getStyle("N$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("P$row", cleanNumber($Totals['OTAmount']));
       $sheet->getStyle("P$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("R$row", cleanNumber($Totals['TravelAllowances']));
       $sheet->getStyle("R$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);  
       
       $sheet->setCellValue("S$row", cleanNumber($Totals['ArearAmount']));
       $sheet->getStyle("S$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);  

       $sheet->setCellValue("T$row", cleanNumber($Totals['ShipmentCharges']));
       $sheet->getStyle("T$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);  

       $sheet->setCellValue("U$row", cleanNumber($Totals['TotalGrossSalary']));
       $sheet->getStyle("U$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);  

       $sheet->setCellValue("V$row", cleanNumber($Totals['PF']));
       $sheet->getStyle("V$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("W$row", cleanNumber($Totals['ESI']));
       $sheet->getStyle("W$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("X$row", cleanNumber($Totals['BusCharges']));
       $sheet->getStyle("X$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("Y$row", cleanNumber($Totals['MessCharges']));
       $sheet->getStyle("Y$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("Z$row", cleanNumber($Totals['Fine']));
       $sheet->getStyle("Z$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("AA$row", cleanNumber($Totals['DressCodeFine']));
       $sheet->getStyle("AA$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("AB$row", cleanNumber($Totals['Deductions']));
       $sheet->getStyle("AB$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("AC$row", cleanNumber($Totals['Commission']));
       $sheet->getStyle("AC$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

       $sheet->setCellValue("AD$row", cleanNumber(round($Totals['NetSalary'])));
       $sheet->getStyle("AD$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
   
       
       // Set footer cells to bold
       $sheet->getStyle("A{$row}:AD{$row}")->getFont()->setBold(true); 
   }    

   // Output to browser
   // ob_clean();
   // flush();

   header("Content-type: application/vnd.ms-excel; charset=UTF-8");
   header('Content-Disposition: attachment; filename=MonMonthly-Worker-Salary-Sheet-' . date("d-m-Y") . '.xlsx');

   $objWriter = new PHPExcel_Writer_Excel2007($spreadsheet); // ✅ use $spreadsheet
   $objWriter->save('php://output');      

} else if($ReportType == 3){

    $SqlOnTimeEmpDetails="SELECT *,HED.EmpID,HED.BioMetricID as BioMetricID,HPD.PresentAddress,HWBM.BatchName,HWSM.SectionName,HWVM.VillageName,HMSD.TravelAllowances,
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
//echo $SqlOnTimeEmpDetails; exit;
    $StmtOnTimeEmpDetails=$iconn->query($SqlOnTimeEmpDetails);
    //$RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array();


   $timestamp = date("YmdHis");
   $spreadsheet = new PHPExcel();
   $sheet = $spreadsheet->getActiveSheet();
   // Set Font Size
   $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
   // Set table heading bold text
   $sheet->getStyle('1')->getFont()->setBold(true);
   $sheet->getStyle('2')->getFont()->setBold(true);

// Row 1 - Main headers
$sheet->setCellValue('A1', 'S.No');
$sheet->setCellValue('B1', 'Bio ID');
$sheet->setCellValue('C1', 'NAME OF THE WORKER');
$sheet->setCellValue('D1', 'VILLAGE');
$sheet->setCellValue('E1', 'BATCH NAME');
$sheet->setCellValue('F1', 'DESIGNATION');
$sheet->setCellValue('G1', 'WAGE PER DAY');
$sheet->setCellValue('H1', 'WORKING DAYS');
$sheet->setCellValue('I1', 'Earned Wage Amount');
$sheet->setCellValue('J1', 'Travel Allowances');
$sheet->mergeCells('J1:K1');
$sheet->setCellValue('L1', 'Sunday Incentive');
$sheet->setCellValue('M1', 'Gross Wages');
$sheet->setCellValue('N1', 'Deductions');
$sheet->mergeCells('N1:T1');
$sheet->setCellValue('U1', 'Commission');
$sheet->setCellValue('V1', 'Net Amount');

// Merge cells for single-column headers (rowspan = 2)
foreach (['A','B','C','D','E','F','G','H','I','L','M','U','V'] as $col) {
    $sheet->mergeCells("{$col}1:{$col}2");
}

// Row 2 - Sub headers
$sheet->setCellValue('J2', 'Charges Per Day');
$sheet->setCellValue('K2', 'Travel Allowances');

$sheet->setCellValue('N2', 'PF & ESI');
$sheet->setCellValue('O2', 'Wage Deduction');
$sheet->setCellValue('P2', 'Bus Charge Deduction');
$sheet->setCellValue('Q2', 'Mess');
$sheet->setCellValue('R2', 'Fine');
$sheet->setCellValue('S2', 'Dress Code Deduction');
$sheet->setCellValue('T2', 'Total Deductions');

// Style the full header
$headerRange = 'A1:V2';
$styleArray = [
    'alignment' => [
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'wrap'       => true,
    ],
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => ['rgb' => 'D9EAD3'],
    ],
    'borders' => [
        'allborders' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN,
        ]
    ],
];

$sheet->getStyle($headerRange)->applyFromArray($styleArray);



   // Column Widths
   foreach (range('A', 'V') as $columnID) {
       if ($columnID === 'A') {
           $sheet->getColumnDimension($columnID)->setWidth(8);
       } else if ($columnID === 'U' || $columnID === 'V') {
           $sheet->getColumnDimension($columnID)->setWidth(15);
       } else if ($columnID === 'C') {
           $sheet->getColumnDimension($columnID)->setWidth(30);
       }else if ($columnID === 'D' || $columnID === 'E' || $columnID === 'F') {
           $sheet->getColumnDimension($columnID)->setWidth(20);
       }else if ($columnID === 'G' || $columnID === 'H') {
           $sheet->getColumnDimension($columnID)->setWidth(12);
       }else if ($columnID === 'S' || $columnID === 'T') {
           $sheet->getColumnDimension($columnID)->setWidth(15);
       }else if ($columnID === 'U') {
            $sheet->getColumnDimension($columnID)->setWidth(20);
       }else if ($columnID === 'L' || $columnID === 'Q' || $columnID === 'R') {
            $sheet->getColumnDimension($columnID)->setWidth(15);
       }else {
           $sheet->getColumnDimension($columnID)->setAutoSize(true);
       }
   }
    // //Define the columns involved in the merged cells
    $mergedColumns = ['S','T'];

    // // Set the width for each column
    foreach ($mergedColumns as $columnID) {
        $sheet->getColumnDimension($columnID)->setWidth(25);
    }

   // Align Specific Columns to Right
   $columnsToAlign = ['G','J', 'L' ,'M', 'N', 'O', 'P', 'Q', 'R','S','T'];
   foreach ($columnsToAlign as $col) {
       $sheet->getStyle($col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
   }

   // Fill Data
    $row = 3;
    $S_NO=1;
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
      //echo "<pre>"  ; print_r($RowEmpCompltDtils); exit;
      $EmpName=$RowEmpCompltDtils['EmpName'];
      $EmpLocation=$RowEmpCompltDtils['VillageNamePresentAddress'];
      $VillageName=$RowEmpCompltDtils['VillageName'];
      $BatchName=$RowEmpCompltDtils['BatchName'];
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

        $sheet->setCellValue("A$row", $S_NO); // S_NO
        $sheet->setCellValue("B$row", $BioMetricID); // BioMetricID
        $sheet->setCellValue("C$row", $EmpName); // EmpName
        $sheet->setCellValue("D$row", $VillageName); // EmpLocation
        $sheet->setCellValue("E$row", $BatchName); // BatchName
        $sheet->setCellValue("F$row", $Designation); // Designation

        $sheet->setCellValue("G$row", cleanNumber($GrossSalary)); // GrossSalary
        $sheet->getStyle("G$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("H$row", $DaysWithoutWeekOffs); // DaysWithoutWeekOffs
        $sheet->setCellValue("I$row", cleanNumber($EarnedWageAmt)); // Earned Wage Amount
        $sheet->getStyle("I$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("J$row", cleanNumber($AutoChargePerDay)); // Auto Charge Per Day
        $sheet->getStyle("J$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("K$row", cleanNumber($TravelAllowances)); // Travel Allowance
        $sheet->getStyle("K$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("L$row", cleanNumber($SundayIncentive)); // Sunday Incentive
        $sheet->getStyle("L$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("M$row", cleanNumber($GrossSalaryWithAllowances)); // Gross Salary With Allowances
        $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("N$row", cleanNumber($PF)); // PF
        $sheet->getStyle("N$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("O$row", cleanNumber($ESI)); // ESI
        $sheet->getStyle("O$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("P$row", cleanNumber($BusChargeDeductions)); // Bus Charge
        $sheet->getStyle("P$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("Q$row", cleanNumber($MessInDays)); // Mess in Days
        $sheet->getStyle("Q$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("R$row", cleanNumber($LateLoginFine)); // Late Login Fine
        $sheet->getStyle("R$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("S$row", cleanNumber($DessCodeDeductions)); // Dress Code Deduction
        $sheet->getStyle("S$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("T$row", cleanNumber($TotalDeductions)); // Total Deductions
        $sheet->getStyle("T$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("U$row", cleanNumber($Commission)); // Commission
        $sheet->getStyle("U$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("V$row", cleanNumber(round($NetSalary))); // Net Salary
        $sheet->getStyle("V$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
  

      $row++;
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


    // Assume $row is already set to the row number where the totals should be placed
    if ($Totals) {
        $sheet->setCellValue("A{$row}", '');
        $sheet->setCellValue("B{$row}", 'Grand Total:');
        $sheet->setCellValue("C{$row}", '');
        $sheet->setCellValue("D{$row}", '');
        $sheet->setCellValue("E{$row}", '');
        $sheet->setCellValue("F{$row}", '');
        $sheet->setCellValue("G{$row}", '');

        $sheet->setCellValue("H{$row}", cleanNumber($Totals['WorkingDays']));
        $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("I{$row}", cleanNumber($Totals['EarnedWage']));
        $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("J{$row}", '');
        $sheet->setCellValue("K{$row}", cleanNumber($Totals['TravelAllowances']));
        $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("L{$row}", cleanNumber($Totals['SundayIncentive']));
        $sheet->getStyle("L{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("M{$row}", cleanNumber($Totals['TotalGrossSalary']));
        $sheet->getStyle("M{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("N{$row}", cleanNumber($Totals['PF']));
        $sheet->getStyle("N{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("O{$row}", cleanNumber($Totals['ESI']));
        $sheet->getStyle("O{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("P{$row}", cleanNumber($Totals['BusCharges']));
        $sheet->getStyle("P{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("Q{$row}", cleanNumber($Totals['MessCharges']));
        $sheet->getStyle("Q{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("R{$row}", cleanNumber($Totals['Fine']));
        $sheet->getStyle("R{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("S{$row}", cleanNumber($Totals['DressCodeFine']));
        $sheet->getStyle("S{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("T{$row}", cleanNumber($Totals['Deductions']));
        $sheet->getStyle("T{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("U{$row}", cleanNumber($Totals['Commission']));
        $sheet->getStyle("U{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue("V{$row}", cleanNumber(round($Totals['NetSalary'])));
        $sheet->getStyle("V{$row}")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        // Make the entire totals row bold
        $sheet->getStyle("A{$row}:V{$row}")->getFont()->setBold(true);
    }

   // Output to browser
   // ob_clean();
   // flush();


   $MonthSegmentData=getMonthSegments($Month,$Year,$MonthSegment);    
   $date1= new DateTime($MonthSegmentStart);
   $date2= new DateTime($MonthSegmentEnd);
   $DateText = ($MonthSegment == 1 ? 'First Half' : 'Second Half') . " ({$date1->format('Y M d')} - {$date2->format('d')})";

  // Convert to filename-friendly format: FirstHalf_YYYY-MMM-DD-DD or SecondHalf_YYYY-MMM-DD-DD
   $DateText = preg_replace('/^(First|Second) Half \((\d{4}) (\w{3}) (\d{2}) - (\d{2})\)$/', '${1}Half_${2}-${3}-${4}-${5}', $DateText);
   header("Content-type: application/vnd.ms-excel; charset=UTF-8");
   header('Content-Disposition: attachment; filename=15Days-Worker-Salary-Sheet-'. $DateText .'-'. date("H-i-s") . '.xlsx');

   $objWriter = new PHPExcel_Writer_Excel2007($spreadsheet); // ✅ use $spreadsheet
   $objWriter->save('php://output');
    
}
