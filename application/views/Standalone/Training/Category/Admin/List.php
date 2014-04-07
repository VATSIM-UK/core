<p>
    A list of all the current training categories are listed below.
</p>
<p>
    It's important to understand what is meant by a <em>category</em>. A category is a collection of information <em>within a course</em>.
    One category can be shared by multiple courses - it is not specific to a rating or point of training.
</p>
<p>
    [ <?= HTML::anchor("training/category/admin_modify", "Create Category") ?> ]
</p>

<div>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th style="text-align: center">Status</th>
                <th style="text-align: center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= HTML::anchor("training/category/admin_modify/" . $category->id, $category->id) ?></td>
                    <td><?= $category->name ?></td>
                    <td align="center">
                        <?php if ($category->available): ?>
                            <span class="label label-success">&nbsp;&nbsp;Available&nbsp;&nbsp;</span>
                        <?php else: ?>
                            <span class="label label-danger">Unavailable</span>
                        <?php endif; ?>
                    </td>
                    <td align="center">
                        <a href="<?= URL::site("training/category/admin_toggle_status/" . $category->id) ?>">
                            <?php if ($category->available): ?>
                                <span class="label label-info"><span class="glyphicon glyphicon-eye-close"></span></span>
                            <?php else: ?>
                                <span class="label label-info"><span class="glyphicon glyphicon-eye-open"></span></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= URL::site("training/category/admin_modify/" . $category->id) ?>">
                            <span class="label label-default"><span class="glyphicon glyphicon-pencil"></span></span>
                        </a>
                        <a href="<?= URL::site("training/category/admin_delete/" . $category->id) ?>">
                            <span class="label label-danger"><span class="glyphicon glyphicon-remove"></span></span>
                        </a>
                    </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>