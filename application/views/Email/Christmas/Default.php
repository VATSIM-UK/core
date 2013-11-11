<?= View::factory("Email/Global/Header")->render() ?>
    <?= $content ?>
    <?php if (gmdate("m") == 11): ?>
        <br /><p style="font-family: 'Josefin Slab', serif;">
            <?php if ((24 - gmdate("d")) < 1): ?>
                P.S: Christmas is tomorrow!
            <?php else: ?>
                P.S: It's only <?= 24 - gmdate("d") ?> days until Christmas!
            <?php endif; ?>
        </p>
    <?php endif; ?>
<?=View::factory("Email/Global/Footer")->render()?>