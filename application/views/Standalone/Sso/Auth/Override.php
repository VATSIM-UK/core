<p>
    Please enter the CID of an account to override, along with your second security layer details.
</p>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Warning! An error occured:</strong>
        <ul>
            <li><?= $error ?></li>
        </ul>
    </div>
<?php endif; ?>

<div class="row-fluid">
    <div class="span6 offset2">
        <form class="form-horizontal form-login" method="POST" action="<?= URL::site("sso/auth/override") ?>">
            <div class="control-group">
                <label class="control-label" for="override_cid">Account CID</label>
                <div class="controls">
                    <input type="text" id="override_cid" name="override_cid" placeholder="Account CID">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="password">Secondary Password</label>
                <div class="controls">
                    <input type="password" id="extra_password" name="password" placeholder="Secondary Password">
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn" name="processoverride" value="override">Override Account</button>
                </div>
            </div>
        </form>
    </div>
</div>