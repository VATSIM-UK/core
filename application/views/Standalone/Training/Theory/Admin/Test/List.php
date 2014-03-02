<p>
    A list of all the current theory examinations is shown below.
</p>
<p>
    [ <?= HTML::anchor("training/theory/admin_test_modify", "Create Exam") ?> ]
</p>

<div>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Time Allowed (minutes)</th>
                <th style="text-align: center">Time Expire Action</th>
                <th>Retake Cooloff (days)</th>
                <th>Retake Max Attempts (0=N/A)</th>
                <th style="text-align: center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tests as $test): ?>
                <tr>
                    <td><?= HTML::anchor("training/theory/admin_test_modify/" . $test->id, $test->id) ?></td>
                    <td><?= $test->name ?></td>
                    <td><?= $test->time_allowed ?>m</td>
                    <td align="center">
                        <?php if ($test->time_expire_action == "SUBMIT"): ?>
                            <span class="label label-success">SUBMIT</span>
                        <?php else: ?>
                            <span class="label label-danger">FORFEIT</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $test->retake_cooloff ?></td>
                    <td><?= $test->retake_max ?></td>
                    <td align="center">
                        <a href="<?= URL::site("training/theory/admin_test_toggle_status/" . $test->id) ?>">
                            <?php if ($test->available): ?>
                                <button type="button" class="btn btn-success btn-xs btn-block">&nbsp;&nbsp;Available&nbsp;&nbsp;</button>
                            <?php else: ?>
                                <button type="button" class="btn btn-danger btn-xs btn-block">Unavailable</button>
                            <?php endif; ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>