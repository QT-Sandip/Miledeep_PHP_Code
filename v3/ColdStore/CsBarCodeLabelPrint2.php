<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
require_once($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
require_once($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf_barcodes_2d.php");
$ProductionPlant = $_SESSION['UnitID'];


$BID=$_GET['BID'];
$StockID=$_GET['StockID'];

$SqlPID="SELECT *,CPID.FreezingType,CPID.Grade,CPS.PackingStyle as PackingStyle, COALESCE(LotNo,'NA')AS LotNo  FROM `CsStockData` as CPID 
   LEFT JOIN AdminPackingStyles as CPS on CPS.PCKID=CPID.PackingStyleID 
     left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=CPID.VarietyID
      left join ScrmPoData as SPD on SPD.PoID= CPID.PoID
     left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
   where CPID.BarCodeID='$BID' and CPID.StockID='$StockID'";
  $StmtPID=$iconn->query($SqlPID);

  $SqlBarcode="SELECT BarCodeValue,RackName,BarCodeDate,FloorName,RackName,BlockName 
from CsBarCodes as CB 
LEFT JOIN CsStockData as CSD on CB.BarCodeID=CSD.BarCodeID
LEFT JOIN CsStoresRackData as CRD on CSD.StoreRackID=CRD.RowID
LEFT JOIN CsStoresBlockData as CSBLK on CRD.BlockID=CSBLK.BlockID 
LEFT JOIN CsStoresFloorData as CSFD on CSFD.FloorID=CRD.FloorID WHERE CB.BarCodeID='$BID' and CSD.StockID='$StockID'";  
  $StmtBarCode=$iconn->query($SqlBarcode);
  $RowBarCode=$StmtBarCode->fetch_array();  

  $sqlPlant ="SELECT * FROM RmProcessingStations WHERE ProcessingStationID=$ProductionPlant";
  $stmtPlant=$iconn->query($sqlPlant);
  $RowstmtPlant=$stmtPlant->fetch_array();
  $StationName = $RowstmtPlant['StationName'];

  class MYPDF extends TCPDF {
       
    public function Header() {
        $image_file = 'http://mourya.silverlights.in/qc/images/ma_logo.jpg';
        $this->Image($image_file, 5, 0, 15, 15, 'JPG', '', 'T', false, 25, '', false, false, 0, false, false, false);
    $this->Cell(0 ,15,'Mourya Aquex Pvt Ltd',0,0,'C');
    }

    public function Footer() {
     $this->SetY(-10);
        $this->SetFont('helvetica', 'I', 8);
  $this->writeHTML("<hr>", true, false, false, false, '');
  $this->Cell(0 ,5,'Powered by SilverLights Software Solutions',0,1,'C');
   }
}

$widt=10;
$height=15;
$pageLayout = array($width, $height);
$pdf = new MYPDF('L', 'mm', array('150','150'), true, 'UTF-8', false);
  
    $pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Stock Card ');  
    $pdf->SetHeaderData('', '', '', '');  
  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(5); 
  $pdf->SetHeaderMargin(5); 
    $pdf->SetMargins(10, 20, 10, 10);  
    $pdf->setPrintHeader(false);  
    $pdf->setPrintFooter(false);  
  
    $pdf->SetAutoPageBreak(TRUE, 10);  

$pdf->AddPage('L');  
// ---------- data ----------
$DOB_TM_STMP  = strtotime($RowBarCode['BarCodeDate']);
$DATE_TIME    = date("d-m-Y", $DOB_TM_STMP);
$BAR_CODE_VAL = $RowBarCode['BarCodeValue'];

$hdrBg  = 'background-color:#484E52; color:#ffffff;';
$bodyBg = 'background-color:#f0f0f0;';

// ---------- RESPONSIVE HTML - no fixed widths ----------

$innerContent = '
<table cellpadding="0" cellspacing="0" width="95%" align="center">
<tr>
    <td>
      <table border="0" cellpadding="5" cellspacing="0" width="100%">
        <tr>
          <td width="100%" align="left">           
            '.$BARCODE_IMG.'
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr><td height="8"></td></tr>
  <tr><td height="8"></td></tr>
  <tr><td height="4" width="100%"><hr></td></tr>
  <!-- BARCODE + DETAILS -->
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%">
        <tr >
          <td align="left" colspan=2><font size="10"><b>BAR CODE NO</b></font></td>
          <td align="left" colspan=2><font size="10"><b>'.$BAR_CODE_VAL.'</b></font></td>
        </tr>

        <tr >
          <td align="left" colspan=2><font size="10"><b>DATE</b></font></td>
          <td align="left" colspan=2><font size="10">'.$DATE_TIME.'</font></td>
        </tr>

        <tr >
          <td align="left" colspan=2><font size="10"><b>STORE</b></font></td>
          <td align="left" colspan=2><font size="10">'.$RowBarCode['BlockName'].'</font></td>
        </tr>

        <tr >
          <td align="left" colspan=2><font size="10"><b>FLOOR</b></font></td>
          <td align="left" colspan=2><font size="10">'.$RowBarCode['FloorName'].'</font></td>
        </tr>

        <tr >
          <td align="left" colspan=2><font size="10"><b>RACK NUMBER</b></font></td>
          <td align="left" colspan=2><font size="10">'.trim($RowBarCode['RackName']).'</font></td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- SPACER -->
  <tr><td height="4"></td></tr>

  <!-- PRODUCT DETAILS TABLE -->
  <tr>
    <td>
      <table border="1" cellpadding="4" cellspacing="0" width="100%">
        <tr >
          <td width="25%" align="center"><font size="9"><b>TYPE</b></font></td>
          <td width="25%" align="center"><font size="9"><b>SIZE / CODE</b></font></td>
          <td width="25%" align="center"><font size="9"><b>PACKING</b></font></td>
          <td width="25%" align="center"><font size="9"><b>EXPIRY</b></font></td>
        </tr>';


while ($RowPID = $StmtPID->fetch_array()) {

  $innerContent .= '
    <tr>
      <td align="center">
        <font size="8">'.$RowPID['ShortName'].'<br>('.$RowPID['PackingTypeID'].')</font>
      </td>

      <td align="center">
        <font size="8">'.$RowPID['Grade'].'<br>('.$RowPID['FreezingType'].')</font>
      </td>

      <td align="center">
        <font size="8">'.$RowPID['PackingStyle'].'<br>('.$RowPID['AvalMcs'].' MCS)</font>
      </td>

      <td align="center">
        <font size="8">'.$RowPID['LotNo'].'</font>
      </td>
    </tr>';
}


$innerContent .= '
      </table>
    </td>
  </tr>

  <!-- SPACER -->
  <tr><td height="4"></td></tr>
  <tr><td height="4"><hr></td></tr>

  <!-- COMPANY + QR -->
  <tr>
    <td>
      <table border="0" cellpadding="5" cellspacing="0" width="100%">
        <tr>
          <td width="70%" align="left">
            <font size="12"><b>'.$_SESSION['ClientName'].'</b></font><br>
            <font size="11">
              Branch ' . $StationName . '
            </font>
          </td>
          <td width="30%" align="center" valign="middle">
            '.$QR_IMG.'
          </td>
        </tr>
      </table>
    </td>
  </tr>

</table>';

//echo $innerContent; exit;
// ---------- PARENT BOX ----------
$parentX = 8;    // left position
$parentY = 10;    // top position
$parentW = 130;   // safe width for 150mm page
$pad = 2;

// Write content with fitwidth to prevent overflow
$pdf->SetXY($parentX + $pad, $parentY + $pad);
$startY = $pdf->GetY();
$pdf->Ln(5); // adds 10mm space from top or previous content
$pdf->writeHTML($innerContent, true, false, true, false, '');  // fitwidth=true

$endY = $pdf->GetY();
$parentH = ($endY + $pad) - $parentY;

// // Rounded border
$pdf->SetLineWidth(0.7);
$pdf->SetDrawColor(0, 0, 0);
$pdf->RoundedRect($parentX, $parentY, $parentW, $parentH, 3.5, '1111', 'D');

// ---------- QR CODE ----------
$qrStyle = array(
    'border' => false,
    'padding' => 0,
    'hpadding' => 0,
    'vpadding' => 0,
    'fgcolor' => array(0,0,0),
    'bgcolor' => false
);

$qrX = $parentX + $pad + 95;     // QR cell
$qrY = $parentY + $pad + 103;     // top aligned
$qrSize = 24;
$pdf->write2DBarcode($BAR_CODE_VAL, 'QRCODE,L', $qrX, $qrY, $qrSize, $qrSize, $qrStyle, 'N');

$style = array(
    'position' => '',
    'align' => 'C',
    'stretch' => false,
    'fitwidth' => true,
    'cellfitalign' => '',
    'border' => false,
    'hpadding' => 'auto',
    'vpadding' => 'auto',
    'fgcolor' => array(0,0,0),
    'bgcolor' => false,
    'text' => true,   // show numbers under barcode
    'font' => 'helvetica',
    'fontsize' => 8,
    'stretchtext' => 4
);

    $x = 10;
    $y = 10;
    $w = 130;
    $h = 28;

 
 $pdf->write1DBarcode(
    $BAR_CODE_VAL,
    'C128',
    $x,
    $y,
    $w,
    $h,
    0.9,
    $style,
    'N' // N = Normal (horizontal)
);


ob_clean();
$pdf->Output('StockCard.pdf', 'I');

?>
