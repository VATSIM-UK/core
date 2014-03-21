<div class="col-md-6">
    <div class="form-group">
        <label class="col-sm-2 control-label" for="question">Question</label>
        <div class="col-sm-10">
            <textarea rows="8" id="questionEditor" name="question"><?= ($_request->post("question") == NULL) ? $question->question : $_request->post("question") ?></textarea>
        </div>
    </div>
</div>

<div class="col-md-5">
    <div class="form-group">
        <label class="col-sm-3 control-label" for="answer_a">Answer A</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" name="answer_a" id="answer_a" value="<?= ($_request->post("answer_a") == NULL) ? $question->answer_a : $_request->post("answer_a") ?>" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="answer_b">Answer B</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" name="answer_b" id="answer_b" value="<?= ($_request->post("answer_b") == NULL) ? $question->answer_a : $_request->post("answer_b") ?>" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="answer_c">Answer C</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" name="answer_c" id="answer_c" value="<?= ($_request->post("answer_c") == NULL) ? $question->answer_c : $_request->post("answer_c") ?>" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="answer_d">Answer D</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" name="answer_d" id="answer_d" value="<?= ($_request->post("answer_d") == NULL) ? $question->answer_d : $_request->post("answer_d") ?>" />

        </div>
    </div>
</div>

<div class="col-md-1">
    <div class="form-group">
        <div class="radio">
                <input type="radio" name="answer_correct" id="answer_correct_a" value="a" <?= (($_request->post("answer_correct") == "a") ? "checked='checked'" : (($question->answer_correct == "a") ? "checked='checked'" : "")) ?> />
        </div>
    </div>
    <div class="form-group">
        <div class="radio">
                <input type="radio" name="answer_correct" id="answer_correct_b" value="b" <?= (($_request->post("answer_correct") == "b") ? "checked='checked'" : (($question->answer_correct == "b") ? "checked='checked'" : "")) ?> />
        </div>
    </div>
    <div class="form-group">
        <div class="radio">
                <input type="radio" name="answer_correct" id="answer_correct_c" value="c" <?= (($_request->post("answer_correct") == "c") ? "checked='checked'" : (($question->answer_correct == "c") ? "checked='checked'" : "")) ?> />
        </div>
    </div>
    <div class="form-group">
        <div class="radio">
                <input type="radio" name="answer_correct" id="answer_correct_d" value="d" <?= (($_request->post("answer_correct") == "d") ? "checked='checked'" : (($question->answer_correct == "d") ? "checked='checked'" : "")) ?> />
        </div>
    </div>
</div>

<script language="javascript" type="text/javascript">
    $(document).ready(function() {
        // WYSIWYG
        $('#questionEditor').summernote({
            toolbar: [
                //['style', ['style']], // no style button
                ['style', ['bold', 'italic', 'underline', 'clear']],
                        //['fontsize', ['fontsize']],
                        //['color', ['color']],
                        //['para', ['ul', 'ol', 'paragraph']],
                        //['height', ['height']],
                        //['insert', ['picture', 'link']], // no insert buttons
                        //['table', ['table']], // no table button
                        //['help', ['help']] //no help button
            ]
        });


        // Now sort the switches out!
        $('input[id^="answer_correct_"]').bootstrapSwitch("size", "mini");
        $('input[id^="answer_correct_"]').bootstrapSwitch("onColor", "success");
        $('input[id^="answer_correct_"]').bootstrapSwitch("onText", '<span class="glyphicon glyphicon-ok"></span>');
        $('input[id^="answer_correct_"]').bootstrapSwitch("offColor", "danger");
        $('input[id^="answer_correct_"]').bootstrapSwitch("offText", '<span class="glyphicon glyphicon-remove"></span>');

    });

</script>