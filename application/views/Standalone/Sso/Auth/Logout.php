<p>
    You have now been logged out of the <?= $area ?> system.  Would you like for your SSO session to be terminated, preventing future access to other services?
</p>

<div class="row-fluid">
    <div class="span6 offset2">
        <form class="form-horizontal form-login" method="POST" action="<?= URL::site("sso/auth/logout") ?>">
            <div class="control-group offset3">
                <div class="btn-group">
                    <button type="submit" class="btn btn-success btn-large" name="processlogout" value="1">YES, please!</button>
                    <button type="submit" class="btn btn-danger btn-large" name="processlogout" value="0">NO, thanks</button>
                </div>
            </div>
        </form>
    </div>
</div>