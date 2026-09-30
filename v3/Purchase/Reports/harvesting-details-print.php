<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include_once ROOT . 'config/api-call.php';



$apiHarvestData = API_HARVESTING_DETAILS;

$api = API_HARVESTING_DETAILS;

$bodyContent = [
    'HarvestingID' => trim($_GET['HarvestingID'])
];
$result = CallAPI($api, "GET", "", $bodyContent);
$HarvestData = $result->Data;
$BreakUpData = $result->Data[0]->BreakUpData;

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$hasHarvestDate = $ArrayClientConfigData['hasHarvestDate'];


$TotalPackingTrays = 0;
$ClientName = $_SESSION['ClientName'];

class MYPDF extends TCPDF
{
    protected $ClientName;



    public function setClientName($name)
    {
        $this->ClientName = $name;
    }

    public function Header()
    {
        $this->SetFont('helvetica', 'B', 15);
        $this->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 5, $this->ClientName, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        // $this->Cell(0, 5, 'WEIGHTMENT SHEET', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
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

$pdf->setClientName($ClientName);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Weighment Sheet : ');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(15);
$pdf->SetHeaderMargin(5);
$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT, 45);
// $pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->SetAutoPageBreak(TRUE, 40);
$pdf->SetFont('helvetica', 13);



function basicData($HarvestData, $hasHarvestDate)
{

    $headerLabels = array();
    $headerLabels[] = [
        "name" => "BookingNumber",
        "displayName" => "Booking Number",
    ];
    if ($hasHarvestDate != 1) {
        $headerLabels[] = [
            "name" => "CheckingCount",
            "displayName" => "Count",
        ];
    } else {
        $headerLabels[] = [
            "name" => "",
            "displayName" => "",
        ];
    }

    $headerLabels[] = [
        "name" => "BookingDate",
        "displayName" => "Booking Date",
        "type" => "DATE"

    ];
    $headerLabels[] = [
        "name" => "FarmerName",
        "displayName" => "Farmer Name",
    ];
    $headerLabels[] = [
        "name" => "FarmAddress",
        "displayName" => "Village",
    ];

    if ($hasHarvestDate == 1) {

        $headerLabels[] = [
            "name" => "HarvestDate",
            "displayName" => "Harvest Date",
            "type" => "DATE"
        ];
        $headerLabels[] = [
            "name" => "CheckingQty",
            "displayName" => "Count Net Weight",
            "suffix" => "KGs"
        ];
        $headerLabels[] = [
            "name" => "NumberOfPieces",
            "displayName" => "Number Of Pieces",

        ];
        $headerLabels[] = [
            "name" => "CheckingCount",
            "displayName" => "Count",
        ];

        $headerLabels[] = [
            "name" => "DefectRemarks",
            "displayName" => "Defect Remarks",
        ];
        
    } else {
        $headerLabels[] = [
            "name" => "",
            "displayName" => "",
        ];
    }
    return prepareTable($HarvestData, $headerLabels, false);
}

$firstPageData = array_slice($BreakUpData, 0, $recordsPerPage);
$secondPageData = array_slice($BreakUpData, $recordsPerPage);

function generateTableContent($data, $snoStart, $HarvestData, $recordsPerColumn)
{

    $content = '';
    $content .= '<table border="1" cellspacing="0" cellpadding="1" style="float:right; nobr=true;">';


    $totalRecords = count($data);
    $columns = ceil($totalRecords / $recordsPerColumn);

    $headers = [];
    for ($i = 0; $i < $columns; $i++) {
        $headers[] = 'SNO';
        $headers[] = 'KGs';
        $headers[] = 'Net';
    }

    $content .= '<tr>';
    foreach ($headers as $header) {
        $content .= '<th align="center" style="width: 40px"><font size="9"><b>' . $header . '</b></font></th>';
    }
    $content .= '</tr>';

    $i = 0;
    for ($rowIndex = 0; $rowIndex < $recordsPerColumn; $rowIndex++) {
        $content .= '<tr>';
        for ($colIndex = 0; $colIndex < $columns; $colIndex++) {
            $recordIndex = $rowIndex + $colIndex * $recordsPerColumn;
            if ($recordIndex < $totalRecords) {
                $Weight = $data[$recordIndex]->Weight;
                $NoofCrates = $data[$recordIndex]->NoofCrates;
                $Sno = $recordIndex + $snoStart;
                $content .= '<td align="center" style="width: 40px"><font size="9">' . $Sno . '</font></td>
                            <td align="center" style="width: 40px"><font size="9">' . $Weight . '</font></td>
                            <td align="center" style="width: 40px"><font size="9">' . $NoofCrates . '</font></td>';
            } elseif ($recordIndex == $totalRecords && $HarvestData[0]->CheckingCrate > 0) {
                $Sno = $recordIndex + $snoStart;
                $content .= '<td align="center" style="width: 40px"><font size="9">' . $Sno . '</font></td>
                <td align="center" style="width: 40px"><font size="9">' . $HarvestData[0]->CheckingQty . '</font></td>
                <td align="center" style="width: 40px"><font size="9">' . $HarvestData[0]->CheckingCrate . '</font></td>';
            } else {
                $content .= '<td></td><td></td><td></td>';
            }
        }
        $content .= '</tr>';
    }

    $content .= '</table>';
    $content .= '<br>';


    return $content;
}

function countTheTrays($BreakUpData)
{
    $totalTraysCount = 0;
    for ($rowIndex = 0; $rowIndex < count($BreakUpData); $rowIndex++) {
        $NoofCrates = intval($BreakUpData[$rowIndex]->NoofCrates);
        $totalTraysCount = $totalTraysCount + $NoofCrates;
    }

    return $totalTraysCount;
}

function prepareTable($HarvestData, $HeaderJson, $footer)
{
    $content = '';
    $content .= '<table border="0" cellpadding="3" id="basicData" cellspacing="0">';
    $tdCount = 0;

    foreach ($HeaderJson as $label) {

        if ($label['name'] === 'DefectRemarks') {

            if ($tdCount > 0) {
                while ($tdCount < 3) {
                    $content .= '<td></td>';
                    $tdCount++;
                }
                $content .= '</tr>';
                $tdCount = 0;
            }

            $remarks = !empty($HarvestData[0]->DefectRemarks)
                ? $HarvestData[0]->DefectRemarks
                : '-';

            $content .= '
            <tr style="line-height:11px;">
                 <td align="left" colspan="3" ><font size="9"><b>Defect Remarks</b> : ' . $remarks . '</font>
                </td>
            </tr>';

            continue;
        }

        if ($tdCount == 0) {
            $content .= '<tr style="line-height:11px;">';
        }

        $value = isset($label['value']) && !empty($label['value'])
            ? $label['value']
            : (isset($HarvestData[0]) && property_exists($HarvestData[0], $label['name'])
                ? $HarvestData[0]->{$label['name']}
                : '');

    //     // $value = !empty($label['name']) ? $HarvestData[0]->{$label['name']} : '';
    //     if (isset($label['type']) && $label['type'] == 'DATE') {
    //         $value = date('d-m-Y', strtotime($value));
    //     }
    //     $value = !empty($label['suffix']) ? $value . ' ' . $label['suffix'] : $value;
    //     if (!empty($label['name'])) {
    //         $content .= '<td align="left"><font size="9"><b>' . $label['displayName'] . '</b>: ' . $value . '</font></td>';
    //     } else {
    //         $content .= '<td align="left"><font size="9"> </font></td>';
    //     }
        
  

       
        if (isset($label['type']) && $label['type'] === 'DATE' && !empty($value)) {
            $value = date('d-m-Y', strtotime($value));
        }
        if (!empty($label['suffix'])) { $value .= ' ' . $label['suffix']; }

        if (!empty($label['name'])) {
            $content .= '<td align="left"><font size="9"><b>' . $label['displayName'] . '</b>: ' . $value . '</font> </td>';
        } else {
            $content .= '<td align="left"><font size="9"> </font></td>';
        }

        // Check if 'type' is set, as it's optional
        $tdCount++;
        if ($tdCount == 3) {
            $content .= '</tr>';
            $tdCount = 0;
        }
    }


    if ($footer) {
        $content .= '<tr style="line-height:11px;">
        <td align="left" colspan="2" ><font size="9" ><b>Remarks</b> : ' . $HarvestData[0]->Remarks . '</font></td>
    </tr>';
    }
    $content .= '</table>';

    return $content;
}





function calculationData($HarvestData, $TotalTraysCount, $hasHarvestDate)
{

    $CheckingCrate = 0;

    if ($hasHarvestDate == 1 && $HarvestData[0]->CheckingCrate > 0) {
        $CheckingCrate = intval($HarvestData[0]->CheckingCrate);
    }
    $TotalTraysCount = intval($TotalTraysCount);

    $footerLabels = [
        [
            "name" => "MaterialTotalQuantity",
            "displayName" => "(+) NW Total Material Qty",
            "suffix" => "KGs"
        ],
        [
            "name" => "WithOutNetQty",
            "displayName" => "(-) NW Total Material Qty",
            "suffix" => "KGs"
        ],
        [
            "name" => "MaterialTotalCrates",
            "displayName" => "Total Crate Net Weight",
            "suffix" => "KGs"
        ],
        [
            "name" => "PackingTrays",
            "displayName" => "Packing Trays",
            "value" => $TotalTraysCount + $CheckingCrate,
            "suffix" => "Box"
        ],
        [
            "name" => "VehicalNumber",
            "displayName" => "Vehical Number",
        ],
        [
            "name" => "SealNumber",
            "displayName" => "Seal Number",
        ],
        [
            "name" => "PhoneNumber",
            "displayName" => "Farmer Mobile No",

        ],
        [
            "name" => "GroupName",
            "displayName" => "Boys Group",
            "value" => $HarvestData[0]->GroupName . '(' . $HarvestData[0]->WorkingBoys . ')'
        ],
        [
            "name" => "DriverName",
            "displayName" => "Driver Name",
        ],
        [
            "name" => "GraderName",
            "displayName" => "Grader Name",
        ],
        [
            "name" => "MTCrates",
            "displayName" => "Empty Crates",
        ],
    ];

    $footerLabels[] = [
        "name" => "Nets",
        "displayName" => "Nets",
    ];
    $footerLabels[] = [
        "name" => "Savels",
        "displayName" => "Savels",
    ];
    $footerLabels[] = [
        "name" => "WeightStones",
        "displayName" => "Weight Stones",
    ];
    $footerLabels[] = [
        "name" => "WeightMc",
        "displayName" => "Weight M/C",
    ];
    $footerLabels[] = [
        "name" => "WaterTin",
        "displayName" => "Water Tin",
    ];

    $footerLabels[] = [
        "name" => "GraderName",
        "displayName" => "Supervisor",
    ];


    return prepareTable($HarvestData, $footerLabels, true);
}

$pdf->AddPage();
$pdf->setPrintHeader(true);
$pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 5, 'WEIGHMENT SHEET', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

$basicData = basicData($HarvestData, $hasHarvestDate);

$totalBreakUpRecords = count($BreakUpData);
$recordsPerPage = 0;
$recordsPerColumn = 0;
if ($totalBreakUpRecords < 80) {
    $recordsPerPage = 80;
    $recordsPerColumn = 20;
} elseif ($totalBreakUpRecords < 200) {
    $recordsPerPage = 100;
    $recordsPerColumn = 25;
} else {
    $recordsPerPage = 100;
    $recordsPerColumn = 25;
}

$firstPageData = array_slice($BreakUpData, 0, $recordsPerPage);
$secondPageData = array_slice($BreakUpData, $recordsPerPage);

$pageNo = 1;
$firstPageContent = generateTableContent($firstPageData, $pageNo, $HarvestData, $recordsPerColumn);
$pdf->writeHTML($basicData);
$pdf->writeHTML($firstPageContent);
$pdf->setPrintHeader(false);

if (count($secondPageData) > 0) {
    $pdf->AddPage();
    $pdf->setPrintHeader(false);
    $pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    $pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    $pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    $totalBreakUpRecords = count($secondPageData);
    if ($totalBreakUpRecords < 80) {
        $recordsPerPage = 80;
        $recordsPerColumn = 20;
    }elseif ($totalBreakUpRecords < 200) {//newly added
        $recordsPerPage = 100;
        $recordsPerColumn = 25;
    } else {
        $recordsPerPage = 100;
        $recordsPerColumn = 25;
    } // newly added
    //$secondPageContent = generateTableContent($secondPageData, 101, $HarvestData, $recordsPerColumn*2);
    $secondPageContent = generateTableContent($secondPageData, 101, $HarvestData, $recordsPerColumn);
    $pdf->writeHTML($secondPageContent);
}


$calculationData = calculationData($HarvestData, countTheTrays($BreakUpData), $hasHarvestDate);
$pdf->writeHTML($calculationData);

$pdf->Output('Weightment Sheet.pdf', 'I');
