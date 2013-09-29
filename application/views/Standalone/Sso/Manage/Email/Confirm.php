<p>
    We do not yet have a valid email address stored in our database for you.
    This could be because this is the first time you have logged in, you haven't logged in within the last 3 months or a 
    syncronisation error with the main VATSIM database.
</p>
<p>
    To continue utilising our systems, please provide your current VATSIM email address so that we can store this for future reference.
</p>
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
        <form class="form-horizontal form-login" method="POST" action="<?= URL::site("sso/manage/email_confirm") ?>">
            <div class="control-group">
                <label class="control-label" for="cid">EMail</label>
                <div class="controls">
                    <input type="text" id="email" name="email" placeholder="E-Mail">
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn" name="processemail_confirm" value="true">Confirm EMail</button>
                </div>
            </div>
        </form>
    </div>
</div>