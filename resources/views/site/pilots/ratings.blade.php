@extends('layout')

@section('content')


    <div class="col-md-8 col-md-offset-2 ">
        <div class="alert alert-danger">
		    <h3 style="margin-top: 0">Very long waiting times - in excess of 1 year to begin Pilot Training</h3>
		    <p>Please note that the average time frame for new pilots joining our waiting list and being offered a training place exceeds one year. You should be prepared for this wait - the division is working hard to improve training times, but demand is high. Thank you for your patience.</p>
	    </div>

        <div class="row">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-plane"></i> &thinsp; P1 (Online Pilot) Rating
                </div>
                <div class="panel-body">
                <p>Welcome to the P1 Private Pilots License course for fixed wing aircraft!  The successful completion of this course will result in the issue of the VATSIM P1 PPL(A) pilot rating.<br><br>
                    Throughout the course you will learn the fundamental theory and practical skills associated with operating a light aircraft. The course focuses on ‘stick and rudder’ skills backed up with the relevant theory with the aim of providing you with the how and the why!<br><br>
                    The course is split into 3 sections: <strong>Theory Phase</strong>, <strong>Practical Phase</strong>, and a <strong>License Skills Test (LST)</strong>.</p>

                    <br>

                    <ol>
                        <li>
                            <strong>Theory Phase</strong><br>
                            After the completion of each topic, you will be required to complete a quiz containing a number of questions for you to answer. The purpose of each quiz is to help you gauge your knowledge of the section that you have studied so that you can target areas for improvement.<br><br>
                            After you have completed all of the sections in a module you will sit an exam. This exam is graded and has a pass mark of 75%.<br><br>
                            Each module can be completed in any order and does not have to be completed in the order.<br><br>
                            After you have passed all 3 ‘end of module’ theory exams, submit a ticket <a href="https://helpdesk.vatsim.uk/open.php" rel="external nofollow">here</a> indicating that you have completed the P1 PPL(A) theory phase. This will allow us to make you ‘eligible’ for a training place.<br><br>
                            Once you have reached the top of our waiting list and have indicated to us that you have passed all 3 ‘end of module’ theory exams, we will issue you a training place when one becomes available.<br><br>
                        </li>
                        <li>
                            <strong>Practical Phase</strong><br>
                            During the practical phase you will complete 22 practical lessons. These lessons are either one-to-one mentoring sessions or online video lessons.<br><br>
                            One-to-one mentoring sessions take place on the VATSIM UK TeamSpeak server which can be booked via the <a href="https://cts.vatsim.uk" rel="external nofollow">CTS</a>.<br><br>
                            Online video lessons are marked in brackets with (Online) next to the lesson title. These lessons require you to read the associate briefing, watch the online video lesson, and then practise the contents of the lesson yourself.<br><br>
                            The next lesson will come available when you have completed the previous lesson. One-to-one mentoring sessions will be marked as completed by your mentor, and online video lessons will be automatically marked as complete when you have read the briefing and watched the video for that lesson.<br><br>
                        </li>
                        <li>
                            <strong>License Skills Test</strong><br>
                            Once you have completed the practical phase of the course you will complete a practical exam with an examiner. The details of the exam can be found in the Pilot Training Handbook.
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

        <div class="row">

<div class="col-md-8 col-md-offset-2 ">
    <div class="panel panel-ukblue">
        <div class="panel-heading"><i class="fa fa-plane"></i> &thinsp; How To Enrol
        </div>
        <div class="panel-body">
            @if(Auth::user() && $_account->primary_state->code == 'DIVISION')
                <strong>{{ $_account->name_first }}</strong>, <strong>you are</strong> a Division member, so you can get started straight away!<br />
                You can find details on how to sign up for training in the UK below.
            @elseif(Auth::user())
                <strong>{{ $_account->name_first }}</strong>, <strong>you're not</strong> currently a Division member!<br />
                You can find details on how to sign up for training in the UK below.
            @else
                You will need to be a member of VATSIM to sign up to our training courses.<br />
                Already a member? <a href="{{ route('login') }}">Click here to login</a> and find out which route is the most applicable to you.
            @endif
        </div>
    </div>
</div>

</div>
        <div class="row">

            <div class="col-md-4 col-md-offset-2" @if(Auth::user() && $_account->primary_state->code !== 'DIVISION')style="opacity: 0.3"@endif>
                <div class="panel panel-uk-success">
                    <div class="panel-heading"><i class="fa fa-check"></i> &thinsp; I am a member of the
                        UK division
                    </div>
                    <div class="panel-body">
                        <ol>
                            <li>
                                Express your interest <a href="https://helpdesk.vatsim.uk/open.php" rel="external nofollow">here</a> - you will then be added to the waiting list.
                            </li>
                            <li>
                                Sign up to the P1 PPL(A) moodle course <a href="https://moodle.vatsim.uk/course/view.php?id=51" rel="external nofollow">here</a>.
                            </li>
                            <li>
                                Complete Theory Modules one, two and three.
                            </li>
                            <li>
                                Notify us <a href="https://helpdesk.vatsim.uk/open.php" rel="external nofollow">here</a> that you have completed the Theory Phase of the P1 PPL(A) moodle course.
                            </li>
                            <li>
                                Sit tight! We will be in touch when a training place becomes available.
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="col-md-4" @if(Auth::user() && $_account->primary_state->code == 'DIVISION')style="opacity: 0.3"@endif>
                <div class="panel panel-uk-danger">
                    <div class="panel-heading"><i class="fa fa-times"></i> &thinsp; I am not a member
                        of the UK divison
                    </div>
                    <div class="panel-body">
                        <ol>
                            <li text="">
                                <a href="{{ route('visiting.landing') }}" rel="">Apply to visit as a
                                    Pilot</a>
                            </li>
                            <li text="">
                                When your V/T application has been processed you will be contacted by the Pilot Training
                                Team using our HelpDesk.
                            </li>
                            <li>
                                You will either be added to the waiting list or you will be informed that your mentoring
                                permissions have been assigned.
                            </li>
                            <li>
                                Once your mentoring permissions have been assigned navigate to our <a
                                        href="https://cts.vatsim.uk/" rel="external nofollow">Central Training System
                                    (CTS)</a>.
                            </li>
                            <li>
                                Sign into the CTS using our SSO.
                            </li>
                            <li>
                                Select the Students Drop down menu and navigate to <strong>Sessions &gt; Managment</strong>
                            </li>
                            <li>
                                Add a session request using the <strong>&#39;Request Session&#39;</strong> drop down box,
                                <em>e.g P1_PPL(A) for the P1 Course</em>
                            </li>
                            <li>
                                Add availability to the system and ensure this is kept up to date.
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
@stop
