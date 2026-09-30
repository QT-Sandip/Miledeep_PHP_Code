<style>
     @media (min-width: 992px) {
        /* .col-lg-2 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 16.66667%;
            flex: 0 0 16.66667%;
            max-width: 16.66667%;
        } */
        .col-lg-1-5 {
            flex: 0 0 12.5%;
            /* 1.5 columns = (1.5/12)*100 = 12.5% */
            max-width: 12.5%;
        }
    }
</style>
<?php 
$DefaultExporterID = $ClientConfigData->DefaultExporterID;
?>
<div class="col-12">
                                <div class="card filterCnt">
                                    <div class="card-body">
                                        
                                        <div class="row">				  
                                                    <div class="col-lg-2 col-sm-6">
                                                        <label>Exporter: </label>
                                                        <select class="form-control select2" name="drpExporter" id="drpExporter">
                                                            <option value=" " >All</option>
                                                            <?php 
                                                                $isFirstOption = false; // Flag to track the first option
                                                                while($row_add = $StmtExporter->fetch_array()) {
                                                                     $selected = !empty($DefaultExporterID) && ($DefaultExporterID == $row_add['ExpID']) ? 'selected' : '' ;  ?>  
                                                                    <option value="<?php echo $row_add['ExpID']; ?>" 
                                                                        <?php echo $selected ?>>
                                                                        <?php echo $row_add['ExpName']; ?>
                                                                    </option>
                                                            <?php } ?>
                                                        <select>
                                                    </div>		
                                                    		  
                                                    <div class="col-lg-2 col-sm-6">
                                                        <label>Consignee: </label>
                                                        <select class="form-control select2" name="Buyer" id="Buyer">
                                                            <option value=" " >All</option>
                                                            <?php while($RowConsignee=$StmtConsignee->fetch_array()) { ?>  
                                                                <option Value="<?php echo $RowConsignee['ConID'];?>"><?php echo $RowConsignee['ConsigneeName'];?></OPTION>
                                                            <?php } ?>
                                                        <select> 
                                                    </div>

                                                    <div class="col-lg-2 col-sm-6">
                                                        <label>Brand: </label>
                                                        <select class="form-control select2" name="Brand" id="Brand">
                                                            <option value=" " >All</option>
                                                            <?php while ($RowBrand = $StmtBrand->fetch_array()) { ?>  
                                                                     <option value="<?php echo $RowBrand['BrandName']; ?>"><?php echo $RowBrand['BrandName']; ?></OPTION> 
                                                            <?php  } ?>
                                                        <select> 
                                                    </div>
                                                        
                                                    <div class="col-lg-1-5 col-sm-6">
                                                        <label>Country: </label>
                                                        <select class="form-control select2" name="Country" id="Country">
                                                            <option value=" " >All</option>
                                                            <?php while($RowCountries=$StmtCountries->fetch_array()) { ?>  
                                                                <option value="<?php echo $RowCountries['id']; ?>"><?php echo $RowCountries['name']; ?></option>
                                                            <?php } ?>
                                                        <select> 
                                                    </div>

 
   
  
                                                    <!-- <div class="form-group col-lg-2 col-sm-6">
                                                        <label>Month: </label>
                                                        <select class="form-control select2" name="drpMonth" id="drpMonth">
                                                            <?php
                                                              print '<option value="" selected>Select month</option>';
                                                              for ( $i = 1; $i <= 12; $i ++ ) { 
                                                                  print '<option value="' . $i . '">' . date( 'F', strtotime( "$i/12/10" ) ) . '</option>'; 
                                                              }  
                                                                
                                                                ?>
                                                        <select>
                                                    </div> -->

                                                    <div class="form-group col-lg-1-5 col-sm-6">
                                                        <label>Year: </label>
                                                        <select class="form-control select2" name="Year" id="Year">
                                                            <option value="">Select Year</option>
                                                            <?php
                                                                for($Startyear; $Startyear <= $endYear; $Startyear++) { 
                                                                // $year=($Startyear).'-'.($Startyear+1); 
                                                                $year = ($Startyear - 1) . '-' . ($Startyear); //uma
                                                            ?>
                                                            <!-- <option <?php //echo $Startyear; if($Startyear == $FinClsYear){ ?> selected="selected" <?php //} ?>			
                                                            value="<?php //echo $Startyear ?>"><?php //echo $year ?></option> -->

                                                            <option <?php if ($Startyear == $FinClsYear) {
                                        //echo 'selected="selected"';
                                    } ?>
                                        value="<?php echo $Startyear - 1 ?>"> <?php echo $year ?></option>
                                                            <?php }   ?>
                                                        <select>
                                                    </div>
                                                    <div class="form-group  col-lg-1-5 col-sm-6 mb-2">
                                                        <label>From Date</label>
                                                        <input type="date" value="" class="form-control" name="fromDate" id="fromDate">
                                                    </div>

                                                    <div class="form-group  col-lg-1-5 col-sm-6 mb-2">
                                                        <label>To Date</label>
                                                        <input type="date" value="" class="form-control" name="toDate" id="toDate">
                                                    </div>

                                                    
                                                    <div  class="col col-sm-12 text-center">   
                                                        <label style="display: block;" class="d-sm-none">&nbsp;</label>				  
                                                        <button id="list"  type="button" class="btn-filter btn btn-primary mb-2 waves-effect waves-light  text-left">
                                                            Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                                        </button>                                   
                                                    </div> 

                                                </div> 
                
                    
            </div>
                        
        </div>
        </div>