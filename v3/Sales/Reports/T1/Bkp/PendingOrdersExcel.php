<?php 
$PageTitle="MPEDA Report";
$PageTitleFolder="Reports";

include("../../RestAPI.php");

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");


    $api = GetPendingContracts_API;

    $bodyContent = array( "ProformaID" => $ProformaID );

     $resGetPendingContractsAPI = CallAPI($api, "GET", "", $bodyContent);
    // var_dump($resPostProfromaPrint);exit;

    $resList = $resGetPendingContractsAPI->Data->Data;




$S_NO=1;
			  $widtd=40;
			  $widtd_s=15;
			  $widtd_ss=10;
              //$FontSize=0;
			  
			 
			  $Content .= '<table  id="key-datatableFullscreen" data-paging="false" class="table  wrap w-150 table-bordered" >
              <thead class="thead-light">
                  <tr>
                      <th style="min-width:40px;">S No.</th>
                      <th style="min-width:250px;">Buyer</th>
                      <th style="min-width:120px;">Destination</th>
                      <th style="min-width:120px;">Shipment</th>
                      <th style="min-width:180px;">P.O.NO.</th>
                      <th style="min-width:120px;">Proc./Exp</th>
          
                      <th style="min-width: 160px;">Brand</th>
                      <th style="min-width: 160px;">Certification</th>
                      <th style="min-width: 110px;">Variety</th>
                      <th style="min-width: 110px;">Form</th>
                      <th style="min-width: 110px;">Packing</th>
                      <th style="min-width: 80px;">Glaze</th>
                      <th style="min-width: 80px;">6/8</th>
                      <th style="min-width: 80px;">8/12</th>
                      <th style="min-width: 80px;">13/15</th>
                      <th style="min-width: 80px;">16/20</th>
                      <th style="min-width: 80px;">21/25</th>
                      <th style="min-width: 80px;">25/30</th>
                      <th style="min-width: 80px;">31/35</th>
                      <th style="min-width: 80px;">31/40</th>
                      <th style="min-width: 80px;">41/50</th>
                      <th style="min-width: 80px;">51/60</th>
                      <th style="min-width: 80px;">61/70</th>
                      <th style="min-width: 80px;">71/90</th>
                      <th style="min-width: 80px;">91/110</th>
                      <th style="min-width: 80px;">100/150</th>
                      <th style="min-width: 80px;">150/200</th>
                      <th style="min-width: 80px;">200/250</th>
          
                      <th style="min-width:80px;">Total</th>
                      <th style="min-width:80px;">Payment</th>
          
                      <!-- <th style="min-width:90px;">Lab Sample</th>
                      <th style="min-width:150px;">Buy Sample PSIP</th> -->
                      
          
                  </tr>
              </thead>
          
              <tbody>';

                      foreach($resList as $item) { 
                       
               	
                  $Content.='<tr>
                      <td style="vertical-align: middle">'. $item->Sno  .'</td>
                      <td style="vertical-align: middle">'. $item->ConsigneeName  .'</td>
                      <td style="vertical-align: middle">'. $item->PortOfDischarge  .'</td>
                      <td style="vertical-align: middle">'. $item->ShipByDate  .'</td>
                      <td style="vertical-align: middle">'. $item->PoNO  .'</td>
                      <td style="vertical-align: middle">'. $item->ExpProc  .'</td>
          
                      <td style="padding: 0px !important;" colspan="23">
                          <table class="subTable" style=""> ';
                         foreach($item->BreakUpData as $itemBreak) {  
                             $Content.='<tr>
                                  <td style="min-width: 160px;">'. $itemBreak->Brand  .'&nbsp;</td>
                                  <td style="min-width: 160px;">'. $itemBreak->Certification .'&nbsp;</td>
                                  <td style="min-width: 110px;">'. $itemBreak->Variety  .'&nbsp;</td>
                                  <td style="min-width: 110px;">'. $itemBreak->Form  .'&nbsp;</td>
                                  <td style="min-width: 110px;">'.   $itemBreak->Packing  .'&nbsp;</td>
                                  <td style="min-width: 80px;">'.   $itemBreak->Glaze  .'&nbsp;</td>
                                  <td style="min-width: 80px;">';
                                      // echo $itemBreak->G1  
                                      $G1 = explode ("&", $itemBreak->G1); 
                                      $Content.= $G1[0];
                                       !empty($G1[1]) ?  $Content.='<span class="breakUpPrice" style="display: none;"> ('.$G1[1].')</span>' : "";
                                       $Content.='</td>';
                                  $Content.='<td style="min-width: 80px;">'; 
                                      $G2 = explode ("&", $itemBreak->G2); 
                                      $Content.= $G2[0];
                                       !empty($G2[1]) ? $Content.='<span class="breakUpPrice" style="display: none;"> ('.$G2[1].')</span>' : "";
                                       $Content.='</td>
                                  <td style="min-width: 80px;">';
                                      $G3 = explode ("&", $itemBreak->G3); 
                                      $Content.= $G3[0];
                                       !empty($G3[1]) ? $Content.='<span class="breakUpPrice" style="display: none;"> ('.$G3[1].')</span>' : "";
                                       $Content.='</td>';
                                       $Content.='<td style="min-width: 80px;">';
                                      $G4 = explode ("&", $itemBreak->G4); 
                                      $Content.= $G4[0];
                                       !empty($G4[1]) ? $Content.='<span class="breakUpPrice" style="display: none;"> ('.$G4[1].')</span>' : "";
                                       $Content.='</td>
                                  <td style="min-width: 80px;"> ';
                                  $G5 = explode ("&", $itemBreak->G5); 
                                  $Content.= $G5[0];
                                       !empty($G5[1]) ? $Content.='<span class="breakUpPrice" style="display: none;"> ('.$G5[1].')</span>' : "";
                                       $Content.='</td>
                                       <td style="min-width: 80px;">';  
                                      $G6 = explode ("&", $itemBreak->G6); 
                                      $Content.= $G6[0];
                                       !empty($G6[1]) ? $Content.='<span class="breakUpPrice" style="display: none;"> ('.$G6[1].')</span>' : "";
                                       $Content.='</td>
                                  <td style="min-width: 80px;">';
                                      $G7 = explode ("&", $itemBreak->G7); 
                                      $Content.= $G7[0];
                                       !empty($G7[1]) ? $Content.='<span class="breakUpPrice" style="display: none;"> ('.$G7[1].')</span>' : "";
                                      $Content.='</td>
                                  <td style="min-width: 80px;">'; 
                                      $G8 = explode ("&", $itemBreak->G8); 
                                      $Content.= $G8[0];
                                      $Content.= !empty($G8[1]) ? '<span class="breakUpPrice" style="display: none;"> ('.$G8[1].')</span>' : "";
                                      $Content.='</td>
                                  <td style="min-width: 80px;">';  
                                      $G9 = explode ("&", $itemBreak->G9); 
                                      $Content.= $G9[0];
                                      $Content.= !empty($G9[1]) ? '<span class="breakUpPrice" style="display: none;"> ('.$G9[1].')</span>' : "";
                                      $Content.='</td>
                                  <td style="min-width: 80px;">'; 
                                      $G10 = explode ("&", $itemBreak->G10); 
                                      $Content.= $G10[0];
                                      $Content.= !empty($G10[1]) ? '<span class="breakUpPrice" style="display: none;"> ('.$G10[1].')</span>' : "";
                                      $Content.='</td>
                                  <td style="min-width: 80px;">';
                                      $G11 = explode ("&", $itemBreak->G11); 
                                      $Content.= $G11[0];
                                      $Content.= !empty($G11[1]) ? '<span class="breakUpPrice" style="display: none;"> ('.$G11[1].')</span>': "";
                                      $Content.='</td>
                                  <td style="min-width: 80px;">'; 
                                      $G12 = explode ("&", $itemBreak->G12); 
                                      $Content.= $G12[0];
                                      $Content.= !empty($G12[1]) ? '<span class="breakUpPrice" style="display: none;"> ('.$G12[1].')</span>' : "";
                                      $Content.='</td>
                                  <td style="min-width: 80px;">';
                                      $G13 = explode ("&", $itemBreak->G13); 
                                      $Content.= $G13[0];
                                      $Content.= !empty($G13[1]) ? '<span class="breakUpPrice" style="display: none;"> ('.$G13[1].')</span>' : "";
                                      $Content.='</td>
                                  <td style="min-width: 80px;">';
                                      $G14 = explode ("&", $itemBreak->G14); 
                                      $Content.= $G14[0];
                                      $Content.= !empty($G14[1]) ? '<span class="breakUpPrice" style="display: none;"> ('.$G14[1].')</span>' : "";
                                      $Content.='</td>
                                  <td style="min-width: 80px;">';  
                                      $G15 = explode ("&", $itemBreak->G15); 
                                      $Content.= $G15[0];
                                      $Content.= !empty($G15[1]) ? '<span class="breakUpPrice" style="display: none;"> ('.$G15[1].')</span>' : "";
                                      $Content.='</td>
                                  <td style="min-width: 80px;">';  
                                      $G16 = explode ("&", $itemBreak->G16); 
                                      $Content.= $G16[0];
                                      $Content.= !empty($G16[1]) ? '<span class="breakUpPrice" style="display: none;"> ('.$G16[1].')</span>' : "";
                                      $Content.='</td>
                                  <td style="min-width: 80px;">'.$itemBreak->Total .'</td>
                                      
                              
                                      </tr>';
                               } 
                              $Content.= '</table>
                      </td>
                      
                     <!-- <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>
                      <td style="display: none;"></td>-->
                      
              
                      <td style="vertical-align: middle">'. $item->Payment  .'</td>
                  
                      <!-- <td><?php // echo $item->LabSample  ?></td>
                      <td><?php // echo $item->BuyerSample  ?></td> -->
                  </tr>';
                  
                      }
                 
          
                      $Content.=' </tbody>';
              
              $Content.='</table>
			<br>
			<br>';
			 
		 
			
			
/*			
$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];*/
 
//$filename=$InvShortCode.'-AZ_SD-'.$CInvNo.'-'.$FinancialYear.'.xls';
$filename='PendingContracts.xls';

header("Content-Type: application/vnd.ms-excel;base64");
header('Content-Disposition: attachment;filename='.$filename.'');
header("Pragma: ");
header("Cache-Control: ");

			
echo "<html><head><style>
table, th, td {
  border: 1px solid black;
}
</style></head>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
echo '<body style="border: 1px solid #ccc">';
echo $Content;
echo "</body>";
echo "</html>";

?>