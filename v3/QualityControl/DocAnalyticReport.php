<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";

$CL_ID=$_GET['id'];



require_once('../tcpdf/tcpdf.php'); 
	
	//$Exporter=$PROCESSOR;


	
class MYPDF extends TCPDF {
	
	
 public function Footer() {
      
		//$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		//$this->SetY(-55);
   }
   
    public function Header() {	
	
	include "Constants.php";
	 
        $image_file = K_PATH_IMAGES.'jme_logo.jpg';
        //$this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 15);
		//$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'M/s.Jagadeesh Marine Exports,Bhimavaram', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'Raw Material Register', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		
    }
	
   
}


	
    $pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Analytical Result: '.$row['PO_NUM'].' - '.$row['PO_DATE']);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
 $pdf->SetFooterMargin(0); 
	$pdf->SetHeaderMargin(0);	
 $pdf->SetMargins(5, 5, 5,1);
    $pdf->setPrintHeader(false);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 4);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  

		
	$sql_code_list="SELECT PVL.SHORT_NAME ,CL.INV_ID,CL.DAY_CODE , CL.FARM_REG_ID , CL.T_O_MCS ,CL.BRAND  FROM 
`CODE_LIST_CREATE`AS CL JOIN PO_VARIETY_LIST 
AS PVL ON CL.FULL_NAME=PVL.ID  where CL.CODE_LIST_ID=$CL_ID";
$STMT_CODE_LIST=$iconn->query($sql_code_list);
$ROW_CODE_LIST=$STMT_CODE_LIST->fetch_array();

$ID=$ROW_CODE_LIST['INV_ID'];

$ID=$ID;
include "Constants.php";
	 
	 
		//$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$pdf->Cell(0, 5, $EXPORTOR, 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$pdf->Cell(0, 5, 'REGISTER FOR ANALYTICAL RESULTS', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	



	$table_data=getP_TABLE_head($iconn,$CL_ID);
	$pdf->writeHTML($table_data);
		 $limit = 10; 
		 
	 $sql = "SELECT COUNT(*) FROM QC_ANALYTIC_REPORTS where CODE_LIST_ID=$CL_ID"; 
	 
	$stmt_sql=$iconn->query($sql);	
     $rs_result = $stmt_sql->fetch_array();    
     
        $total_records = $rs_result[0];   

		$total_pages = ceil($total_records / $limit);   
        
		$start_from=0;		
			   
        for ($i=1; $i<=$total_pages; $i++) { 
     
	 $start_from;
     
	 
     $table_data=getDS_TABLE3($iconn,$ID,$CL_ID,$start_from,$limit);
	 $pdf->writeHTML($table_data);
	 $start_from=($start_from+$limit);
	  //$pdf->AddPage('L');
	
		}
	
  $pdf->Cell(49 ,5,'Signature of technologist with Date:',0,1,'L');
$file_name='Analytic Report.pdf'.$row['PO_NUM'];
$pdf->Output($file_name, 'I');



function getP_TABLE_head($iconn,$CL_ID){
	
	
	
	
		
	
$sql_code_list="SELECT PVL.SHORT_NAME ,CL.INV_ID,CL.DAY_CODE , CL.FARM_REG_ID , CL.T_O_MCS ,
CL.BRAND , CL.PACKING FROM 
`CODE_LIST_CREATE`AS CL JOIN PO_VARIETY_LIST 
AS PVL ON CL.FULL_NAME=PVL.ID  where CL.CODE_LIST_ID=$CL_ID";
$STMT_CODE_LIST=$iconn->query($sql_code_list);
$ROW_CODE_LIST=$STMT_CODE_LIST->fetch_array();
$ID=$ROW_CODE_LIST['INV_ID'];
$S_NAME=$ROW_CODE_LIST['SHORT_NAME'];
$DAY_CODE=$ROW_CODE_LIST['DAY_CODE'];
$TOTAL_MCS=$ROW_CODE_LIST['T_O_MCS'];
$FARM_NUMBER=$ROW_CODE_LIST['FARM_REG_ID'];
$BRAND=$ROW_CODE_LIST['BRAND'];
$PACKING=$ROW_CODE_LIST['PACKING'];

$Production_date=getDayCodeDate($DAY_CODE);

$Examine_date=date('Y-m-d', strtotime('+1 day', strtotime($Production_date)));

 $Examination_Date = date("d-m-Y", strtotime($Examine_date));

 
include "Constants.php";
	

	
	
	 $SQL_INSP="SELECT * FROM QC_ANALYTIC_REPORTS WHERE CODE_LIST_ID= $CL_ID";
	 
		$stmt_mpeda_data=$iconn->query($SQL_INSP);
		
	 while($r_varity_list = $stmt_mpeda_data->fetch_assoc()){
				
				$data[] = $r_varity_list;
			}  
	
	
	
	$n_o_samples=count($data);
			

			
	 $PACKING=getPacking($iconn,$ID);
			 
	$count=1;
	$content='';
	$size=40;	
	$size1=60;
	$F_SIZE=9;
		   
	
	

	 

	 $content .= '<table border="1" cellspacing="0" cellpadding="0">	

	            <tr>  
           		<th  align="center"  width="12.5%"><font size="'.$F_SIZE.'">Name of Processor:</font></th>
                <th  align="center" width="12.5%"><font size="9"><B>'.$EXPORTOR.'</B></font></th>
				<th  align="center"  width="12.5%"><font size="'.$F_SIZE.'">Approval No:</font></th>
				<th  align="center"  width="12.5%"><font size="9"><b>'.$EXPORTOR_APP_NUM.'</b></font></th>
                <th  align="center" width="12.5%"><font size="'.$F_SIZE.'">Type of Product:</font></th>
                <th  align="center" width="12.5%"><font size="9"><B>'.$S_NAME.'</B></font></th>
                <th  align="center" width="12.5%"><font size="'.$F_SIZE.'">Prodution Code</font></th>
                <th  align="center" width="12.5%"><font size="9"><B>'.$DAY_CODE.'</B></font></th>
                

				</tr>  
				
				<tr>  
           		<th  align="center"  width="12.5%"><font size="'.$F_SIZE.'">No. of Cartons</font></th>
                <th  align="center" width="12.5%"><font size="7">'.$TOTAL_MCS.'</font></th>
				
                <th  align="center" width="12.5%"><font size="'.$F_SIZE.'">No. of Samples:</font></th>
                <th  align="center" width="12.5%"><font size="7">'.$n_o_samples.'</font></th>
                <th  align="center" width="12.5%"><font size="'.$F_SIZE.'">Date of Examination</font></th>
                <th  align="center" width="12.5%"><font size="7">'.$Examination_Date.'</font></th>
                <th  align="center" width="12.5%"><font size="'.$F_SIZE.'">Brand</font></th>
                <th  align="center" width="12.5%"><font size="7">'.$BRAND.'</font></th>
                

				</tr>
				
				<tr>  
           		<th  align="center"  width="20%"><font size="'.$F_SIZE.'">Product Core Temp</font></th>
                <th  align="center" width="30%"><font size="9"><B> -18.1C </B></font></th>
                <th  align="center" width="20%"><font size="'.$F_SIZE.'">Tracebility Details</font></th>
                <th  align="center" width="30%"><font size="9"><B>'.$FARM_NUMBER.'</B></font></th>
                

				</tr>
				
				

			 </table>';
			   
			   return $content;
			

}
	
	$c_size=5;
function getDS_TABLE3($iconn,$ID,$CL_ID,$start_from,$limit){
		
	include "Constants.php";	
	//umask
	
	
	$COUNTRY=$row_inv['DESTINATION_COUNTRY'];
	
	
	IF($COUNTRY== 'USA'){
		
		$SALMONELLA='Absent/375g';
	}else{
		
		$SALMONELLA='Absent/25g';
	}
	
	
	$sql_code_lists="SELECT PVL.SHORT_NAME ,CL.INV_ID,CL.DAY_CODE , CL.FARM_REG_ID , CL.T_O_MCS ,
CL.BRAND , CL.PACKING FROM 
`CODE_LIST_CREATE`AS CL JOIN PO_VARIETY_LIST 
AS PVL ON CL.FULL_NAME=PVL.ID  where CL.CODE_LIST_ID=$CL_ID";
$STMT_CODE_LISTs=$iconn->query($sql_code_lists);
$ROW_CODE_LISTs=$STMT_CODE_LISTs->fetch_assoc();

				//print_r($ROW_CODE_LISTs);

				$PACK_STYLE = $ROW_CODE_LISTs['PACKING'];

	
  $SQL_count="SELECT count(*)as COUNT,INV_ID,CODE_LIST_ID,DAY_CODE 
  FROM QC_ANALYTIC_REPORTS WHERE CODE_LIST_ID= $CL_ID";   
		$stmt_count=$iconn->query($SQL_count);
	
	       $r_count = $stmt_count->fetch_assoc();
		   $COUNT=$r_count['COUNT']; 
		   $INV_ID=$r_count['INV_ID']; 
		   $C_ID=$r_count['DAY_CODE'].'&'.$r_count['CODE_LIST_ID']; 
		  
		  // $C_ID=$r_count['CODE_LIST_ID']; 
		

	//**//	
			
		  $SQL_INSP="SELECT * FROM QC_ANALYTIC_REPORTS WHERE CODE_LIST_ID= $CL_ID";  
			
		$stmt_mpeda_data=$iconn->query($SQL_INSP);
			
			
	       while($r_varity_list = $stmt_mpeda_data->fetch_assoc()){
				
				
				
				$data[] = $r_varity_list;

			}  

			
				$c=1+$start_from;
				$cell_size=100-30;
				$cell_size=$cell_size/count($data);
			
			//uma fetching data from mb result
			
 $sql_mbr="SELECT * FROM `BUYER_MB_REPORT` WHERE `INVOICE_ID`='$INV_ID'  ORDER by ID ASC";   
	
 $stmt_mbr=$iconn->query($sql_mbr);
 	       $r_mbr = $stmt_mbr->fetch_assoc();

 		   $MB_ID=$r_mbr['ID']; 

 
 	if($MB_ID!=''){	

 //$sql_mb="SELECT * FROM `TEST_RESULT_PARMS` where TEST_ID='4' and COUNTRY='$COUNTRY'";

 	  $sql_mb="SELECT * FROM `BUYER_MB_RESULT` WHERE `MB_REPORT_ID`='$MB_ID'";  
	  
	  
 $stmt_mb=$iconn->query($sql_mb);
					
	while($row_mb = $stmt_mb->fetch_assoc()){
	
		//$INVOICE_ID=$row_mb['INVOICE_ID'];
			//$FARM_NUMBER = MB_ID['P_CODES_REG_NUM']		
  				$data_mb[] = $row_mb;
				
				$CODELIST_ID=$row_mb['P_CODES_REG_NUM'];
				$TRACEBILITY_ID=EXPLODE("&",$CODELIST_ID);
			$TRACE_ID=$TRACEBILITY_ID[1];
			
			IF($CL_ID==$TRACE_ID){
						
				$TPC=$row_mb['TPC'];
				$Ecoli=$row_mb['ECOLI'];
				$STAPHYLO_COCCUS=$row_mb['STAPHYLO_COCCUS'];
				$SALMONELLA=$row_mb['SALMONELLA'];
				$V_CHOLERAE=$row_mb['V_CHOLERAE'];
				$V_PRAHAE=$row_mb['V_PRAHAE'];
			}
	}
  
   
 
			 
			 $count=1;
	$content='';
	
	$size=40;	
	$size1=60;	
	$F_SIZE=9;
		
	 //  $GLAZE=$row_mb['GLAZE'];
		//$DEGLAZED_WEIGHT=$row_mb['DEGLAZED_WEIGHT'];
		//$FROZEN=($GLAZE + $DEGLAZED_WEIGHT)/100;
	
	$Alignment='left';
	
	
	 $content .= '<table border="1" cellspacing="0" cellpadding="0">
	 <tr>
	 
	 <td width="90%">';


	 $content .= '<table border="1" cellspacing="0" cellpadding="0">	

	           
				<tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Sample No.</font></th>';  
				foreach($data as $value){	       
					
					$content .='<th  align="center"  width="'.$cell_size.'%">
					<font size="'.$F_SIZE.'">'.$count.'</font></th>';
				

				$count++;
				}
				
				

               $content .='</tr>

                <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Type of packing</font></th>';  
				forEACH($data as $value){	         
					
				$content .='<th  align="center"  width="'.$cell_size.'%"><font size="'.$F_SIZE.'">
				'.$PACK_STYLE.'</font></th>';
				

				$count++;
				}
				
				

               $content .='</tr>

               <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Species</font></th>';  
				foreach($data as $value) {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">
					'.$row_inv['SCIENTIFIC_NAME'].'</font></th>';
				

				$count++;
				}
				
				

               $content .='</tr>
			   
			   
			   <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Declared size grade</font></th>';  
				foreach($data as $value)  {         
					
				$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">'.$value['GRADE'].'
				</font></th>';
				

				$count++;
				}
				
				

               $content .='</tr>
			   
			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Glaze%</font></th>';  
				foreach($data as $value) {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">'.$value['GLAZE'].'</font></th>';
				

				$count++;
				}
				
				

               $content .='</tr>
			   
			   <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Frozen wt. in Kgs</font></th>';  
				foreach($data as $value)  { 

				$FROZEN	=$value['DEGLAZED_WEIGHT']+($value['DEGLAZED_WEIGHT']*$value['GLAZE'])/100;			
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">'.$FROZEN.'
					</font></th>';
				

				$count++;
				}
				

               $content .='</tr>
			   
			   <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'"> Deglazed Wt in Kg.</font></th>';  
				foreach($data as $value) {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">'.$value['DEGLAZED_WEIGHT'].'</font></th>';
				

				$count++;
				}
				
				

               $content .='</tr>
			   
			   
			  
			   
			   
			   <tr>  
           		<th  align="left"  width="30%"><font size="'.$F_SIZE.'"> Deglazed count</font></th>';  
				foreach($data as $value) {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">'.$value['DEGLAZED_COUNT'].'</font></th>';
				

				$count++;
				}
				
               $content .='</tr>
			   
			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  General Apperance</font></th>';  
				foreach($data as $value) {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">GOOD</font></th>';
				
			//'.$value['APPEARANCE'].'
				$count++;
				}
				
               $content .='</tr>
			   
			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Feneral Odour</font></th>';  
				foreach($data as $value)  {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">CHARCTERSTIC</font></th>';
				

				$count++;
				}
				
               $content .='</tr>
			   
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Dehydration</font></th>';  
				foreach($data as $value) {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">NIL</font></th>';
				

				$count++;
				}
				
               $content .='</tr>
			   
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Decolouration</font></th>';  
				foreach($data as $value)  {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">NIL</font></th>';
				

				$count++;
				}
				
				$content .='</tr>
			   
			   
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Texture</font></th>';  
				foreach($data as $value)  {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">FIRM</font></th>';
				

				$count++;
				}
				
				
               $content .='</tr>
			   
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Deterioration</font></th>';  
foreach($data as $value)  {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">NIL</font></th>';
				

				$count++;
				}
				
               $content .='</tr> 
			   
			   
			   
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Black spot on shell/meat</font></th>';  
				foreach($data as $value) {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">'.$value['BLACK_SPOT_MEAT'].'</font></th>';
				

				$count++;
				}
				
               $content .='</tr>
			   
			   
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Broken/Damaged/bruiced Pieces</font></th>';  
				foreach($data as $value)  {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">'.$value['BROKEN_DAMAGE'].'</font></th>';
				

				$count++;
				}
				
				  
               $content .='</tr>
			   
			   
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Hanging Meat</font></th>';  
foreach($data as $value)  {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">NIL</font></th>';
				

				$count++;
				}
				
				
               $content .='</tr>
			   
			   			    <tr>  
           		<th  align="left"  width="30%"><font size="'.$F_SIZE.'"> Debris:(Legs,bits of veins,antennae,
				 etc)</font></th>';  
				foreach($data as $value)  {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">NIL</font></th>';
				

				$count++;
				}
				
               $content .='</tr>
			   
			   
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'"> B\S Ratio</font></th>';  
				foreach($data as $value)  {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">'.$value['UNIFORMITY'].'</font></th>';
				

				$count++;
				}
				
               $content .='</tr>
			   
			   
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Foreign vegetable matter and No of pieces</font></th>';  
				foreach($data as $value) {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">NIL</font></th>';
				

				$count++;
				}
				
               $content .='</tr>
			   
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Objectionable Foreign matter</font></th>';  
				foreach($data as $value)  {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">NIL</font></th>';
				

				$count++;
				}
				
             
               $content .='</tr>
			   
			   
			   
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Product Satisfactory/Unsatisfactory</font></th>';  
				foreach($data as $value)  {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">Satisfactory</font></th>';
				

				$count++;
				}
				
               $content .='</tr>
			   
			   <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">   TPC at 37C.c.f.u/gm</font></th>';  
				      
					
					$content .='<th  align="center"  width="70%"><font size="7">'.$TPC.'</font></th>';
				

				$count++;
				
				
               $content .='</tr>
			   
			   
			   
			   			
			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">   EColi at 37C.c.f.u/gm</font></th>';  
				     
					
					$content .='<th  align="center"  width="70%"><font size="7">'.$Ecoli.'</font></th>';
				

				$count++;
				
				
               $content .='</tr>
			   			   			       <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">   Staph.cocci at 37C.c.f.u/gm</font></th>';  
				
					
					$content .='<th  align="center"  width="70%"><font size="7">'.$STAPHYLO_COCCUS.'</font></th>';
				

				$count++;
				
				
               $content .='</tr>
			   
			   
			   			   			   
			   			   			       <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">   Salmonella/Arizona</font></th>';  
				       
					
					$content .='<th  align="center"  width="70%"><font size="7">'.$SALMONELLA.'</font></th>';
				

				$count++;
				
				
               $content .='</tr>
			   
			   
			   
			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">   V.C/V.P</font></th>';  
				        
					
					$content .='<th  align="center"  width="70%"><font size="7">'.$V_CHOLERAE.'/'.$V_PRAHAE.'</font></th>';
				

				$count++;
				
				
               $content .='</tr>
			   
			   			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Satisfactory/Unsatisfactory</font></th>';  
				foreach($data as $value){         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">Satisfactory</font></th>';
				

				$count++;
				}
				
               $content .='</tr>
			   
			   			   			   			    <tr>  
           		<th  align="center"  width="30%"><font size="'.$F_SIZE.'">Decision:Export worthy/Non exportworthy/to be re-examined</font></th>';  
				foreach($data as $value) {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">EXPORT WORTHY</font></th>';
				

				$count++;
				}
				
               $content .='</tr>
			   
			   
			   			   			   			    <tr>  
           		<th  align="'.$Alignment.'"  width="30%"><font size="'.$F_SIZE.'">  Remarks:</font></th>';  
				foreach($data as $value) {         
					
					$content .='<th  align="center"  width="'.$cell_size.'%"><font size="7">NIL</font></th>';
				

				$count++;
				}
				
              
 


			  $content .='</tr></table></td>
		  
			  
			  
			  <td width="10%">
			  
			  <table border="0" cellspacing="0" cellpadding="0">
			  
			 <tr  align="center" ><font size="8"> <BR><BR><BR><BR> <BR><BR><BR><BR> <BR><BR><BR><BR> 
			 ORGENALEPTIC EVALUATION    <BR>
			 <BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><B>__________________</B><BR>BACTERIOLOGICAL EVALUATION</font></tr>
			
			
			
			</table>
			  
			  </td>  
			 
			  </tr> 
			  
			  </table>'; 	   
	
			   
			   

					   
	
	//echo $content;
	return $content;

	}
else{
	echo "please  enter microbiology report";
}

}

	
     
$pdf->Output('payroll.pdf', 'I');




        ?>