<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include_once ROOT . 'config/api-call.php';
$imgData = base64_encode(file_get_contents(ROOT . "assetsv2/assets/images/qr/coastal_qr.png"));
$src = 'data: ' . mime_content_type(ROOT . "assetsv2/assets/images/qr/coastal_qr.png") . ';base64,' . $imgData;
$ClientID = $_SESSION["ClientID"];

$api = API_PLANT_CHECK_REPORT;

$bodyContent = [
    'ArrivalID' => trim($_GET['ArrivalID'])
];
$result = CallAPI($api, "GET", "", $bodyContent);

$PlantCheckReportData = $result;
$HarvestData = $result->HarvestDetails;
$PreprocessingDetails = $result->PreprocessingDetails;

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);

$TotalPackingTrays = 0;
$ClientName = $_SESSION['ClientName'];

class MYPDF extends TCPDF
{
    protected $ClientName;
    protected $clientID;
    protected $QRSrc;



    public function setClientDetails($name, $ClientID, $src)
    {
        $this->ClientName = $name;
        $this->clientID = $ClientID;
        $this->QRSrc = $src;
    }

    public function Header()
    {
        $this->SetFont('helvetica', 'B', 15);
     

        $this->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 5, $this->ClientName, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    }

    public function Footer()
    {
        $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $dt = $dateTime->format("d-m-Y  H:i:s");
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Dated ' . $dt . ', Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setClientDetails($ClientName, $ClientID, $src);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Plant Check Report');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(15);
$pdf->SetHeaderMargin(5);
$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT, 45);
$pdf->setPrintFooter(true,10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', 13);

$firstPageData = array_slice($HarvestData, 0, $recordsPerPage);

function prepareTable($PlantCheckReportData)
{
    $ReceivingData = $PlantCheckReportData->PreprocessingDetails->ReceivingData;
    $HarvestDetails = $PlantCheckReportData->HarvestDetails;
    $GradingData = $PlantCheckReportData->PreprocessingDetails->GradingData ;
    $PlantCheckData = $PlantCheckReportData->PreprocessingDetails->PlantCheckData;
    $CratesData = $PlantCheckReportData->PreprocessingDetails->CratesData;
    $SupervisorData = $PlantCheckReportData->PreprocessingDetails->harvestSupervisor;   
    $GradingDetails = $PlantCheckReportData->PreprocessingDetails->GradingData ->GradingDetails;
    // echo "<pre>"; print_r($ReceivingData); exit;
    $HoPlant = $ReceivingData[0] ->HoPlant;
    /* if(!empty($ReceivingData[0] ->BreakUp[0]->UnitName)){
        $HoPlant = $ReceivingData[0] ->BreakUp[0]->UnitName;
    } */

    $DeheadingSuperVisor = $ReceivingData[0]->DeheadingSuperVisor;
    /* if(!empty($ReceivingData[0] ->BreakUp[0]->SupervisorName)){
        $DeheadingSuperVisor = $ReceivingData[0] ->BreakUp[0]->SupervisorName;
    } */
    $BreakupHoPlant = $BreakupDeheadingSuperVisor = $PlantCheckSupervisor = '';
    if(!empty($ReceivingData)){
        foreach($ReceivingData as $RecvData){
            if(!empty($RecvData->BreakUp)){
                foreach($RecvData->BreakUp as $BreakUp){
                    if(empty($BreakupHoPlant) && !empty($BreakUp->UnitName)){
                        $BreakupHoPlant = $BreakUp->UnitName;
                    }
                    if(empty($BreakupDeheadingSuperVisor) && !empty($BreakUp->UserName)){
                        $BreakupDeheadingSuperVisor = $BreakUp->UserName;
                    }
                }
            }            
        }
        $HoPlant = !empty($BreakupHoPlant) ? $BreakupHoPlant : $HoPlant;
        $DeheadingSuperVisor = !empty($BreakupDeheadingSuperVisor) ? $BreakupDeheadingSuperVisor : $DeheadingSuperVisor;
    }

    $ReceivingSupervisor = $ReceivingData[0] ->ReceivingSupervisor;
    /* if(!empty($PlantCheckData[0]->PlantCheckSupervisorName)){
        $ReceivingSupervisor = $PlantCheckData[0]->PlantCheckSupervisorName;
    } */
    if(!empty($PlantCheckData)){
        foreach($PlantCheckData as $PlantChkData){
            if(empty($PlantCheckSupervisor) && !empty($PlantChkData->PlantCheckSupervisorName)){
                $PlantCheckSupervisor = $PlantChkData->PlantCheckSupervisorName;
            }
        }
        $ReceivingSupervisor = !empty($PlantCheckSupervisor) ? $PlantCheckSupervisor : $ReceivingSupervisor;
    }
    $content = '<table border="0" cellpadding="2" cellspacing="0" width="100%" style="float:right; nobr=true;">

   
   <tr>    
    <th><font size="12"><B>H/O In Plant : </B>'.$HoPlant.'    </font></th>
   </tr>
   <tr>
    <th><font size="12"><B>Grading In Plant : </B>'.$ReceivingData[0] ->GradingStationName.'    </font></th>  
    
   
    </tr>';

     $content .= '  <tr>    
    <th><font size="12"><B>Receving Sup : </B>'.$ReceivingSupervisor.'    </font></th>
   </tr>   
   <tr>
    <th><font size="12"><B>De-Heading Sup : </B>'.$DeheadingSuperVisor.'    </font></th>
    </tr>
   ';

    
//     if(!empty($ReceivingData)){

//         $content .= '  <tr>    
//     <th><font size="12"><B>Receving Sup : </B>'.$ReceivingData[0] ->ReceivingSupervisor.'    </font></th>
//    </tr>   
//    <tr>
//     <th><font size="12"><B>De-Heading Sup : </B>'.$ReceivingData[0]->DeheadingSuperVisor.'    </font></th>
//     </tr>
//    ';
//      $content .= ' <tr>
//         <td> <font size="12"><U><b>Plant Count: </b></U></font><br> <br>
//             <table border="1" cellpadding="4" cellspacing="0" width="100%" style="font-size: 10px;">
//                 <tr>
//                     <th><b>Weight</b></th>
//                     <th><b>No.of Pieces</b></th>
//                     <th><b>Count</b></th>
//                     <th><b>Body Yield</b></th>

//                 </tr>';

//                 foreach ($ReceivingData as $value) {
//                     $content .= '
//                     <tr>
//                         <td>' . $value ->ReceivedQuantity . '</td>
//                         <td>' . $value->ReceivedNoofPieces . '</td>
//                         <td>' . $value->ReceivedCount . '</td>
//                         <td>' . $value->BodyYield . '</td>

//                     </tr>';
//                 }
   
//     $content .= ' </table>
//       </td>
//    </tr>';
//             }
   
  
// if(!empty($PlantCheckData)){
//     $content .= ' <tr>
//         <td> <font size="12"><br><U><b>Plant Check Details : </b></U></font><br> <br>
//             <table border="1" cellpadding="4" cellspacing="0" width="100%" style="font-size: 10px;">
//                 <tr>
//                     <th><b>S.No</b></th>
//                     <th><b>Variety</b></th>
//                     <th><b>No.of Pieces</b></th>

//                 </tr>';

//                 // Loop through checklist data to fill table rows
//                 foreach ($PlantCheckData as $plantdata) {
//                     $content .= '
//                     <tr>
//                         <td>' . $plantdata ->SNo . '</td>
//                         <td>' . $plantdata->PlantCheckVariety . '</td>
//                         <td>' . $plantdata->PlantCheckNoOfPieces . '</td>

//                     </tr>';
//                 }
   
//     $content .= ' </table>
//       </td>
//    </tr>
//    ';
//    }


if (!empty($ReceivingData)) {

    $content .= '
    <tr>
        <td>
            <font size="12"><u><b> Plant Check Count :</b></u></font><br><br>

            <table border="1" cellpadding="4" cellspacing="0" width="100%" style="font-size:10px;">
                <tr>
                    <th><b>Weight</b></th>
                    <th><b>No.of Pieces</b></th>
                    <th><b>Count</b></th>
                    <th><b>Body Yield %</b></th>
                </tr>';

   $DeheadingBreakup = [];
    foreach ($ReceivingData as $value) {
        $content .= '
                <tr>
                    <td>' . $value->HonWeight . '</td>
                    <td>' . number_format($value->CountAfterDH, 0) . '</td>
                   <td>' . number_format($value->PlantCheckCnt, 2) . '</td>
                    <td>' . $value->BodyYield . '</td>
                </tr>';
        //$DeheadingBreakup[] = $value->BreakUp;
         if (!empty($value->BreakUp)) {
            $DeheadingBreakup = array_merge($DeheadingBreakup, $value->BreakUp);
        }
    }
   
    if (!empty($DeheadingBreakup)) {

        $content .= '
               
                <tr>
                    <th><b>S.No</b></th>
                    <th><b>Variety</b></th>
                   <th colspan="2"><b>No.of Pieces</b></th>
                    <th></th>
                </tr>';
        $BSno = 0;
        foreach ($DeheadingBreakup as $BreakUpItem) {
            $content .= '
                <tr>
                    <td>' . ++$BSno . '</td>
                    <td>' . $BreakUpItem->Name . '</td>
                   <td colspan="2">' . $BreakUpItem->NoOfPeaces . '</td>
                    <td></td>
                </tr>';
        }
    }

    $content .= '
            </table>
        </td>
    </tr>';
}


if (!empty($ReceivingData)) {


    $content .= '
    <tr>
        <td>
            <font size="12"><u><b>Purchase Check Count:</b></u></font><br><br>

            <table border="1" cellpadding="4" cellspacing="0" width="100%" style="font-size:10px;">
                <tr>
                    <th><b>Weight</b></th>
                    <th><b>No.of Pieces</b></th>
                    <th><b>Count</b></th>
                   
                </tr>';

   
    foreach ($ReceivingData as $value) {
        $ReceivedCount = 0;
        if(!empty($value->ReceivedNoofPieces) && !empty($value->ReceivedQuantity)){
            $ReceivedCount = ($value->ReceivedNoofPieces / $value->ReceivedQuantity);
        }
        
        $content .= '
                <tr>
                    <td>' . $value->ReceivedQuantity. '</td>
                     <td>' . number_format($value->ReceivedNoofPieces, 0) . '</td>
                   <!-- <td>' . number_format($value->ReceivedCount, 2) . '</td> -->
                   <td>' . number_format($ReceivedCount, 2) . '</td>
                    
                    
                </tr>';
    }

   
    if (!empty($PlantCheckData)) {

        $content .= '
               
                <tr>
                    <th><b>S.No</b></th>
                    <th><b>Variety</b></th>
                   <th colspan="2"><b>No.of Pieces</b></th>
                    <th></th>
                </tr>';

        foreach ($PlantCheckData as $plantdata) {
            $content .= '
                <tr>
                    <td>' . $plantdata->SNo . '</td>
                    <td>' . $plantdata->PlantCheckVariety . '</td>
                   <td colspan="2">' . $plantdata->PlantCheckNoOfPieces . '</td>
                    <td></td>
                </tr>';
        }
    }

    $content .= '
            </table>
        </td>
    </tr>';
}
  

   
   if(!empty($CratesData)){
    $content .= ' <!--<tr><td style="height: 120px;">&nbsp;</td></tr> -->
     <tr>
     <!--<td><font size="12"><br><U> <b>Crate Details : </b></U><b> Harvest Supervisor : ' . $SupervisorData->Supervisors .'</b></font><br> <br>-->
     <td><font size="12"><br><U> <b>Crate Details : </b></U><b><!--RM Recevied Supervisor : -->' . $ReceivingData[0] ->ReceivingSupervisor .'</b></font><br> <br>
            <table border="1" cellpadding="4" cellspacing="0" width="100%" style="font-size: 10px;">
                <tr>
                    <th><b>S.No</b></th>
                    <th><b>Crate No</b></th>
                    <th><b>Actual Crate Weight</b></th>
                    <th><b>Received Crate Weight</b></th>
                    <th><b> Weight Difference</b></th>
                    </tr>';

                // Loop through checklist data to fill table rows
                foreach ($CratesData as $cratesdata) {
                    $content .= '
                    <tr>
                        <td>' . $cratesdata ->SNo . '</td>
                        <td>' . $cratesdata->CrateNo . '</td>
                        <td>' . $cratesdata ->ActualCrateWeight . '</td>
                        <td>' . $cratesdata->ReceivedCrateWeight . '</td>
                       <!--<td style ="color:'.$cratesdata ->ColourCode.'">' . round($cratesdata->WeightDifference,3) . '</td> -->
                        <td style="color:' . $cratesdata->ColourCode . '">' . $cratesdata->WeightDifference . '</td>

                    </tr>';
                }
   
    $content .= ' </table>
      </td>
   </tr>
   ';  }

   if(isset($GradingDetails) && !empty($GradingDetails)){
   
    $content .= ' <tr>
        <font size="12"><br><u><b>Grading Details</b></u> <b><!-- Supervisor -->: '.$GradingData->GradingSupervisor.'</b></font><br><br>

            <table border="1" cellpadding="4" cellspacing="0" width="100%" style="font-size: 10px;">
                <tr>
                    <th><b>S.No</b></th>
                     <th><b>Grade</b></th>
                    <th><b>Count</b></th>
                    <th><b>Grading weight</b></th>
                    <th><b>Variety</b></th>
                    <th><b> Head on Count</b></th>
                </tr>';

                // Loop through checklist data to fill table rows
                //echo "<pre>"; print_r($GradingDetails); exit;
                $total_headon_cnt = 0; 
                $EachGradeHlWeight=0;
                $hd_cnt = 0;

                foreach ($GradingDetails as $gradingData) {
                    $total_headon_cnt = $total_headon_cnt + $gradingData ->GradingWeight;    
                    //$EachGradeHlWeight = $EachGradeHlWeight + $gradingData ->EachGradeHlWeight;
                    $EachGradeHlWeight =$gradingData ->FinalYield;
                    $hd_cnt = $hd_cnt +   $gradingData->HeadOnCount;  
                    $content .= '
                    <tr>
                        <td>' . $gradingData ->SNo . '</td>
                        <td>' . $gradingData->Grade . '</td>
                        <td>' . $gradingData->Count . '</td>
                        <td>' . $gradingData ->GradingWeight . '</td>
                        <td>' . $gradingData->Variety . '</td>
                        <td>' . $gradingData->HeadOnCount . '</td>

                    </tr>';
                }


    $content .= ' </table>
      </td>
   </tr>

   ';  
   
}

 

    $content .= '</table><br>';
    $yield = 0;$HOnQtyHtml = '';
    if($GradingData->TotalHonQty){
        $yield = round((($total_headon_cnt / $GradingData->TotalHonQty) * 100), 2);
        $HOnQtyHtml = '<span style="width:33%;float:left">Total HON Qty: '.$GradingData->TotalHonQty.'</span>';
    }
    $content .= '<div style="width:100%">
                    <font size="12">
                        '.$HOnQtyHtml.'
                        <span style="width:33%;float:left">Total HL Qty: '.$total_headon_cnt.'</span>
                        <span style="width:33%;float:right">Total Yield: <!-- '.$EachGradeHlWeight.'-->'.$yield.'</span>
                    </font>
                </div>
            ';

    // $content .= '<div style="width:100%; clear:both; margin-top:5px; font-size:12px;">
    //     <b>Mixed Batch Name:</b> '.(!empty($GradingData->BatchNumbers) ? $GradingData->BatchNumbers : '-').'
    // </div>';

             
if (!empty($GradingData->BatchNumbers)) {

    $batchArray = array_filter(array_map('trim', explode(',', $GradingData->BatchNumbers)));

    if (count($batchArray) > 1) {
        $batchStr = implode('<br>', $batchArray);

        $content .= '<div style="width:100%; clear:both; margin-top:5px; font-size:12px;">
                        <table width="100%">
                            <tr>
                                <td width="150"><b>Mixed Batch Name:</b></td>
                                <td>' . $batchStr . '</td>
                            </tr>
                        </table>
                    </div>';
    }
}


    return $content;
}

// if (!empty($GradingDetails)) {

//     $content .= '
//     <tr>
//         <td>
//             <font size="12"><br><u><b>Grading Details</b></u> - <b>Supervisor: '.$GradingData->GradingSupervisor.'</b></font><br><br>

//             <table border="1" cellpadding="4" cellspacing="0" width="100%" style="font-size:10px;">
//                 <tr>
//                     <th>S.No</th>
//                     <th>Grade</th>
//                     <th>Count</th>
//                     <th>Grading Weight</th>
//                     <th>Variety</th>
//                     <th>Head On Count</th>
//                 </tr>';

//                 // echo "<pre>"; print_r($GradingDetails); exit;
//                 $total_headon_cnt = 0; 
//                 $EachGradeHlWeight=0;
//                 $hd_cnt = 0;

//             foreach ($GradingDetails as $gradingData) {
//                     $total_headon_cnt = $total_headon_cnt + $gradingData ->GradingWeight;    
//                     //$EachGradeHlWeight = $EachGradeHlWeight + $gradingData ->EachGradeHlWeight;
//                     $EachGradeHlWeight =$gradingData ->FinalYield;
//                     $hd_cnt = $hd_cnt +   $gradingData->HeadOnCount;  

//         $content .= '
//         <tr>
//             <td>'.$gradingData->SNo.'</td>
//             <td>'.$gradingData->Grade.'</td>
//             <td>'.$gradingData->Count.'</td>
//             <td>'.$gradingData->GradingWeight.'</td>
//             <td>'.$gradingData->Variety.'</td>
//             <td>'.$gradingData->HeadOnCount.'</td>
//         </tr>';
//     }

//     $content .= '
//             </table>

//             <br>
//             <div style="width:100%"><font size="12"><span style="width:50%;float:left">Total HL Qty: '.$total_headon_cnt.' 
//                 </span><span style="width:50%;float:right">Total Yield: '.$EachGradeHlWeight.'</span></font></div>
//         </td>
//     </tr>';

// }

// if (!empty($CratesData)) {

//     $content .= '
//     <tr>
//         <td>
//             <font size="12">
//                 <br><u><b>Crate Details</b></u> - 
//                 <b>Harvest Supervisor: '.$SupervisorData->Supervisors.'</b>
//             </font><br><br>

//             <table border="1" cellpadding="4" cellspacing="0" width="100%" style="font-size:10px;">
//                 <tr>
//                     <th>S.No</th>
//                     <th>Crate No</th>
//                     <th>Actual Crate Weight</th>
//                     <th>Received Crate Weight</th>
//                     <th>Weight Difference</th>
//                 </tr>';

//     foreach ($CratesData as $cratesdata) {
//         $content .= '
//         <tr>
//             <td>'.$cratesdata->SNo.'</td>
//             <td>'.$cratesdata->CrateNo.'</td>
//             <td>'.$cratesdata->ActualCrateWeight.'</td>
//             <td>'.$cratesdata->ReceivedCrateWeight.'</td>
//             <td style="color:'.$cratesdata->ColourCode.'">'
//                 .round($cratesdata->WeightDifference,3).
//             '</td>
//         </tr>';
//     }

//     $content .= '
//             </table>
//         </td>
//     </tr>';
// }


  
//     return $content;
// }


$pdf->AddPage();
// $pdf->Cell(0, 20, '', 0, 1, 'C'); // Display client name
$pdf->setPrintHeader(false);
$pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 5, 'BATCH NO : '.$HarvestData->BatchNumber, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

$prepareTable=prepareTable($PlantCheckReportData);

$pageNo = 1;

$pdf->writeHTML($prepareTable);
$pdf->writeHTML($firstPageContent);

ob_end_clean();
$pdf->Output('Plant Check report.pdf', 'I');
