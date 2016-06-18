@extends('layout')

@section('content')

<?php if($disable): ?>
    <p>To disable your secondary password, please enter your current password below.</p>
<?php else: ?>
    <p>
        <?php if (isset($sls_type) && $sls_type == "forced"): ?>
            An administrator has requested that you create a second password before continuing.  You will not be able to use any services until this action has been completed.
        <?php elseif (isset($sls_type) && $sls_type == "requested"): ?>
            You have requested to set a secondary password on your account.  Please complete the form below to complete this request.
        <?php elseif (isset($sls_type) && $sls_type == "expired"): ?>
            Your previous secondary password has expired, please enter your old password along with a new one below.  Remember though, they must be different!
        <?php else: ?>
            To change your secondary password, complete the form below.
        <?php endif; ?>
    </p>

    <?php if (isset($requirements)): ?>
        <div class="alert alert-warning">
            <strong>Please note, your password must contain:</strong>
            <ul>
                <?php foreach ($requirements as $r): ?>
                    <li>&nbsp;&nbsp;<?= $r ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row">
    <div class="col-md-7 col-md-offset-2">
        {!! Form::open(["route" => ["mship.security.replace", $disable], "class" => "form-horizontal"]) !!}
            <?php if ((isset($sls_type) && ($sls_type == "replace" OR $sls_type == "expired" OR $sls_type == "forced")) OR $disable): ?>
                <div class="form-group">
                    <label class="col-sm-5 control-label" for="old_password">Old Password</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="password" id="old_password" name="old_password" placeholder="Old Password">
                        <span class="help-block col-md-offset-1">May be a temporary password</span>
                    </div>
                </div>
            <?php endif; ?>
            <?php if(!$disable): ?>
                <div class="form-group">
                    <label class="col-sm-5 control-label" for="new_password">New Password</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="password" id="new_password" name="new_password" placeholder="New Password">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-5 control-label" for="new_password2">Confirm New Password</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="password" id="new_password2" name="new_password2" placeholder="Confirm New Password">
                    </div>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <div class="col-sm-offset-5 col-sm-7">
                    <button type="submit" class="btn btn-default" name="processextra_security_replace" value="extra_security_replace">Proceed</button>
                    <a class="btn btn-default" href='#' onclick='javascript: checkResetPassword();'>Forgotten Password</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="resetConfirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Reset Secondary Password</h4>
            </div>
            <div class="modal-body">
                <p>Once you click this button, your old secondary password will be gone forever.  We'll then start the password recovery process for you - are you sure you wish to continue?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm" onclick="javascript: window.location.href ='{{ URL::route("mship.security.forgotten") }}';">Confirm</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Dialog show event handler -->
<script type="text/javascript">
    function checkResetPassword(){
        $('#resetConfirmModal').modal()
    }
</script>
@stop