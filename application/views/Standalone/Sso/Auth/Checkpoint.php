<p>
    Sorry to have to stop you, but it appears there have been multiple logins from the same IP address (<?=$_SERVER["REMOTE_ADDR"]?>).  As a precaution you are required to validate your details by answering the security question below.
</p>

<div class="row">
    <div class="col-md-7 col-md-offset-2">
        <form class="form-horizontal" method="POST" action="<?= URL::site("/sso/auth/checkpoint") ?>" role="form">
            <div class="form-group">
                <label class="col-sm-5 control-label" for="password">
                    <?php if ($has_sls): ?>
                        Second Level Password
                    <?php else: ?>
                        Password
                    <?php endif; ?>
                </label>
                <div class="col-sm-7">
                    <input class="form-control" type="password" id="password" name="password" placeholder="<?=($has_sls ? "Extra Security Password" : "Password")?>">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-5 col-sm-7">
                    <button type="submit" class="btn btn-default" name="processcheckpoint" value="login">Confirm</button>
                </div>
            </div>
        </form>
    </div>
</div>