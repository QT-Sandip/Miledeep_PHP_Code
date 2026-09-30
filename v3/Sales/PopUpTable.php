<style>
  @media (min-width: 992px) {

    .modal-lg,
    .modal-xl {
      max-width: 1230px;
      max-height: 210px;
    }
  }

  /* Custom media query for tablets */
  @media (min-width: 576px) and (max-width: 992px) {
    .modal-dialog {
      max-width: 80%;
      /* Adjust modal width for tablets */
    }
  }
</style>
<div id="myModal" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog modal-lg modal-xl">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">PO Status Details</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">



        <div id="PopUpTable" class="col-md-12 scrollPopup">

        </div>

      </div>

      <div class="modal-footer">

        <button type="button" id='Update' value="" class="btn btn-info">Update</button>
        <button type="button" id="btnModalClose" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<style>
  /* .scrollPopup .dataTables_wrapper .row:nth-child(2){
     overflow-y: auto;
    max-height: 380px; 
  } */
  /* .dataTables_scrollHeadInner{
  width:100% !important;
}
.dataTables_scrollHeadInner table{
  width:100% !important;
} */
</style>