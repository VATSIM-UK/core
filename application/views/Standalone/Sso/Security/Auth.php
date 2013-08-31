<p>
    Please enter your second, VATSIM-UK password below.
</p>
<div class="row-fluid">
    <div class="span6 offset2">
        <form class="form-horizontal form-login" method="POST" action="<?= URL::site("sso/security/auth") ?>">
            <div class="control-group">
                <label class="control-label" for="password">Secondary Password</label>
                <div class="controls">
                    <input type="password" id="password" name="password" placeholder="Secondary Password">
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn">Validate Secondary Password</button>
                </div>
            </div>
        </form>
    </div>
</div>