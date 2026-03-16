<div class="modal fade" id="leaveConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Leave Waiting List</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to leave this waiting list?</p>
                <p>If you re-join in the future, you will be placed at the <strong>back of the queue</strong>.</p>
            </div>
            <div class="modal-footer">
                <form class="form-horizontal" role="form" method="POST" id="leaveConfirmForm">
                    {{ csrf_field() }}
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Leave Waiting List</button>
                </form>
            </div>
        </div>
    </div>
</div>