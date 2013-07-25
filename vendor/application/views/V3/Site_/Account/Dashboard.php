<p>
    Welcome to your account dashboard.  Here you can see a full overview of all your account
    details, property listings, messages and much more.
</p>
<p>
    More content to come!
</p>
<h3>Current Emails</h3>
<p>
    <?php foreach($_account->emails->find_all() as $e): ?>
        <?=($e->primary ? "**PRIMARY** " : "")?>
        <?=$e->email?> // Added: <?=$e->created?><br />
    <?php endforeach; ?>
</p>
<h3>Endorsements</h3>
<p>
    <?php foreach($_account->endorsements->find_all() as $e): ?>
        <h5><?=$e->type?> :: <?=$e->formatEndorsement(true)?> (<?=$e?>)</h5>
        <p>Gained: <?=$e->created?></p>
        <p><?=(($e->type == "ATC") ? $e->formatPositionSuffixes() : "")?></p>
    <?php endforeach; ?>
</p>
