<!-- Add -->
<div class="modal fade" id="addnewholiday">
    <div class="modal-dialog">
        <div class="modal-content">
          	<div class="modal-header">
            	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
              		<span aria-hidden="true">&times;</span></button>
            	<h4 class="modal-title"><b>Add Holiday</b></h4>
          	</div>
          	<div class="modal-body">
            	<form class="form-horizontal" method="POST" action="Ajax/AjDeptDetails.php">
          		  <div class="form-group">
                  	<label for="employee" class="col-sm-3 control-label"> Name</label>
					
					

                  	<div class="col-sm-9">
                    	<input type="text" class="form-control" id="holiday_name" name="holiday_name" required>
                  	</div>
                </div>
                 <div class="form-group">

                    <label for="datepicker_add" class="col-sm-3 control-label">Date</label>



                    <div class="col-sm-9"> 

                      <div class="date">

                        <input type="date" class="form-control" id="holiday_date" name="holiday_date"  autocomplete="off" required>

                      </div>

                    </div>

                </div>

				
				
          	</div>
          	<div class="modal-footer">
            	<button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
            	<button  class="btn btn-primary btn-flat" id="addholiday"><i class="fa fa-save"></i> Save</button>
            	</form>
          	</div>
        </div>
    </div>
</div>

<!-- Edit -->
<div class="modal fade" id="editholiday">
    <div class="modal-dialog">
        <div class="modal-content">
          	<div class="modal-header">
            	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
              		<span aria-hidden="true">&times;</span></button>
            	<h4 class="modal-title"><b><span id="employee_name"></span></b></h4>
          	</div>
          	<div class="modal-body">
            	<form class="form-horizontal" method="POST" action="Ajax/AjDeptDetails.php">
            		<input type="hidden" id="hol_id" name="id">
                <div class="form-group">
                  	<label for="employee" class="col-sm-3 control-label"> Name</label>
					
					

                  	<div class="col-sm-9">
                    	<input autocomplete='off' type="text" class="form-control" id="holidays_Name" name="holidays_Name" required>
                  	</div>
                </div> 
				<div class="form-group">

                    <label for="datepicker_add" class="col-sm-3 control-label">Date</label>



                    <div class="col-sm-9"> 

                      <div class="date">

                        <input type="date" class="form-control" id="holidays_date" name="holidays_date" required>

                      </div>

                    </div>

                </div>

				
                
				
				
          	</div>
          	<div class="modal-footer">
            	<button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
            	<button  class="btn btn-success btn-flat" id="Updateholiday"><i class="fa fa-check-square-o"></i> Update</button>
            	</form>
          	</div>
        </div>
    </div>
</div>

<!-- Delete -->
<div class="modal fade" id="deleteholiday">
    <div class="modal-dialog">
        <div class="modal-content">
          	<div class="modal-header">
            	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
              		<span aria-hidden="true">&times;</span></button>
            	<h4 class="modal-title"><b><span id="attendance_date"></span></b></h4>
          	</div>
          	<div class="modal-body">
            	<form class="form-horizontal" method="POST" action="Ajax/AjDeptDetails.php">
            		<input type="hidden" id="del_attid" name="id">
            		<div class="text-center">
	                	<p>Are you sure you want to delete this?</p>
	                	<h2 id="del_employee_name" class="bold"></h2>
	            	</div>
          	</div>
          	<div class="modal-footer">
            	<button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
            	<button  class="btn btn-danger btn-flat" id="Deleteholiday"><i class="fa fa-trash"></i> Delete</button>
            	</form>
          	</div>
        </div>
    </div>
</div>


     