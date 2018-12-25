<?php

namespace App\Http\Controllers\Adm\Mship\Feedback;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\Mship\Feedback\Feedback as FeedbackModel;

class FeedbackSendController extends \App\Http\Controllers\BaseController
{
    public function store(FeedbackModel $feedback, Request $request)
    {
        $feedback->markSent(Auth::user(), $request->input('comment'));

        return Redirect::back()
            ->withSuccess('Feedback sent to user!');
    }
}
