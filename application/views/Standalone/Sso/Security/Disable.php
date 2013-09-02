<p>
    Please complete this form below to disable a second security layer on your account. Before disabling your extra security password, we need to confirm your current one.
</p>

<?php if(isset($error)): ?>
    <div class="alert alert-error">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Warning! An error occured:</strong>
        <ul>
            <li><?=$error?></li>
        </ul>
    </div>
<?php endif; ?>

<div class="row-fluid">
    <div class="span6 offset2">
        <form class="form-horizontal form-login" method="POST" action="<?= URL::site("sso/security/disable") ?>">
            <div class="control-group">
                <label class="control-label" for="password">Current Password</label>
                <div class="controls">
                    <input type="password" id="password" name="password" placeholder="Password">
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn" name="processsecurity_disable" value="security_disable">Disable Extra Security</button>
                </div>
            </div>
        </form>
    </div>
</div>