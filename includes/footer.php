<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <script>
                document.write(new Date().getFullYear())
                </script> © Camaro. -- Version-3.0.0
            </div>
            <!-- <div class="col-sm-6">
                <div class="text-sm-end d-none d-sm-block">
                    Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesdesign
                </div>
            </div> -->
        </div>
    </div>
</footer>


<!-- Modal -->
<div class="modal fade" id="modalConfirmation" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmationLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="modalDesc"></p>
                <input type="hidden" id="hdnSelectedData" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger waves-effect" data-bs-dismiss="modal"
                    id="btnModalCancel">Cancel</button>
                <button type="button" class="btn btn-primary waves-effect waves-light"
                    id="btnModalContinue">Continue</button>
            </div>
        </div>
    </div>
</div>
<style>
.acc {
  width: 100%;
}

.acc-item {
  border-bottom: 1px solid #ccc;
}

.acc-header {    
    background-color: #FFF;
    cursor: pointer;
    border: 0px;
    padding: 10px 15px;
    width: 100%;
    text-align: left;
}

.acc-header i {
  float: right; /* Position the icon on the right */
  margin-left: 10px; /* Add spacing between text and icon */
  font-size: 12px;
}

.acc-content {
  display: none;
  padding: 15px;
}
.acc-item.active .acc-header{
    background-color: #f1f5f7;
}
.acc-item .table td, 
.acc-item .table th{
    border-top: 1px solid #eff2f7; 
}
.acc{
    display: block;
    height: 75vh;
    overflow-y: auto;  
}
.acc-content .table tr:hover{
    color: #000000;
}
</style>
<!-- Notify -->
<div class="modal fade" id="modalNotify" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 1140px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Notifications</h5>
                <button type="button" class="close btn-close btnNotifyClose" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body" >
                <div>
                    <div id="accNotifyPanel" class="acc">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btnNotifyClose">Close</button>
            </div>
        </div>
    </div>
</div>