<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <?php echo   $CopyRight ?>
            </div>
            <div class="col-sm-6">
                <div class="text-sm-right d-none d-sm-block">
                    Version <i class="mdi mdi-info text-danger"></i> <?php echo $AppVersion ?>
                </div>
            </div>
        </div>
    </div>
</footer>
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
.acc-item .table tr:hover {
    background: none !important;
}
.acc-item .table tr:hover td, 
.acc-item .table tfoot tr:hover th {
    color: #000000 !important;
}
.acc-item .table td, .acc-item .table th {
    padding: 0.5rem 0.5rem;
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
<div class="modal fade" id="modalNotify"  tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content"  style="max-width: 1140px;">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Notifications</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <div id="accNotifyPanel" class="acc">
                        <!-- <div class="acc-item">
                            <button class="acc-header">
                            <i class="fas fa-plus"></i> Section 1
                            </button>
                            <div class="acc-content">
                            Content of Section 1
                            </div>
                        </div>
                        <div class="acc-item">
                            <button class="acc-header">
                            <i class="fas fa-plus"></i> Section 2
                            </button>
                            <div class="acc-content">
                            Content of Section 2
                            </div>
                        </div>
                        <div class="acc-item">
                            <button class="acc-header">
                            <i class="fas fa-plus"></i> Section 2
                            </button>
                            <div class="acc-content">
                            Content of Section 2
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script> -->


<!--Start of Tawk.to Script-->
<!-- <script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/6405ab784247f20fefe43733/1gqr2bkjt';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script> -->
<!--End of Tawk.to Script-->