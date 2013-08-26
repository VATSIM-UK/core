<h1>Login</h1>
<p>
    To login to the <?= (isset($_GET["area"]) ? $_GET["area"] : "VATUK system") ?>, please enter your VATSIM Certificate ID and network password below.
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
        <form class="form-horizontal form-login" method="POST" action="<?= URL::site("sso/auth/login") ?>">
            <div class="control-group">
                <label class="control-label" for="cid">CID</label>
                <div class="controls">
                    <input type="text" id="cid" name="cid" placeholder="CID" value="<?= $_request->request->post("cid") ?>">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="password"><?= isset($cert_offline) ? "Extra Password" : "Password" ?></label>
                <div class="controls">
                    <input type="password" id="password" name="password" placeholder="Password">
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn" name="processlogin" value="login">Login</button>
                </div>
            </div>
        </form>
    </div>
</div>