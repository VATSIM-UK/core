<p>
    <?php if (isset($sls_type) && $sls_type == "forced"): ?>
        An administrator has requested that you create a second password before continuing with your login.  You will not be able to use any services until this action has been completed.
    <?php elseif (isset($sls_type) && $sls_type == "requested"): ?>
        You have requested to set a secondary password on your account.  Please complete the form below to complete this request.
    <?php elseif (isset($sls_type) && $sls_type == "expired"): ?>
        Your previous secondary password has expired, please enter your old password along with a new one below.  Remember though, they must be different!
    <?php else: ?>
        To change your secondary password, complete the form below.
    <?php endif; ?>
</p>

<?php if (isset($_requirements)): ?>
    <div class="alert alert-warning">
        <strong>Please note, your password must contain:</strong>
        <ul>
            <?php foreach ($_requirements as $r): ?>
                &nbsp;<strong>-</strong>&nbsp;&nbsp;<?= $r ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-7 col-md-offset-2">
        <form class="form-horizontal" method="POST" action="<?= URL::site("/mship/security/replace") ?>" role="form">
            <?php if (isset($sls_type) && ($sls_type == "change")): ?>
                <div class="form-group">
                    <label class="col-sm-5 control-label" for="old_password">Old Password</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="password" id="old_password" name="old_password" placeholder="Old Password">
                        <span class="help-block col-md-offset-1">May be a temporary password</span>
                    </div>
                </div>
            <?php endif; ?>
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
            <div class="form-group">
                <div class="col-sm-offset-5 col-sm-7">
                    <button type="submit" class="btn btn-default" name="processextra_security_replace" value="extra_security_replace">Proceed</button>
                </div>
            </div>
        </form>
    </div>
</div>