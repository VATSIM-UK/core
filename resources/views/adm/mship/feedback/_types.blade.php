@include('adm.mship.feedback._question', ['question' => $new_question, 'hideme' => true, 'num' => 'template'])
<div class="box box-warning">
    <div class="box-header">
        <h4 class="box-title" style="font-size:1.5em">
            Input Types<br>
            <small>Click and drag these types across</small>
        </h4>
    </div>
</div>
<ol class="simple_connected_list" id="question-types-box" style="list-style: none; padding: 0;">
    @foreach ($question_types as $type)
        @include('adm.mship.feedback._type', ['type' => $type])
    @endforeach
</ol>
