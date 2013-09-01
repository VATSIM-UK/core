<p>
    <?php if (isset($sls_type) && $sls_type == "forced"): ?>
        An administrator has requested that you create a second password before continuing with your login.  You will not be able to use any services until this action has been completed.
    <?php else: ?>
        Your previous secondary password has expired, please enter your old password along with a new one below.  Remember though, they must be different!
    <?php endif; ?>
</p>

<?php if (isset($_requirements)): ?>
    <div class="alert alert-warning">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Please note, your password must:</strong>
        <ul>
            <?php foreach ($_requirements as $r): ?>
                <li><?= $r ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row-fluid">
    <div class="span6 offset2">
        <form class="form-horizontal form-login" method="POST" action="<?= URL::site("/sso/security/replace") ?>">
            <?php if (!isset($_newReg)): ?>
                <div class="control-group">
                    <label class="control-label" for="old_password">Old Password</label>
                    <div class="controls">
                        <input type="password" id="old_password" name="old_password" placeholder="Old Password">
                    </div>
                </div>
            <?php endif; ?>
            <div class="control-group">
                <label class="control-label" for="new_password">New Password</label>
                <div class="controls">
                    <input type="password" id="new_password" name="new_password" placeholder="New Password">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="new_password2">Confirm New Password</label>
                <div class="controls">
                    <input type="password" id="new_password2" name="new_password2" placeholder="Confirm New Password">
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn" name="processextra_security_replace" value="extra_security_replace">Change Security Details</button>
                </div>
            </div>
        </form>
    </div>
</div>