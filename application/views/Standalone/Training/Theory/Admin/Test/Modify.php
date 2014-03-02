<p>
    Create a new theory exam!
</p>

<div class="row">
    <form class="form-horizontal" method="POST" action="<?= URL::site("training/theory/admin_test_modify/" . $test->id) ?>" role="form">
        <div class="col-md-12">
            <legend>Basic Test Details</legend>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="name">Name</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" id="name" name="name" placeholder="Approach Test 1" value="<?= ($_request->post("name") == NULL) ? $test->name : $_request->post("name") ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="time_allowed">Time Allowed (minutes)</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" id="name" name="time_allowed" placeholder="eg. 30" value="<?= ($_request->post("time_allowed") == NULL) ? $test->time_allowed : $_request->post("time_allowed") ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="time_expire_action">Time Expire Action</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="time_expire_action" name="time_expire_action">
                            <option value="FORFEIT" selected="<?= ($_request->post("time_expire_action") == "FORFEIT" ? "selected" : ($test->time_expire_action == "FORFEIT" ? "selected" : "")) ?>">Foreit, scoring ZERO.</option>
                            <option value="SUBMIT" selected="<?= ($_request->post("time_expire_action") == "FORFEIT" ? "selected" : ($test->time_expire_action == "FORFEIT" ? "selected" : "")) ?>">Submit, scoring for any answered questions.</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="retake_cooloff">Cooling Off Period</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" id="retake_cooloff" name="retake_cooloff" placeholder="eg. 7" value="<?= ($_request->post("retake_cooloff") == NULL) ? $test->retake_cooloff : $_request->post("retake_cooloff") ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label" for="retake_max">Max Auto Re-takes</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" id="retake_max" name="retake_max" placeholder="eg. 7" value="<?= ($_request->post("retake_max") == NULL) ? $test->retake_max : $_request->post("retake_max") ?>" />
                        <span class="help">Maximum number of automatic retakes.</span>
                    </div>
                </div>
            </div>
            <div class="clear">&nbsp;</div>

            <legend>Categories, Quantities and Difficulty</legend>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Min Difficulty</th>
                        <th>Max Difficulty</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="categories_list">
                    <?php foreach ($test->categories->find_all() as $tc): ?>
                        <tr>
                            <td>
                                <select class="form-control input-sm" name="category_id[]">
                                    <option value="0">Please select</option>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= $c->id ?>" selected="<?= ($tc->category_id == $c->id) ? "selected" : "" ?>"><?= $c->name ?></option>
                                    <?php endforeach; ?>
                                </select></td>
                            <td>
                                <input class="form-control input-sm" type="text" id="name" name="category_question_count[]" placeholder="eg. 4" value="<?= $tc->question_count ?>" onchange="javascript: totalQuestionCount();" />
                            </td>
                            <td>
                                <select class="form-control input-sm" name="category_difficulty_min[]">
                                    <?php foreach (Enum_Training_Theory_Difficulty::getAll() as $value => $key): ?>
                                        <option value="<?= $key ?>" <?= ($tc->difficulty_min == $key) ? "selected" : "" ?>>
                                            <?= ucfirst(strtolower($value)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select class="form-control input-sm" name="category_difficulty_max[]">
                                    <?php foreach (Enum_Training_Theory_Difficulty::getAll() as $value => $key): ?>
                                        <option value="<?= $key ?>" <?= ($tc->difficulty_max == $key) ? "selected" : "" ?>>
                                            <?= ucfirst(strtolower($value)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td align="right">
                                <button type="button" class="btn btn-danger btn-sm btn-block table-delete-link">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td>
                            <select class="form-control input-sm" name="category_id[]">
                                    <option value="0">Please select</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c->id ?>"><?= $c->name ?></option>
                                <?php endforeach; ?>
                            </select></td>
                        <td>
                            <input class="form-control input-sm" type="text" name="category_question_count[]" placeholder="eg. 4" onchange="javascript: totalQuestionCount();" />
                        </td>
                        <td>
                            <select class="form-control input-sm" name="category_difficulty_min[]">
                                <?php foreach (Enum_Training_Theory_Difficulty::getAll() as $value => $key): ?>
                                    <option value="<?= $key ?>">
                                        <?= ucfirst(strtolower($value)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select class="form-control input-sm" name="category_difficulty_max[]">
                                <?php foreach (Enum_Training_Theory_Difficulty::getAll() as $value => $key): ?>
                                    <option value="<?= $key ?>">
                                        <?= ucfirst(strtolower($value)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td align="right">
                            <button type="button" class="btn btn-danger btn-sm btn-block table-delete-link">Delete</button>
                        </td>
                    </tr>                </tbody>
                <tfoot>
                    <tr>
                        <td>&nbsp;</td>
                        <td>Total Question Count: <span id="question_count_total"><?=$test->get_question_count()?></span></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="right">
                            <button type="button" class="btn btn-success btn-sm btn-block" onclick="javascript: cloneTableFormRowBelow('categories_list');">Add Row</button>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="form-group">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-default btn-primary pull-right"><?= ($create ? "Create" : "Edit") ?> test!</button>
                </div>
            </div>
        </div>
    </form>
</div>