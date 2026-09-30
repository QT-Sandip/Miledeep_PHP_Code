<?php
$count = $sNo = 1;
if (!empty($_POST['portIds'])) {
    
    $portIds = implode(',',$_POST['portIds']);
    include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
    $RowPortCities = [];
    /* $SqlScrmPortCities = "SELECT * FROM `ScrmPortCities`";
    $StmtPortCities = $iconn->query($SqlScrmPortCities);
    while ($PortCities = $StmtPortCities->fetch_assoc()) {
        $RowPortCities[] = $PortCities;
    } */
    $count = count($_POST['portIds']);
    $oldDeleteData = legacyAuditFetchRowById('ScrmFreightRatesBreakUp', 'ParentID', $_POST['ParentID'], 'Sales');
    $DeleteSql = "DELETE FROM `ScrmFreightRatesBreakUp` WHERE `BreakupID` IN(
                        SELECT `BreakupID` FROM `ScrmFreightRatesBreakUp` WHERE `DestinationPortID` NOT IN($portIds) AND `ParentID`= ".$_POST['ParentID']."
                    )";
    $StmtDelete = $iconn->query($DeleteSql);
    if($StmtDelete){
        legacyAuditLogDeleteById('ScrmFreightRatesBreakUp', 'ParentID', $_POST['ParentID'], $oldDeleteData, $_POST, array(), 'Sales');
    }
    if(isset($_POST['Country'])){
        $portQry = "SELECT `DestinationPort`,Country FROM `ScrmFreightRates` WHERE `ID`= ".$_POST['ParentID'];
        $StmtportQry = $iconn->query($portQry);
        $portRes = $StmtportQry->fetch_assoc();
        if(!empty($portRes)){
            $DestinationPort = explode(',',$portRes['DestinationPort']);
            $Country = explode(',',$portRes['Country']);
            $difPort = implode(',',array_intersect($DestinationPort,$_POST['portIds']));            
            if(is_array($_POST['Country'])){
                $difCountry = implode(',',array_intersect($Country,$_POST['Country']));
            }else{
                $postArr[] = $_POST['Country'];
                $difCountry = implode(',',array_intersect($Country,$postArr));
            }
            
             $oldFreightData = legacyAuditFetchRowById('ScrmFreightRates', 'ID', $_POST['ParentID'], 'Sales');
             $updateSql = "UPDATE ScrmFreightRates SET DestinationPort='$difPort',Country='$difCountry' WHERE ID=".$_POST['ParentID'];
            $StmtUpdate=$iconn->query($updateSql);
            if($StmtUpdate){
                legacyAuditLogUpdateById('ScrmFreightRates', 'ID', $_POST['ParentID'], $oldFreightData, $_POST, array(), 'Sales');
            }
           // echo '<pre>';print_r($difPort);die;
        }
    }
}
$n = 0;
for ($i = 0; $i < $count; $i++) {
    $FreightRatesBrkUpResults = [];
    if (!empty($_POST['ParentID'])) {
        $SqlScrmFreightBrkUpQry = "SELECT * FROM ScrmFreightRatesBreakUp WHERE DestinationPortID=" . $_POST['portIds'][$i] . " AND ParentID=" . $_POST['ParentID'];
        $StmtPortCities = $iconn->query($SqlScrmFreightBrkUpQry);
        $FreightRatesBrkUpResults = $StmtPortCities->fetch_assoc();
    }
    $SqlScrmPortCities = "SELECT PortId,Name FROM `ScrmPortCities` WHERE PortId =".$_POST['portIds'][$i];
    $StmtPortCities = $iconn->query($SqlScrmPortCities);
     $RowPortCities = $StmtPortCities->fetch_assoc();
    /* while ($PortCities = $StmtPortCities->fetch_assoc()) {
        $RowPortCities[] = $PortCities;
    } */
    if (!empty($_POST['port_ids'])) {        
        /* added this condition for when we multy select port the port prev enter data is erasing */
        //for ($k = 0; $k < count($_POST['port_ids']); $k++) {
            if (in_array($_POST['portIds'][$i], $_POST['port_ids'])) {
                $key = array_search($_POST['portIds'][$i],$_POST['port_ids']);
                $FreightRatesBrkUpResults['DestinationPortID'] = $_POST['port_ids'][$key];
                $FreightRatesBrkUpResults['Rate'] = $_POST['Rate'][$key];
               // $FreightRatesBrkUpResults['RateType'] = $_POST['RateType'][$key];
               // $FreightRatesBrkUpResults['Combined'] = $_POST['Combined'][$key];
                $FreightRatesBrkUpResults['DFD1'] = !empty($_POST['BDfd1'][$key])?$_POST['BDfd1'][$key]:$_POST['Dfd1'];
                $FreightRatesBrkUpResults['DFD2'] = !empty($_POST['BDfd2'][$key])?$_POST['BDfd2'][$key]:$_POST['Dfd2'];
                $FreightRatesBrkUpResults['ContryCode'] = $_POST['ContryCode'][$key];
                $FreightRatesBrkUpResults['Remarks'] = $_POST['Remarks'][$key];
                $FreightRatesBrkUpResults['Via'] = $_POST['Via'][$key];
               // $n++; //1
               // break;
            }
       // }
        // echo '<pre>';print_r($FreightRatesBrkUpResults);die;
    }?>
    <h5 class="mb-0">Sno. <?php echo $sNo; ?></h5>
    <div class="card <?php echo !empty($_POST['portIds'][$i])?'portId_'.$_POST['portIds'][$i]:'';?>" id="frightRatesBreakup<?php echo $sNo; ?>">
        <div class="card-body">
            <div class="row">
                <div class="form-group col-sm-3">
                    <input type="hidden" name="BreakupID[<?php echo $sNo; ?>]" value="<?php echo !empty($FreightRatesBrkUpResults['BreakupID']) ? $FreightRatesBrkUpResults['BreakupID'] : ''; ?>">
                    <label for="port_id">Destination Port<span
                            style="color:red">*</span></label>
                    <!-- <select required class="form-control select2 port_ids" name="port_id[<?php echo $sNo; ?>]" id="port_id<?php echo $sNo; ?>">
                        <?php
                        /* if (!empty($RowPortCities)) {
                            foreach ($RowPortCities as $val) { ?>
                                <?php if (!empty($_POST['portIds']) && $_POST['portIds'][$i] == $val['PortId']) { ?>
                                    <option <?php if ((!empty($_POST['portIds']) && $_POST['portIds'][$i] == $val['PortId']) || (!empty($FreightRatesBrkUpResults['DestinationPortID']) && $FreightRatesBrkUpResults['DestinationPortID'] == $val['PortId'])) { ?> selected="selected" <?php } ?>
                                        value="<?php echo $val['PortId']; ?>">
                                        <?php echo $val['Name']; ?>
                                    </option>
                                <?php break;
                                } ?>
                        <?php }
                        } */ ?>

                    </select> -->
                    <input type="text" class="form-control" disabled value="<?php echo $RowPortCities['Name'];?>" >
                    <input type="hidden" class="port_ids" name="port_id[<?php echo $sNo; ?>]" value="<?php echo $RowPortCities['PortId'];?>"  id="port_id<?php echo $sNo; ?>">
                </div>
                <!-- <div class="form-group col-sm-3">
                    <label for="RateType">Rate Type <span
                            style="color:red">*</span></label>
                    <select required class="form-control RateType" name="RateType[<?php echo $sNo; ?>]" id="RateType<?php echo $sNo; ?>">
                        <option value="1" <?php if (isset($FreightRatesBrkUpResults['RateType']) && $FreightRatesBrkUpResults['RateType'] == 1)
                                                echo 'selected'; ?>>Contract</option>
                        <option value="0" <?php if (isset($FreightRatesBrkUpResults['RateType']) && $FreightRatesBrkUpResults['RateType'] == 0)
                                                echo 'selected'; ?>>Spot</option>
                    </select>
                </div> -->
                <div class="form-group col-sm-3">
                    <label for="Rate"> Rate <span style="color:red">*</span></label>
                    <input required type="text" class="form-control Rate" name="Rate[<?php echo $sNo; ?>]" id="Rate<?php echo $sNo; ?>"
                        placeholder="Enter Rate"
                        value="<?php echo !empty($FreightRatesBrkUpResults['Rate']) ? $FreightRatesBrkUpResults['Rate'] : ''; ?>" />
                </div>
                <!-- <div class="form-group col-sm-3">
                    <label for="Combined ">Combined <span style="color:red">*</span></label>
                    <select required class="form-control Combined" name="Combined[<?php echo $sNo; ?>]" id="Combined<?php echo $sNo; ?>">
                        <option value="1" <?php if (isset($FreightRatesBrkUpResults['Combined']) && $FreightRatesBrkUpResults['Combined'] == 1)
                                                echo 'selected'; ?>>Yes</option>
                        <option value="0" <?php if (isset($FreightRatesBrkUpResults['Combined']) && $FreightRatesBrkUpResults['Combined'] == 0)
                                                echo 'selected'; ?>>No</option>
                    </select>
                </div>-->
                <div class="form-group col-sm-3">
                    <label for="BDfd1<?php echo $sNo; ?>">Dfd1 <span style="color:red"></span></label>
                    <input  type="number" class="form-control BDfd1" name="BDfd1[<?php echo $sNo; ?>]" id="BDfd1<?php echo $sNo; ?>"
                        placeholder="Enter Dfd1"
                        value="<?php echo !empty($FreightRatesBrkUpResults['DFD1']) ? $FreightRatesBrkUpResults['DFD1']: (!empty($_POST['Dfd1'])?$_POST['Dfd1'] : ''); ?>" />
                </div>

                <div class="form-group col-sm-3">
                    <label for="BDfd2<?php echo $sNo; ?>">Dfd2 <span style="color:red"></span></label>
                    <input type="number" class="form-control BDfd2" name="BDfd2[<?php echo $sNo; ?>]" id="BDfd2<?php echo $sNo; ?>"
                        placeholder="Enter Dfd2"
                        value="<?php echo !empty($FreightRatesBrkUpResults['DFD2']) ? $FreightRatesBrkUpResults['DFD2'] : (!empty($_POST['Dfd2'])?$_POST['Dfd2'] : ''); ?>" <?php echo !empty($_POST['Combined'])?'readonly':'';?>/>
                </div> 
                <div class="form-group col-sm-3">
                    <label for="ContryCode">Country Code <span style="color:red"></span></label>
                    <input type="text" class="form-control ContryCode" name="ContryCode[<?php echo $sNo; ?>]" id="ContryCode<?php echo $sNo; ?>"
                        placeholder="Enter ContryCode"
                        value="<?php echo !empty($FreightRatesBrkUpResults['ContryCode']) ? $FreightRatesBrkUpResults['ContryCode'] : ''; ?>" />
                </div> 
                <div class="form-group col-sm-3">
                    <label for="Via">Via <span style="color:red"></span></label>
                    <input type="text" class="form-control Via" name="Via[<?php echo $sNo; ?>]" id="Via<?php echo $sNo; ?>"
                        placeholder="Enter Via"
                        value="<?php echo !empty($FreightRatesBrkUpResults['Via']) ? $FreightRatesBrkUpResults['Via'] : ''; ?>" />
                </div>
                <div class="form-group col-sm-3">
                    <label for="Remarks">Remarks <span style="color:red"></span></label>
                    <input type="text" class="form-control Remarks" name="Remarks[<?php echo $sNo; ?>]" id="Remarks<?php echo $sNo; ?>"
                        placeholder="Enter Remarks"
                        value="<?php echo !empty($FreightRatesBrkUpResults['Remarks']) ? $FreightRatesBrkUpResults['Remarks'] : ''; ?>" />
                </div> 
            </div>
        </div>
    </div>
<?php $sNo++;
} ?>
<hr>
