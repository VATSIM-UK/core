<?php

namespace App\Http\Requests\Mship\Feedback;

use App\Http\Requests\Request;
use App\Models\Mship\Feedback\Question\Type;

class NewFeedbackFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'question' => 'required',
            'question.*.name' => 'required|min:5',
            'question.*.slug' => 'required|alpha_num',
            'question.*.type' => 'required|exists:mship_feedback_question_types,name',
            'question.*.exists' => 'exists:mship_feedback_questions,id',
            'question.*.required' => 'required|boolean',
            'ident' => 'required',
            'name' => 'required',
            'contact' => 'nullable|email',
            'targeted' => 'nullable|boolean',
            'public' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'question.required' => 'You need to have some questions!',

            'question.*.name.required' => 'Your question needs a question!',
            'question.*.name.min' => 'Your question must be more than 5 charecters long.',

            'question.*.slug.required' => 'Please enter a slug for your question',
            'question.*.slug.alpha_num' => "A question's slug can only be alphanumeric. (A-z0-9)",

            'question.*.type.required' => 'Your question needs a type!',
            'question.*.type.exists' => "Your question type doesn't seem to exist. Please try again.",

            'question.*.exists.exists' => "This exisiting answer doesn't seem to exist. Please try again.",

            'question.*.required.required' => 'Please mark if this question is required or not.',
            'question.*.required.boolean' => 'There was an error with your question. Please try again.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('question') as $question) {
                // Ensure questions that require values have values supplied
                if (Type::findByName($question['type'])->requires_value) {
                    if (isset($question['options']['values'])) {
                        if ($question['options']['values'] != '' && count(explode(',', $question['options']['values'])) > 0) {
                            continue;
                        }
                    }
                    $validator->errors()->add($question['name'], 'The question "'.$question['name'].'" requires values!');
                }
            }
        });
    }

    protected function getValidatorInstance()
    {
        $data = $this->all();
        // Remove the template
        unset($data['question']['template']);

        // Remove the example inputs
        unset($data['example']);

        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
