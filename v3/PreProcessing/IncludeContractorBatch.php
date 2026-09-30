	
				<div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Select Station<span style="color:red">*</span>  </label>
				<select required class="form-control select2" name="StationID" id="StationID">
				  <option value=""> Select</option>
				<?php while($RowDeheadingSheds=$StmtDeheadingSheds->fetch_array()) {	?>
				<option 
				<?php if($RowDeheadingSheds['StationID']==$RowRmLots["StationID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowDeheadingSheds['StationID'];?>">
				<?php echo $RowDeheadingSheds['UnitName'];?></OPTION>
				<?php } ?>
				</select>
				  
			   </div>
			<!--   <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Supplier Type<span style="color:red">*</span></label>
				<select required class="form-control select2" name="Type" id="Type">
				 <option value=""> Select</option>
				 <option value="1">Contractor</option>
				 <option value="2">Mastri</option>
				 </select>
				 </div>
				
			 
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Contractor/Mastri<span style="color:red">*</span> </label>
				<select required class="form-control select2" name="ContractorID" id="ContractorID">
				 <option value=""> Select</option>
				<?php /*while($RowContractor=$StmtContractor->fetch_array()) {	?>
				<option 
				<?php if($RowContractor['ContractorID']==$RowRmLots["ContractorID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowContractor['ContractorID'];?>">
				<?php echo $RowContractor['ContrctrName'];?></OPTION>
				<?php } */?>
				</select>
				  
			   </div>
			   
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Batch<span style="color:red">*</span></label>
				<select required class="form-control select2" name="BatchID" id="BatchID">
				 
				</select>
				  
			   </div>-->
			   
			   
			   
			   