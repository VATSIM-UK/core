<p>
    To login and manage your account, enter your email and password below.  If 
    you haven't yet created an account with us, you can do so by
    <a href="<?= URL::site("account/register") ?>">visiting our registration pages</a>.
</p>

<form class="form-horizontal" action="<?= URL::site("membership/account/login") ?>" method="POST">
    <legend>Login</legend>

    <? if (isset($errors) && in_array("login", $errors)): ?>
        <div class="alert alert-error">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h4>Error!</h4>
            <p>The email/password combination you supplied was not recognised.
                Please check and try again.</p>
        </div>
    <? endif; ?>

    <div class="control-group">
        <label class="control-label" for="email">Email</label>
        <div class="controls">
            <input type="email" name="email" value="<?= $request->post("email") ?>" required />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="password">Password</label>
        <div class="controls">
            <input type="password" name="password" required />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="remember">Remember Me</label>
        <div class="controls">
            <input type="checkbox" name="remember" value="1" />
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create Account</button>
    </div>
</form>
