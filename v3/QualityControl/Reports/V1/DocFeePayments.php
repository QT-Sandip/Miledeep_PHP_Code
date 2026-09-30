<?php

session_start();
$IsHeader=0;
$IsFooter=0;
$QueryParms=$_GET['Q'];  

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");


$PoID=$_GET['PoID'];
 $ReportName=$_GET['ReportName']; 
$TYPE=$_GET['TypeProduct']; 
$FileType=$_GET['Type'];



$sql_inv_data="SELECT * FROM `$TblScrmPoData` as pd LEFT JOIN $TblScrmConsigneeDetails as scd on scd.ConID=pd.ConsigneeID
left JOIN $TblQcCoveringLetter as cvl   on  cvl.InvID=pd.PoID  LEFT JOIN  QcPayments as pay on pay.InvID=pd.PoID  LEFT JOIN QcCoveringLetter as cv on cv.InvID=pd.PoID
 left JOIN AdminExporterDetails  as expde on expde.ExpID=pd.ExporterID
 WHERE `PoID`='$PoID'"; 

			$stmt_inv_data=$iconn->query($sql_inv_data);

			$RowPo = $stmt_inv_data->fetch_array();
	
			

	

class MYPDF extends TCPDF {
	

 public function Footer() {

		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);

		$this->SetY(-55);

   }

    public function Header() {	

	 

        $image_file = K_PATH_IMAGES.'jme_logo.jpg';

        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);

        $this->SetFont('helvetica', 'B', 15);

		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		//$this->Cell(0, 5, 'Summary of Fees and Online Payment details', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		//$this->Cell(0, 5, 'Raw Material Register', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

		

    }

   

}
    $pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
    

	$pdf->SetCreator(PDF_CREATOR);  

    $pdf->SetTitle('Fee Payments Reciept: '.$row['PO_NUM'].' - '.$row['PO_DATE']);  

    $pdf->SetHeaderData('', '', '', '');  

    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  

    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  

    $pdf->SetDefaultMonospacedFont('helvetica');  

    $pdf->SetFooterMargin(45); 

	$pdf->SetHeaderMargin(15);	

    $pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT,45);  

    $pdf->setPrintHeader(false);  

    $pdf->setPrintFooter(true);  

    $pdf->SetAutoPageBreak(TRUE, 40);  

    $pdf->SetFont('helvetica', '', 11);  

    $pdf->AddPage();  

	

	

	 

		$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

        $pdf->SetFont('helvetica', 'B', 14);  
		$pdf->Cell(0, 15, 'Summary of Fees and Online Payment details' , 0,1, 'C', 0, '', 0, false, 'T', 'M');

		//$pdf->Cell(0, 5, 'Raw Material Register', 0, 1, 'C', 0, '', 0, false, 'T', 'M');


	$table_data=getP_TABLE($iconn,$RowPo,$ID);

	$pdf->writeHTML($table_data);
	
	 $MON_PERC= 0.2;
			 
			  $MON_PERC= 0.2;
			 
			 $MON= ($RowPo['FobValue']*$MON_PERC)/100;
			 
			 $TAX_AMOUNT= $MON+500+200;
			 
			 
			 
			 $GST_VALUE= ROUND ( ($MON*18)/100 );
			 
			
			 
			$GST_VALUE1 = ROUND($GST_VALUE);
			
			$GST_VALUE2 = ($GST_VALUE1 + 90+36);
			
			 $TOTAL_AMOUNT=ROUND($GST_VALUE2+$TAX_AMOUNT) ;
			 
			 $FINAL_AMOUNT= ROUND($TOTAL_AMOUNT+10);

$AMOUNT= $TOTAL_AMOUNT;

 //$pdf->Cell(0 ,15,'',0,1,'R');

     $pdf->Cell(120 ,5,'Amount: ',0,0,'R');
     $pdf->Cell(0 ,5,$AMOUNT,0,1,'L');
 $pdf->Cell(120 ,5,'Conveniance Fee: ',0,0,'R');
     $pdf->Cell(0 ,5,'10',0,1,'L');
	  $pdf->Cell(120 ,5,'Total Amount:',0,0,'R');
     $pdf->Cell(0 ,5,$FINAL_AMOUNT,0,1,'L');
	  $pdf->Cell(110 ,15,'CHECKED BY',0,0,'R');
     $pdf->Cell(0 ,5,'Approved By            ',0,1,'R');
	 
     $pdf->Cell(0 ,5,'Prepared By',0,1,'L');


  
$file_name='Fees'.$row['PoNo'];
ob_clean();
if($FileType==''){
$FileType='I';
$FileName=$ReportName.'_'.$RowPo['PoNo'];
$FileName=getFormatedFileName($FileName);
$pdf->Output($FileName, 'I');
}else{
	$Fname=$MailTempFolder;
	$FileName=$_GET['FileName'];
	$FileName = str_replace('/','', $FileName );
	$FileName=$Fname.$FileName;
	$pdf->Output($FileName, $FileType);

}

function getP_TABLE($iconn,$RowPo,$ID){
	         $PO_DATE=$RowPo['PoDate'];

	         $INVOICE_DATE=$RowPo['InvDate'];
	         $TRANSACTION_DATE=$RowPo['TrnsDate'];
	         $TRANSACTION_ID=$RowPo['TrnsID'];
			 $TRANSACTION_DATE = date("d-m-Y", strtotime($TRANSACTION_DATE));
			 
			 $MON_PERC= 0.2;
			 
			 $MON= ($RowPo['FobValue']*$MON_PERC)/100;
			 
			 $TAX_AMOUNT= $MON+500+200;
			 
			 
			 
			 $GST_VALUE= ROUND ( ($MON*18)/100 );
			 
			
			 
			$GST_VALUE1 = ROUND($GST_VALUE);
			
			$GST_VALUE2 = ($GST_VALUE1 + 90+36);
			
			 $TOTAL_AMOUNT=ROUND($GST_VALUE2+$TAX_AMOUNT) ;
			
	$content='';

	

	$header_size=10;	

	$text_size=9;	

    $PO_DATE = date("d-m-Y", strtotime($PO_DATE));

    $INVOICE_DATE = date("d-m-Y", strtotime($INVOICE_DATE));

	

	 $content .= '<table border="1" cellspacing="0" cellpadding="4">	



	            <tr>  

           		<th  align="center"  width="30%"><font size="9">Processor Name & App.No</font></th>

                <th  align="LEFT" width="40%"><font size="9">COMPANY NAME & APPROVAL NUMBER</font></th>

                <th  align="center" width="30%"><font size="9">Date: '. $TRANSACTION_DATE.'</font></th>

               
               

				</tr>

				

				<tr>  

           		<th  align="center"  width="30%"><font size="9">Exporter Name:</font></th>

                <th  align="LEFT" width="70%"><font size="9">'.$RowPo['ExpName'].'</font></th>

				

				</tr>

				

				<tr>  

           		<th  align="LEFT"  width="100%"><font size="7"> </font></th>

               </tr>

				

				<tr>  

           		<th  align="center"  width="6%"><font size="'.$header_size.'">SI.NO </font></th>

				<th  align="center"  width="18%"><font size="'.$header_size.'">CFE No.& Date ,<br> Invoice No.& Dtd.</font></th>

                <th  align="center" width="10%"><font size="'.$header_size.'">FOB VALUE <br>  (Rs.)</font></th>

                <th  align="center" width="10%"><font size="'.$header_size.'">Monitering Fee (Rs.)</font></th>

				<th  align="center" width="10%"><font size="'.$header_size.'">HC fee/ ME Countersign  fee/  Any Other fee Including amendment charges(Rs.)</font></th>

                

                <th  align="center" width="9%"><font size="'.$header_size.'">GST  Total(Rs.)</font></th>

                <th  align="center" width="12%"><font size="'.$header_size.'">Total Amount (including GST)</font></th>

                <th  align="center" width="18%"><font size="'.$header_size.'">Online payment Transaction ID</font></th>

                <th  align="center" width="7%"><font size="'.$header_size.'">Remarks</font></th>

                

               </tr>';

			   

			   $COUNT=1;

 $FOB_amount =$RowPo['FobValue'];
$money =$RowPo['FobValue'];

$FOB_VALUE= ROUND($money,0);
$MONITRING_FEE= ROUND($MON,0);
$GST_AMOUNT= ROUND($GST_VALUE,0);
$AMOUNT= ROUND($TOTAL_AMOUNT,0);




		$content .= '



				<tr>  

           		<th  align="center"  width="6%"><font size="'.$text_size.'">1 </font></th>

				<th  align="center"  width="18%"><font size="'.$text_size.'"> Q.CERTIFICATE:'.$RowPo['QCertNo'].'& <br>'.  $INVOICE_DATE.'<BR>
				'.$RowPo['InvNo'].'& <br>'. $INVOICE_DATE.'</font></th>

                <th  align="center" width="10%"><font size="'.$text_size.'">'.$FOB_VALUE.'</font></th>

                <th  align="center" width="10%"><font size="'.$text_size.'">'. $MONITRING_FEE.'</font></th>

				<th  align="center" width="10%"><font size="'.$text_size.'">500 + 200</font></th>

                

                <th  align="center" width="9%"><font size="'.$text_size.'"> '. $GST_VALUE2.'+ 90 +36</font></th>

                <th  align="center" width="12%"><font size="'.$text_size.'">'.$TOTAL_AMOUNT.'</font></th>

                <th  align="center" width="18%"><font size="'.$text_size.'">'.$TRANSACTION_ID.'</font></th>

                <th  align="center" width="7%"><font size="'.$text_size.'"> No Remarks</font></th>


               </tr>';

	
			$content .= '<tr>  

           		<th  align="center"  width="63%"><font size="11"><B> TOTAL Amount </B></font></th>
           		<th  align="center"  width="12%"><font size="11"><B> '.$AMOUNT.' </B></font></th>

               </tr></table>';
	return $content;	

}


function formatToInr($number){
        $number=round($number);
        // windows is not supported money_format
    if(setlocale(LC_MONETARY, 'en_IN')){
         return money_format('%!'.$decimal.'n', $number);
         }
     else {
        if(floor($number) == $number) {
            $append='.00';
        }else{
            $append='';
        }
        $number = preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", $number);
        return $number.$append;
        }
    }

?>