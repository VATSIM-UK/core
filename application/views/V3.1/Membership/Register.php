<p>
    Creating an account with us could not be easier.  Enter your details into the form
    below and we will process your request to open a new account - it's as simple as that!
</p>

<form class="form-horizontal" action="<?= URL::site("account/register") ?>" method="POST">
    <div class="container" id="type">
        <legend>Account Type</legend>
        <div class="form-group">
            <label class="col-sm-2 control-label">I am a...</label>
            <div class="col-sm-8">
                <button type="submit" name='account_type' class="btn btn-inverse btn-wide" value='student'>Pilot</button>
                <p class="help-inline">...looking to take advantage of the pilot like features of the site.</p><br />
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-8">
                <button type="submit" name="account_type" class="btn btn-inverse btn-wide" value='controller'>Controller</button>
                <p class="help-inline">...looking to take advantage of your tools.</p><br />
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-8">
                <button type="submit" name="account_type" class="btn btn-inverse btn-wide" value='both'>Pilot & Controller</button>
                <p class="help-inline">...because I'm greedy!</p><br />
            </div>
        </div>
    </div>
</form>
