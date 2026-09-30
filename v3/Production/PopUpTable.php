
<html lang="en">
<head>
<meta charset="utf-8">
<title>Resizing the Bootstrap Modal</title>
<link rel="stylesheet" href="css/bootstrap.min.css">
<script src="js/jquery-3.5.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<style>
    @media screen and (min-width: 676px) {
        .modal-dialog {
          max-width: 600px; /* New width for default modal */
        }
    }
</style>
</head>
<body>


<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">PO Status Details</h4>
      </div>

	  <div class="modal-body">
	
		<div class="row">
	  
		<div class="col-md-6">	
		<table id="PopUpTable" class="table table-bordered ">
		</table>
		</div>
		</div>
      </div>
	  
	  <div class="modal-footer">
	
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>