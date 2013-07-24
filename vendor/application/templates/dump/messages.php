<?php if ($messageSuccess): ?>
    <div id="message-green">
        <table border="0" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="green-left">
                    <strong>SUCCESS!</strong><br />
                    <?= $messageSuccess ?>
                </td>
                <td class="green-right"><a class="close-green"><img src="<?=URL_VIEW . $templateTheme?>/css/images/table/icon_close_green.gif" alt="" /></a></td>
            </tr>
        </table>
    </div>
<?php endif; ?>

<?php if ($messageError): ?>
    <div id="message-red">
        <table border="0" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="red-left">
                    <strong>ERROR!</strong><br />
                    <?= $messageError ?>
                </td>
                <td class="red-right"><a class="close-red"><img src="<?=URL_VIEW . $templateTheme?>/css/images/table/icon_close_red.gif" alt="" /></a></td>
            </tr>
        </table>
    </div>
<?php endif; ?>