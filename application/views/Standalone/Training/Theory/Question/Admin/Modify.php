<p>
    Create a new question!
</p>

<div class="row">
    <form class="form-horizontal" method="POST" action="<?= URL::site("training/theory_question_admin/modify/" . $question->id) ?>" role="form">
        <input type="hidden" name="type" value="<?= $question->type ?>" />
        <div class="col-md-12">
            <legend>Basic Test Details</legend>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="category_id">Category</label>
                    <div class="col-sm-9">
                        <select class="form-control input-sm" name="category_id">
                            <option value="0">Please select</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c->id ?>" <?= ($question->category_id == $c->id) ? "selected='selected'" : "" ?>><?= $c->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="difficulty_rating">Difficulty Rating</label>
                    <div class="col-sm-9">
                        <select class="form-control input-sm" name="difficulty_rating">
                            <option value="0">Please select</option>
                            <?php foreach (Enum_Training_Theory_Difficulty::getAll() as $v): ?>
                                <option value="<?= $v ?>" <?= ($question->difficulty_rating == $v) ? "selected='selected'" : "" ?>>
                                    <?= Enum_Training_Theory_Difficulty::getDescription($v); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="clear">&nbsp;</div>

            <legend>Question: <?= Enum_Training_Theory_Question_Type::getDescription($question->type) ?></legend>
            <?= Request::factory("training/theory_question_admin/modify_type/" . $question->id . "?questionType=" . $question->type)->execute() ?>
            <div class="clear">&nbsp;</div>
            <br />

            <div class="form-group">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-default btn-primary pull-right"><?= ($create ? "Create" : "Edit") ?> question!</button>
                </div>
            </div>
        </div>
    </form>
</div>