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

<div class="row">
    <div class="col-md-7 col-md-offset-2">
        <form class="form-horizontal" method="POST" action="<?= URL::site("sso/auth/override") ?>">
            <div class="form-group">
                <label class="col-sm-5 control-label" for="override_cid">Account CID</label>
                <div class="col-sm-7">
                    <input class="form-control" type="text" id="override_cid" name="override_cid" placeholder="Account CID">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 control-label" for="password">Secondary Password</label>
                <div class="col-sm-7">
                    <input class="form-control" type="password" id="extra_password" name="password" placeholder="Secondary Password">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-5 col-sm-7">
                    <button type="submit" class="btn btn-default" name="processoverride" value="override">Override Account</button>
                </div>
            </div>
        </form>
    </div>
</div>