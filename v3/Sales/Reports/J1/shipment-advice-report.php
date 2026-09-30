<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include($cp . "Includes/IncludePage.php");
//include "PrintHeaderInc.php";
$ID = $_GET['id'];
$Type = $_GET['Type'];

$PoID = $_GET['id'];

class MYPDF extends TCPDF
{


    public function Header()
    {
        $this->SetFont('helvetica', 'B', 15);
        $this->Cell(0, 0, 'SHIPMENT ADVICE', 0, 1, 'C');
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(255, 0, 0);
        $this->setMargins(30, 30, 20);
    }

    public function Footer() // need to update address from API 
    {
        $this->SetFont('helvetica', 'B', 10);
        $this->SetY(-25);
        $footerText = 'JAGADEESH MARINE EXPORTS, 1-174, Komarada Road, Rayalam village, Bhimavaram:534 208, INDIA. <br>Phone:+91 8816 227124/227996, Fax: 227120/227122, Web:www.jmexports.com, E-mail :contact@jmexports.com';
        $this->writeHTML($footerText, true, false, true, false, 'C');
    }
}


$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('SHIPMENT ADVICE');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(8);
$pdf->SetHeaderMargin(8);
$pdf->SetMargins(10, 10, 10, 5);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();


$table_data = getDS_TABLE($iconn, $ID);
$pdf->writeHTML($table_data);
$pdf->SetFont('helvetica', '', 6);


$file_name = $ExporterData['InvShortCode'] . '-A.C-' . $CustomInvoiceRow['CInvNo'] . '-' . $CurrentYear . '-' . $NxtYear . '.pdf';

ob_end_clean();


$pdf->Output($file_name);

function getDS_TABLE($iconn, $ID)
{

    global $PoDataRow, $PoID;

    include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
    include($_SERVER['DOCUMENT_ROOT'] . "/includes/func.php");
    include "../SalesInculdes/SalesConstants.php";


    $content = '';


    $size = 45;
    $size1 = 55;
    $FontSize = 9;


    $Whitspace = '<b style="color:white"> yuhju</b>';

    $StyleTbl = 'Style="border: 1px solid #ccc;
  color: #37393b;
  margin: 10px 0 0 0;
  text-shadow: #fff 1px -1px 1px;
  text-align: left;
  width: 100%;"';

    $StyleTd = 'Style=" margin: 0;
  padding: 0;
  border: 0;
  font-size: 100%;
  vertical-align: baseline;
  background: transparent;"';
    $w1 = '30%';
    $w2 = '70%';

    $content .= '
    <table border="0" cellspacing="0" cellpadding="0">  
				
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">NAME OF THE EXPORTER </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">:  ' . $ExporterName . '</font></th>				
           </tr>
		   
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">NAME OF THE BUYER </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $ExporterIeCode . '</font></th>				
           </tr> 
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">INVOICE NO. & DATE </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"><b>: ' . $ExpBranchCode . '</b></font></th>				
           </tr>

		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">CONTRACT NO. & DATE </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"><b>: ' . $ExpPanNumber . '</b></font></th>				
           </tr> 
           <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">NAME OF THE COMMODITY </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '"><b>: ' . $ExporterGstNo . '</b></font></th>				
           </tr>
		   
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">INVOICE VALUE </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $ProcessorName . '</font><br><font size="7">' . $ProcessorAddress . '</font></th>				
           </tr>
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">CONTAINER NO </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $SciName . '</font></th>				
           </tr>
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">SHIPPING LINE </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $PackStyle . '</font></th>				
           </tr> 
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">BL. NO </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $SciName . '</font></th>				
           </tr>
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">NAME OF THE VESSEL </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $PackStyle . '</font></th>				
           </tr> 
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">E T D </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $PackStyle . '</font></th>				
           </tr> 
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">E T A </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $PackStyle . '</font></th>				
           </tr> 
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">NAME OF THE VESSEL </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $PackStyle . '</font></th>				
           </tr> 
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">E T D </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $PackStyle . '</font></th>				
           </tr> 
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">E T A </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $PackStyle . '</font></th>				
           </tr> 
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">NAME OF THE LOADING PORT </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $PackStyle . '</font></th>				
           </tr> 
		   <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '"NAME OF THE DESTINATION PORT </font></th>
                <th width="' . $w2 . '" align="left"><font size="' . $FontSize . '">: ' . $PackStyle . '</font></th>				
           </tr> 
		  
		    <tr>  
				<th width="' . $w1 . '" align="left"><font size="' . $FontSize . '">DETAILS OF BREAK UP<br></font></th>
           </tr>   
		   
	</table>
				</th>
				
				<th width="100%"><br>
                    <table border="1" cellspacing="0" cellpadding="1">
                        <tr > 
                            <th  align="CENTER" ><font size="7"><b> VARIETY </b></font></th>
                            <th  align="CENTER" ><font size="7"><b> PACKING </b></font></th>
                            <th  align="CENTER" ><font size="7"><b> GRADE </b></font></th>
                            <th  align="CENTER" ><font size="7"><b> NO. OF CARTONS </b></font></th>
                        </tr>
                        <tr> 
                            <th  align="CENTER" ><font size="' . $FontSize . '">' . $ContainerNumber . '</font></th>
                            <th  align="CENTER" ><font size="' . $FontSize . '">40 FCL</font></th>
                            <th  align="CENTER" ><font size="' . $FontSize . '">' . $SealNo . '</font></th>
                            <th  align="CENTER" ><font size="' . $FontSize . '">' . $TOTAL_MCS . '  CARTONS OF FROZEN SHRIMPS</font></th>
                        </tr>
                        <tr> 
                            <th  align="CENTER" ><font size="7"></font></th>
                            <th  align="CENTER" ><font size="7"></font></th>
                            <th  align="CENTER" ><font size="' . $FontSize . '">' . $PackSealNo . '</font></th>
                            <th  align="CENTER" ><font size="7"></font></th>
                        </tr>';
    $content .= '</table>
				</th>
				</tr>';
    $content .= '</table>';
    return $content;
}
