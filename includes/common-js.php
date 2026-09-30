<!-- JAVASCRIPT -->
<script src="<?php echo ROOT_URL ?>assets/libs/jquery/jquery.min.js"></script>
<script src="<?php echo ROOT_URL ?>assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo ROOT_URL ?>assets/libs/metismenu/metisMenu.min.js"></script>
<script src="<?php echo ROOT_URL ?>assets/libs/simplebar/simplebar.min.js"></script>
<script src="<?php echo ROOT_URL ?>assets/libs/node-waves/waves.min.js"></script>

<script src="<?php echo ROOT_URL ?>assets/libs/select2/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>

<script>
    $("#btnNotify,#btnNotifyWeb").on("click", function() {
        $.ajax({
            url: '../../includes/AjaxNotify.php',
            type: 'POST',            
            success: function (data) {
                $("#accNotifyPanel").html(data);
                $('#modalNotify').modal('show');
                return false;
            }
        });

        
    });
    $(".btnNotifyClose").on("click", function() {
        $('#modalNotify').modal('hide');
    })

    // $(document).ready(function() {

        $(document).on("click", ".acc-header", function() {            
            var $this = $(this); // Cache for efficiency
            var $panel = $this.next(".acc-content");

            if ($panel.is(":visible")) { // If the panel is already open
                $this.parent(".acc-item").removeClass("active"); // Remove "active" class
            } else { // If the panel is closed
                $this.parent(".acc-item").addClass("active").siblings().removeClass("active"); // Handle "active" class
            }


            $(this).next(".acc-content").slideToggle();

            // Toggle icon for the clicked header
            $(this).find("i").toggleClass("fas fa-minus fas fa-plus"); /* Toggle icons */

            // Reset icons for other headers
            $(this).parent(".acc-item").siblings().find(".acc-header i").removeClass("fas fa-minus").addClass("fas fa-plus");

            $(this).parent(".acc-item").siblings().find(".acc-content").slideUp();
        });

    // });
</script>
