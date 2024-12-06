<?php
require_once('./config.php');
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $qry = $conn->query("SELECT * from `booking_list` where id = '{$_GET['id']}' ");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
    }
}
?>
<div class="container-fluid">
    <form action="" id="booking-form">
        <input type="hidden" name="id" value="<?= isset($id) ? $id : '' ?>">
        <input type="hidden" name="facility_id" value="<?= isset($_GET['fid']) ? $_GET['fid'] : (isset($facility_id) ? $facility_id : "") ?>">
        
        <div class="form-group">
            <label for="date_from" class="control-label">From Date</label>
            <input name="date_from" id="date_from" type="date" class="form-control form-control-sm rounded-0" value="<?= isset($date_from) ? $date_from : '' ?>" required />
        </div>
        
        <div class="form-group">
            <label for="date_to" class="control-label">To Date</label>
            <input name="date_to" id="date_to" type="date" class="form-control form-control-sm rounded-0" value="<?= isset($date_to) ? $date_to : '' ?>" required />
        </div>

        <!-- Time From Field -->
        <div class="form-group">
            <label for="time_from" class="control-label">From Time</label>
            <input name="time_from" id="time_from" type="time" class="form-control form-control-sm rounded-0" value="<?= isset($time_from) ? $time_from : '' ?>" required />
        </div>

        <!-- Time To Field -->
        <div class="form-group">
            <label for="time_to" class="control-label">To Time</label>
            <input name="time_to" id="time_to" type="time" class="form-control form-control-sm rounded-0" value="<?= isset($time_to) ? $time_to : '' ?>" required />
        </div>
    </form>
</div>
<script>
    $(document).ready(function(){
        $('#booking-form').submit(function(e){
            e.preventDefault();
            var _this = $(this);
            $('.err-msg').remove();
            start_loader();  // Start loading animation
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=save_booking",
                data: new FormData($(this)[0]), // Collects all form data including time_from and time_to
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error: err => {
                    console.log(err);
                    alert_toast("An error occurred", 'error');
                    end_loader();  // End loading animation
                },
                success: function(resp) {
                    if (typeof resp === 'object' && resp.status === 'success') {
                        location.href = './?p=booking_list'; // Redirects to booking list on success
                    } else if (resp.status === 'failed' && !!resp.msg) {
                        var el = $('<div>');
                        el.addClass("alert alert-danger err-msg").text(resp.msg);
                        _this.prepend(el);
                        el.show('slow');
                        end_loader();
                    } else {
                        alert_toast("An error occurred", 'error');
                        end_loader();
                        console.log(resp);
                    }
                    $("html, body, .modal").scrollTop(0); // Scroll to top
                }
            });
        });
    });
</script>
