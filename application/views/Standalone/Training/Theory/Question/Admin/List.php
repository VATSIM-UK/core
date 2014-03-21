<p>
    A list of all the current theory questions are shown below.
</p>
<p>
    [ <button class="btn btn-primary" data-toggle="modal" data-target="#modalQuestionType">Create Question</button> ]
</p>

<div class="modal fade" id="modalQuestionType" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="GET" action="<?= URL::site("training/theory_question_admin/modify") ?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Select a question type...</h4>
                </div>
                <div class="modal-body">
                    <?php foreach(Enum_Training_Theory_Question_Type::getAll() as $key => $value): ?>
                        <div class="radio">
                            <label>
                                <input type="radio" name="questionType" id="questionType" value="<?=$value?>" checked>
                                <?=Enum_Training_Theory_Question_Type::getDescription($value)?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Question</th>
                <th>Category</th>
                <th>Type</th>
                <th>Difficulty</th>
                <th>Used Count</th>
                <th>Used Last</th>
                <th style="text-align: center">Status</th>
                <th style="text-align: center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $question): ?>
                <tr>
                    <td><?= HTML::anchor("training/theory_question_admin/modify/" . $question->id, $question->id) ?></td>
                    <td><?= $question->question ?></td>
                    <td><?= $question->category->name ?></td>
                    <td><?= Enum_Training_Theory_Question_Type::getDescription($question->type) ?></td>
                    <td><?= Enum_Training_Theory_Difficulty::getDescription($question->difficulty_rating) ?></td>
                    <td><?= ($question->used_count == 0) ? "Never" : $question->used_count ?></td>
                    <td><?= ($question->used_last == NULL) ? "N/A" : $question->used_last ?></td>
                    <td align="center">
                        <?php if ($question->available): ?>
                            <span class="label label-success">&nbsp;&nbsp;Available&nbsp;&nbsp;</span>
                        <?php else: ?>
                            <span class="label label-danger">Unavailable</span>
                        <?php endif; ?>
                    </td>
                    <td align="center">
                        <a href="<?= URL::site("training/theory_question_admin/toggle_status/" . $question->id) ?>">
                            <?php if ($question->available): ?>
                                <span class="label label-info"><span class="glyphicon glyphicon-eye-close"></span></span>
                            <?php else: ?>
                                <span class="label label-info"><span class="glyphicon glyphicon-eye-open"></span></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= URL::site("training/theory_question_admin/modify/" . $question->id) ?>">
                            <span class="label label-default"><span class="glyphicon glyphicon-pencil"></span></span>
                        </a>
                        <a href="<?= URL::site("training/theory_question_admin/delete/" . $question->id) ?>">
                            <span class="label label-danger"><span class="glyphicon glyphicon-remove"></span></span>
                        </a>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>