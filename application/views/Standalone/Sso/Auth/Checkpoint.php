<p>
    Sorry to have to stop you, but it appears there have been multiple logins from the same IP address (<?=$_SERVER["REMOTE_ADDR"]?>).  As a precaution you are required to validate your details by answering the security question below.
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
        <form class="form-horizontal form-login" method="POST" action="<?= URL::site("/sso/auth/checkpoint") ?>">
            <div class="control-group">
                <label class="control-label" for="password">
                    <?php if ($has_sls): ?>
                        Second Level Password
                    <?php else: ?>
                        Password
                    <?php endif; ?>
                </label>
                <div class="controls">
                    <input type="password" id="password" name="password" placeholder="<?=($has_sls ? "Extra Security Password" : "Password")?>">
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn" name="processcheckpoint" value="login">Confirm</button>
                </div>
            </div>
        </form>
    </div>
</div>