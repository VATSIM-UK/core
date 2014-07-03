<p>
    A list of all the current theory examinations is shown below.
</p>
<p>
    [ <?= HTML::anchor("training/theory_test_admin/modify", "Create Exam") ?> ]
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
                <th style="text-align: center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tests as $test): ?>
                <tr>
                    <td><?= HTML::anchor("training/theory_test_admin/modify/" . $test->id, $test->id) ?></td>
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
                        <?php if ($test->available): ?>
                            <span class="label label-success">&nbsp;&nbsp;Available&nbsp;&nbsp;</span>
                        <?php else: ?>
                            <span class="label label-danger">Unavailable</span>
                        <?php endif; ?>
                    </td>
                    <td align="center">
                        <a href="<?= URL::site("training/theory_test_admin/toggle_status/" . $test->id) ?>">
                            <?php if ($test->available): ?>
                                <span class="label label-info"><span class="glyphicon glyphicon-eye-close"></span></span>
                            <?php else: ?>
                                <span class="label label-info"><span class="glyphicon glyphicon-eye-open"></span></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= URL::site("training/theory_test_admin/modify/" . $test->id) ?>">
                            <span class="label label-default"><span class="glyphicon glyphicon-pencil"></span></span>
                        </a>
                        <a href="<?= URL::site("training/theory_test_admin/delete/" . $test->id) ?>">
                            <span class="label label-danger"><span class="glyphicon glyphicon-remove"></span></span>
                        </a>
                    </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>