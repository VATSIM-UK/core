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

<div class="row">
    <div class="col-md-7 col-md-offset-2">
        <form class="form-horizontal" method="POST" action="<?= URL::site("sso/security/disable") ?>">
            <div class="form-group">
                <label class="col-sm-5 control-label" for="password">Current Password</label>
                <div class="col-sm-7">
                    <input class="form-control" type="password" id="password" name="password" placeholder="Password">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-5 col-sm-7">
                    <button type="submit" class="btn btn-default" name="processsecurity_disable" value="security_disable">Disable Extra Security</button>
                </div>
            </div>
        </form>
    </div>
</div>