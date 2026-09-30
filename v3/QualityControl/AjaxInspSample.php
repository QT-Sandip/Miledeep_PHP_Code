<?php
session_start();
include "../includes/config.php";
include "Constants.php";
//include "PrintHeaderInc.php";
$ID=$_GET['id'];

$sql_code_list="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID' ORDER BY `DAY_CODE` ASC";
$stmt_code=$iconn->query($sql_code_list);
$varity_ids=array();

$S_W_T_MCS=0;




while($row=$stmt_code->fetch_array()){	

$S_W_T_MCS=$S_W_T_MCS+$row['T_O_MCS'];

$FULL_NAME=$row['FULL_NAME'];	
if(in_array($FULL_NAME, $varity_ids)){					
					}else{
						 $FULL_NAME;
						$varity_ids[]=$FULL_NAME;
						}	
}


	 $TOTAL_SAMPLES =(sqrt($S_W_T_MCS)+1)/2;
	$TOTAL_SAMPLES =round($TOTAL_SAMPLES);



$sql="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();	


$T_GRADES_ARY=array();



$FARM_GRADE=$row['GRADES'];	
	$ARY_GRADES = explode(',', $FARM_GRADE);	
	
	foreach($ARY_GRADES as $value){		
				if(in_array($value, $T_GRADES_ARY)){					
					}else{
						$T_GRADES_ARY[]=$value;
						IF($GRADES==''){
						$GRADES=$value;
					}ELSE{
					 	$GRADES=$GRADES.','.$value;
					}						
		}		
}


//sort($T_GRADES_ARY);

$sql_varity_list="SELECT * FROM `PO_VARIETY_LIST` WHERE INV_ID='$ID'";
$stmt_varity_list=$iconn->query($sql_varity_list);





function getVarityName($ID,$iconn){
	 	
 $sql="SELECT * FROM `PO_VARIETY_LIST`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();
  $name=$row['FULL_NAME'];
 return $name;
}


function generateRow($ID,$iconn,$T_GRADES_ARY,$nameee,$t_width,$S_W_T_MCS){
	
	
	 $TOTAL_SAMPLES =(sqrt($S_W_T_MCS)+1)/2;
	$TOTAL_SAMPLES =round($TOTAL_SAMPLES);
	
	
	
	$sql_code_list="SELECT * FROM `CODE_LIST_CREATE` WHERE FULL_NAME='$ID' ORDER BY `DAY_CODE` ASC";
	$stmt_code_list=$iconn->query($sql_code_list);
	
	
		
		$ARY_GRADE_WISE_MCS=array();
		
		$contents .= '<tr>
				<td align="left"  width="'.$t_width.'%" ><font size="8"><b> VARITY ::</b>'.$nameee.'</font></td></tr>';
	 	
		
		while($row = $stmt_code_list->fetch_assoc()){
			
			$id=$row['FULL_NAME'];
			
			$sql_varity_list="SELECT * FROM `PO_VARIETY_LIST` WHERE ID='$id'";
			$stmt_varity_list=$iconn->query($sql_varity_list);
			$r_varity_list = $stmt_varity_list->fetch_assoc();
			
			$T_ARY_MCS=Array();
			$F_GRADES=Array();
			
			 $MCS=$row['MCS'];
			$ARY_MCS = explode(',', $MCS);
	
				foreach($ARY_MCS as $value){
					$T_ARY_MCS[]=$value;	
				}
				
				 
				
				$GRADES=$row['GRADES'];
				$ARY_GRADES = explode(',', $GRADES);				
				
				foreach($ARY_GRADES as $value){		
				$F_GRADES[]=$value;			
				}
				
				 
				
				$T_MCS=array();
				
				
				for ($x = 0; $x < count($T_GRADES_ARY); $x++) {
					$T_MCS[]='****';
				}
				
			
					//	krsort($T_GRADES_ARY);

				for ($x = 0; $x < count($T_GRADES_ARY); $x++) {		

						//echo $T_GRADES_ARY[$x].'---';				
					 	$r_grade=$T_GRADES_ARY[$x];
						
						if(in_array($r_grade,$F_GRADES )){
							//echo $r_grade.'----';				
							  $key_ref = array_search($r_grade, $T_GRADES_ARY);	
							  $key_value = array_search($r_grade, $F_GRADES);	
							 $T_ARY_MCS[$key];							 
							 array_splice($T_MCS, $key_ref, 0, $T_ARY_MCS[$key_value]);						
						}else{
							
						}					
					}
					
					$TT_ARY_MCS=array();
					for ($x = 0; $x < count($T_GRADES_ARY); $x++) {	
					$TT_ARY_MCS[]=$T_MCS[$x];
					}


 $size=count($T_GRADES_ARY)+5;
 $size=100/$size;	
			
					
					
					
				$contents .= '
				<tr>
				<td align="center"  width="'.$size.'%"><font size="8">'.$row['DAY_CODE'].'</font></td>
				<td align="center"  width="'.($size+5).'%" ><font size="8">'.$r_varity_list['SHORT_NAME'].'<br> '.$row['PACKING'].'  </font></td>
				<td align="center"  width="'.($size+5).'%" ><font size="7">'.$row['FARM_REG_ID'].'</font></td>
			';
			//$G_TOTAL=array();
			
			$ARY_GRADE_WISE_MCS[]=$TT_ARY_MCS;
			
			 	$v_sample=0;
			foreach($TT_ARY_MCS as $value){
				
				if($value!='****'){
				$each_sample=($value/$S_W_T_MCS)*$TOTAL_SAMPLES;
				$each_sample=round($each_sample);
				$v_sample=$v_sample+$each_sample;				
				}else{
				$each_sample=$value;

				}
				
				
					/*$G_TOTAL[]=$value;
					 $value;*/
				
				
		
		
		$contents .= '<td align="center" width="'.$size.'%"><font size="8">'.$each_sample.'</font></td>';
		
		}
		
		/*$v_sample=($row['T_O_MCS']/$S_W_T_MCS)*$TOTAL_SAMPLES;
				$v_sample=floor($v_sample);
				
				*/
		
		$contents .= '<td align="center" width="'.$size.'%" ><font size="8">'.$v_sample.'</font></td></tr>';
		}
		
		
		
		return $contents;
	}
	
	
	
	
	function generateTableHead($T_GRADES_ARY){ 
 
	 $size=count($T_GRADES_ARY)+5;
	 $size=100/$size;	
		
		foreach($T_GRADES_ARY as $value){
						$contents .= '<th width="'.$size.'%" align="center"><font size="7">'.$value.'</font></th>';
		}		
		return $contents;
	}
	
	
	function getRowWidth($T_GRADES_ARY){ 
 
	 $size=count($T_GRADES_ARY)+5;
	 $size=100/$size;	
	 $t_width=0;
		
		foreach($T_GRADES_ARY as $value){
					$t_width=$t_width+$size;
		}		
		return $t_width;
	}
	
	function getTotals($ID,$iconn,$T_GRADES_ARY,$S_W_T_MCS){
		
			 $TOTAL_SAMPLES =(sqrt($S_W_T_MCS)+1)/2;
	$TOTAL_SAMPLES =round($TOTAL_SAMPLES);
	
	
		
			$sql_code_list="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID' ORDER BY `CODE_LIST_ID` ASC";
	$stmt_code_list=$iconn->query($sql_code_list);
	
		
		$ARY_GRADE_WISE_MCS=array();
		
		
		//$contents = '';
	 	
		$over_t_mcs='0';
		while($row = $stmt_code_list->fetch_assoc()){
			
			$id=$row['FULL_NAME'];
			
			 $sql_varity_list="SELECT * FROM `PO_VARIETY_LIST` WHERE ID='$id'";
			$stmt_varity_list=$iconn->query($sql_varity_list);
			$r_varity_list = $stmt_varity_list->fetch_assoc();
			
			$T_ARY_MCS=Array();
			$F_GRADES=Array();
			
			 $MCS=$row['MCS'];
			$ARY_MCS = explode(',', $MCS);
			
			$over_t_mcs=$over_t_mcs+$row['T_O_MCS'];
	
			foreach($ARY_MCS as $value){
					$T_ARY_MCS[]=$value;	
				}
				
				 count($T_ARY_MCS).'end';
				
				$GRADES=$row['GRADES'];
				$ARY_GRADES = explode(',', $GRADES);				
				
				foreach($ARY_GRADES as $value){		
				$F_GRADES[]=$value;			
				}
				
				 count($F_GRADES).'end';
				
				$T_MCS=array();
				
				
				for ($x = 0; $x < count($T_GRADES_ARY); $x++) {
					$T_MCS[]='****';
				}
				
			
					//	krsort($T_GRADES_ARY);

				for ($x = 0; $x < count($T_GRADES_ARY); $x++) {		

						//echo $T_GRADES_ARY[$x].'---';				
					 	$r_grade=$T_GRADES_ARY[$x];
						
						if(in_array($r_grade,$F_GRADES )){
							//echo $r_grade.'----';				
							  $key_ref = array_search($r_grade, $T_GRADES_ARY);	
							  $key_value = array_search($r_grade, $F_GRADES);	
							 $T_ARY_MCS[$key];							 
							 array_splice($T_MCS, $key_ref, 0, $T_ARY_MCS[$key_value]);						
						}else{
							
						}					
					}
					
					$TT_ARY_MCS=array();
					for ($x = 0; $x < count($T_GRADES_ARY); $x++) {	
					$TT_ARY_MCS[]=$T_MCS[$x];
					}


					
					
				
			//$G_TOTAL=array();
			
			$ARY_GRADE_WISE_MCS[]=$TT_ARY_MCS;
			
			
		
	
		}
		
		
	
			$ARY_CCCC=array();
		  count($TT_ARY_MCS);
		  
		  //print_r($ARY_GRADE_WISE_MCS);
		
		for($i=0; $i<count($TT_ARY_MCS); $i++){
			
			$t_val=0;
			for($j=0; $j<count($ARY_GRADE_WISE_MCS); $j++){
			
			if($ARY_GRADE_WISE_MCS[$j][$i]!='****'){
			 $t_val=$t_val+$ARY_GRADE_WISE_MCS[$j][$i];	
			}else{
				
			}
			
			
			
		}
		$ARY_CCCC[]=$t_val;
	
		}
		
		 $size=count($T_GRADES_ARY)+5;
				$size=100/$size;	
		
		
		foreach($ARY_CCCC as $value){
				
				
				
					/*$G_TOTAL[]=$value;
					 $value;*/
					 
					 if($value!='****'){
				$each_sample=($value/$S_W_T_MCS)*$TOTAL_SAMPLES;
				$each_sample=round($each_sample);
				}else{
				$each_sample=$value;	
				}
				
				
		
		$contents .= '<td align="center"  width="'.$size.'%"><font size="8">'.$each_sample.'</font></td>';
		$RR=$RR+$each_sample;
		}
		
		$v_sample=($over_t_mcs/$S_W_T_MCS)*$TOTAL_SAMPLES;
				$v_sample=floor($v_sample);
		
		$contents .= '<td align="center" width="'.$size.'%"  ><font size="8">'.$v_sample.'</font></td>';
	//	$contents .= '<td align="center" width="'.$size.'%"  ><font size="8">'.$RR.'</font></td>';
		
		
		return $contents; 

	}


		
	

	$from_title = date('M d, Y');
	$to_title = date('M d, Y');

	
	
	

$header=11;
$sub_header=10;

	
		//$pdf->Cell(0 ,0.5,'',0,1,'L');
		
		$size=count($T_GRADES_ARY)+5;
	  $size=100/$size;
	  
	  $t_width=getRowWidth($T_GRADES_ARY);
	    $t_width=$t_width+(4*$size+10);
	 		
    $content = '';  
    $content .= '
      	
      	<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="center"  width="'.$size.'%"><font size="7"> DAY CODE </font></th>
                <th  align="center" width="'.($size+5).'%"><font size="7">VARITY TYPE OF PACKING</font></th>
				<th  align="center" width="'.($size+5).'%"><font size="7">TRACEABILITY</font></th>';  
	  
				$content .= generateTableHead($T_GRADES_ARY);  
				$content .= '<th  align="center" width="'.$size.'%"><font size="7"> TOTAL MCS </font></th></tr>'; 
	  
     
	  foreach($varity_ids as $value){
		  
		$nameee=getVarityName($value,$iconn);	 
		$size=count($T_GRADES_ARY)+2;
		$size=100/$size;     
	    $content .= generateRow($value,$iconn,$T_GRADES_ARY,$nameee,$t_width,$S_W_T_MCS);    
	  }
	 $size=count($T_GRADES_ARY)+5;
	  $size=100/$size;
	  $t_width=(3*$size+10);
	 
			$content .= '<tr>
			<th  align="center"  width="'.$t_width.'%"><font size="7"> TOTAL </font></th>
			' ;
			$content .= getTotals($ID,$iconn,$T_GRADES_ARY,$S_W_T_MCS);
			$content .= '</tr>' ;
			$content .= '</table>'; 
			//$pdf->writeHTML($content,false); 
     
  




$COUNT=1;
//$pdf->Cell(0 ,5,'Q.C. Technologist',0,1,'L');





//$pdf->Output('payroll.pdf', 'I');

?>