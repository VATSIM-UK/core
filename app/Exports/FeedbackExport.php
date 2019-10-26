<?php

namespace App\Exports;

use App\Models\Mship\Feedback\Form;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FeedbackExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{
    private $request;
    private $feedback;
    private $form;


    public function __construct(Request $request, $feedback, Form $form)
    {
        $this->request = $request;
        $this->feedback = $feedback;
        $this->form = $form;
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($feedback): array
    {
        $prepend = [];

        if ($this->request->input('include_target') && $this->request->targeted) {
            $prepend[] = $feedback->account->id;
            $prepend[] = $feedback->account->name;
        }

        $question_number = 1;

        $rows = [];

        foreach ($feedback->answers as $response) {
            if ($response->question->type->name == 'userlookup') {
                continue;
            }
            if ($question_number == 1) {
                $insert = $prepend;
            } else {
                $insert = [];
                foreach ($prepend as $header) {
                    $insert[] = '';
                }
            }

            $insert[] = $response->question->question;
            $insert[] = $response->response;
            if ($question_number == 1) {
                $insert[] = $feedback->created_at->format('Y-m-d H:i');
            }
            $rows[] = $insert;
            $question_number++;
        }
        $rows[] = [];
        return $rows;
    }


    /**
     * @return array
     */
    public function headings(): array
    {
        $headers = [];

        $from_date = new Carbon($this->request->input('from'));
        $to_date = new Carbon($this->request->input('to'));

        // Pre Header
        $headers[] = ['Feedback Form:', $this->form->name];
        $headers[] = ['From:', $from_date->format('d-m-Y')];
        $headers[] = ['To:', $to_date->format('d-m-Y')];
        $headers[] = ['Results:', $this->feedback->count()];
        $headers[] = ['Generated at:', Carbon::now()->format('d-m-Y H:i')];
        $headers[] = ['Generated by:', \Auth::user()->name];
        $headers[] = ['All times ZULU'];
        $headers[] = ['VATSIM UK'];
        $headers[] = [];
        $headers[] = [];

        // Headings
        $headings = [];
        if ($this->request->input('include_target') && $this->form->targeted) {
            $headings[] = 'Targeted ID';
            $headings[] = 'Targeted Name';
        }
        $headings[] = 'Question';
        $headings[] = 'Response';
        $headings[] = 'Submitted At';
        $headers[] = $headings;

        return $headers;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->feedback;
    }
}
