function totalQuestionCount(){
    count = 0;
    $("input[name='category_question_count[]']").each(function(){
        count = count + parseInt($(this).val());
    });
    $("#question_count_total").html(count);
}