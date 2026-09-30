<?php

$BlockID=$_POST['BlockID'];
$FloorID=$_POST['FloorID'];
$RackID=strtoupper($_POST['RackID']); 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

if($RackID != ''){
	
		
$sql_serch = "SELECT * FROM `CsStoresRackDataV1` WHERE `BlockID`='$BlockID' and `RowID` ='$RackID'";
$query_serch = $iconn->query($sql_serch);

if($query_serch->num_rows=='0'){
			echo '<div class="tab-content">
                      <div id="billdesk" class="tab-pane fade in active">
                        <div class="row">
						 <div class="col-sm-4">
						 </div>
					
						 <div class="col-sm-4"></br>
						  </br>
						  </br>
						  </br>
						  <strong class="error_spy" style="color:red" align="center">*** No Records Found  ***</strong>
						  </div>
						<div class="col-sm-4">
						 </div>
					
						  </div>
						
                      </div>
                    </div>
';
exit;
		}else{
			
			$row_serch = $query_serch->fetch_assoc();
			$FloorID= $row_serch['FloorID'];
			
			echo  '<strong class="error_spy" style="color:red" align="center">***'. 'FLOOR - '. ($FloorID-1).' ***</strong>
						  </div>
						<div class="col-sm-4">
						 </div>';
		}


	
}


IF($BlockID >0){

$Path='<li class="row row--10"><ol class="seats" type="A">
	  <li class="seat" <!--style="border-left: 1.5px solid black;--> ">
		</li>
		<li class="seat"></li>
		<li class="seat"></li>
		
		<li class="seat">
		<div class="arrow" style=" width: 132px;">
			 <div class="point" style="width: 0;
  height: 0;
  border-top: 20px solid transparent;
  border-bottom: 20px solid transparent;
  border-right: 30px solid black;
  float: left;
"></div>
  <div class="line" style=" margin-top: 14px;
  width: 102px;
  background: black;
  height: 10px;
  float: left;
"></div>
  
</div> </li>

<li class="seat"></li><li class="seat"></li>
<li class="seat"></li><li class="seat"></li>


<li class="seat"><div class="arrow" style=" width: 132px;">
			 <div class="point" style="width: 0;
  height: 0;
  border-top: 20px solid transparent;
  border-bottom: 20px solid transparent;
  border-left: 30px solid black;
  float: right;">
  
  </div>
  <div class="line" style=" margin-top: 14px;
  width: 102px;
  background: black;
  height: 10px;
  float: right;
"></div>
  
</div> </li>

<li class="seat"></li><li class="seat"></li>

<!--<li class="seat" style="margin-left: 14px; border-right: 1.5px solid black"> -->
		</li>
		</ol></li>';
			
	
$LAYOUT='';	
$LAYOUT.= '<div class="col-md-12" style="overflow: auto;"><div class="tab-content">';
$i='0';
  $sql = "SELECT DISTINCT(`FloorID`) FROM `CsStoresRackDataV1` WHERE BlockID='$BlockID' and FloorID='$FloorID'";
		$query = $iconn->query($sql);
	
while($row=$query->fetch_assoc())
{
$active="active";
if($i=='0'){
	 $LAYOUT.=    '<div class="tab-pane active"  id="tb'.$row['FloorID'].'">' ;
     echo $LAYOUT.=  ' <div class="plane" > <ol class="cabin fuselage">';
  
}else {
	
	 echo   '<div class="tab-pane" id="tb'.$row['FloorID'].'">' ;
                echo ' <div class="plane">
  <ol class="cabin fuselage">';
  
}
      $FloorID=$row['FloorID'];
	  $columns="SELECT * FROM `CsStoresRackDataV1` where  BlockID='$BlockID'   group by `ColumnNumber`";
	  $colum_exec=$iconn->query($columns);
	  $COUNT=1;
     
	  while($row_coloumn=$colum_exec->fetch_assoc()){
	     if ($row_coloumn['Path']==1){
	  
	  echo $Path;
			  
			  
		  } 
		 echo '<li class="row row--10">
      <ol class="seats" type="A">';
	    $sl_row="SELECT * FROM `CsStoresRackDataV1` WHERE `FloorID`='$FloorID' 
		and `ColumnNumber`=".$row_coloumn['ColumnNumber']."
	   and BlockID='$BlockID' " ; 
	 // echo $sl_row;
$query_row = $iconn->query($sl_row);
 $TWO=1;

	while($row_row = $query_row->fetch_assoc()){
	$RowID=$row_row['RowID'];
	$RACK_ID=$row_row['RackID'];
	$RACK_NAME=$row_row['RackName'];
	$ROW_NUMBER=$row_row['RowNumber'];
	$ColumnNumber=$row_row['ColumnNumber'];
	 $ARY_BORDER=$row_row['RackBarcode'];//TOP,RIGHT,BOTTOM,LEFT
	
	if($ARY_BORDER!='1'){
	 $ARY_BORDER=explode(',',$ARY_BORDER);
	 }

	 if($ColumnNumber %2 ==0 ){		
		$h_line='border-bottom: 3px solid orange;
				 border-top: 1px solid black;';
	}else if($ColumnNumber%2 !=0){		
		$h_line='border-bottom: 1px solid black;
			border-top: 2px solid orange;';
	}
	
	
	
	
	
	if( $ARY_BORDER =='1'){
		
		//echo $RACK_NAME;
		$v_line='border-left: 1px solid black;
			border-RIGHT: 1px solid black;';
	
	
	
	$style=$v_line.$h_line;
	}else if($ARY_BORDER !='1'){

	if($ARY_BORDER[1]==1 && $ARY_BORDER[3]==1){
		$v_line='border-left: 2.5px solid blue;
			border-RIGHT: 2.5px solid blue;';	
	}else if($ARY_BORDER[1]==1){
	$v_line='border-left: 1.5px solid black;
			border-RIGHT: 2.5px solid blue;';	
	}else if($ARY_BORDER[3]==1){
	$v_line='border-left: 2.5px solid blue;
			border-RIGHT: 1.5px solid black;';	
	} 
			$style=$v_line.$h_line;
	}else if( $ROW_NUMBER ==1){
	$v_line='border-left: 2.5px solid blue;
			border-RIGHT: 1px solid black;';
	
	$style=$v_line.$h_line;
	}
			
			
	$style_white='border-left: 1.5px solid white;
					border-RIGHT: 1.5px solid white;
					border-bottom: 1.5px solid white;
					border-top: 1.5px solid white;';
					
	$border_white= '<li class="seat vertical" style="'.$style_white.'">';				
	$border_black= '<li class="seat vertical" style="'.$style.'">';				
	//echo $COUNT;
	//echo $TWO;
	
	if($row_row['Position'] ==0  ){
	echo $border_white;
	}else {
	echo $border_black;
	//$RACK_NAME='   <br>'; 
	if($row_row['Status']=='1' ){

		if($RACK_NAME==strtoupper($RackID)){
		echo ' <label class="filled" for="10A" id="'.$row_row['RowID'].'" style="background:#134867; color:#f1f1f1">
		 '.$RACK_NAME.'</label>';
		}else{
		  echo ' <label class="filled" for="10A" id="'.$row_row['RowID'].'" style="color:#f1f1f1">'.$RACK_NAME.'</label>';
	
		}
		  
    	  }
		  
		  else {
			 
			
		if($RowID==$RackID){
		echo ' <label class="available" for="10A" id="'.$row_row['RowID'].'" style="background:#134867; color:#f1f1f1">'.
				 $RACK_NAME.'</label>';
		}else{
		echo ' <label class="available" for="10A" id="'.$row_row['RowID'].'" style="background:#089420; color:#f1f1f1">'.
				 $RACK_NAME.'</label>';
		}
		  
		  }
	}
 		  
        echo '</li>';

	} 
	 echo '   </ol></li>';
		  

	  }
	  echo '
  </ol>
 </div>
</div>';

 $i++;
 	
   
}
 echo '</div></div>';
        
}

           ?>