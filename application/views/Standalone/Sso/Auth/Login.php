<p>
    To login to the <?= (isset($_GET["area"]) ? $_GET["area"] : "VATUK system") ?>, please enter your VATSIM Certificate ID and network password below.
</p>

<div class="row">
    <div class="col-md-7 col-md-offset-2">
        <form class="form-horizontal" method="POST" action="<?= URL::site("sso/auth/login") ?>" role="form">
            <div class="form-group">
                <label class="col-sm-4 control-label" for="cid">CID</label>
                <div class="col-sm-8">
                    <input class="form-control" type="text" id="cid" name="cid" placeholder="CID" value="<?= $_request->post("cid") ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="password"><?= isset($cert_offline) ? "Extra Password" : "Password" ?></label>
                <div class="col-sm-8">
                    <input class="form-control" type="password" id="password" name="password" placeholder="Password" />
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-8">
                    <button type="submit" class="btn btn-default" name="processlogin" value="login">Login</button>
                </div>
            </div>
        </form>
    </div>
</div>