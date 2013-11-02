<p>
    Please enter your second, VATSIM-UK password below.
</p>
<div class="row">
    <div class="col-md-7 col-md-offset-2">
        <form class="form-horizontal" method="POST" action="<?= URL::site("sso/security/auth") ?>">
            <div class="form-group">
                <label class="col-sm-5 control-label" for="password">Secondary Password</label>
                <div class="col-sm-7">
                    <input class="form-control" type="password" id="password" name="password" placeholder="Secondary Password">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-5 col-sm-7">
                    <button type="submit" class="btn btn-default">Login</button>
                </div>
            </div>
        </form>
    </div>
</div>