<?php if($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php endif; ?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Rates Management</h3>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <!-- Add Rate Button -->
            <button class="btn btn-primary mb-3" id="addRateBtn" data-toggle="modal" data-target="#rateModal">Add Rate</button>

            <!-- Table for Rates -->
            <table class="table table-bordered table-striped" id="ratesTable">
                <colgroup>
                    <col width="5%">
                    <col width="50%">
                    <col width="30%">
                    <col width="15%">
                </colgroup>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Activity Classification</th>
                        <th>Rate per Hour</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i = 1;
                    $qry = $conn->query("SELECT * FROM `rates` ORDER BY activity_class ASC");
                    while ($row = $qry->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?php echo $i++; ?></td>
                        <td><?php echo ucwords($row['activity_class']); ?></td>
                        <td><?php echo "₱" . number_format($row['rate_per_hour'], 2); ?></td>
                        <td align="center">
                            <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                Action
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <div class="dropdown-menu" role="menu">
                                <a class="dropdown-item" href="javascript:void(0)" data-id="<?php echo $row['id']; ?>"><span class="fa fa-edit text-primary"></span> Edit</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id']; ?>"><span class="fa fa-trash text-danger"></span> Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Rate Modal -->
<div class="modal fade" id="rateModal" tabindex="-1" role="dialog" aria-labelledby="rateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rateModalLabel">Add New Rate</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="rateForm">
                    <div class="form-group">
                        <label for="activity_class">Activity Classification</label>
                        <input type="text" class="form-control" id="activity_class" name="activity_class" required>
                    </div>
                    <div class="form-group">
                        <label for="rate_per_hour">Rate per Hour</label>
                        <input type="number" class="form-control" id="rate_per_hour" name="rate_per_hour" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Rate</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#ratesTable').dataTable();
    // Handle form submission for adding a rate
    $('#rateForm').submit(function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Get the form data
        var formData = $(this).serialize(); // Serialize the form data

        // Send the data via AJAX to Master.php
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=save_rate", // Update the URL to point to Master.php
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    alert_toast("Rate added successfully!", 'success');
                    $('#rateModal').modal('hide'); // Hide the modal
                    location.reload(); // Reload the page to show the new rate in the table
                } else {
                    alert_toast(response.msg || "Failed to add rate.", 'error');
                }
            },
            error: function() {
                alert_toast("An error occurred.", 'error');
            }
        });
    });
});

</script>


