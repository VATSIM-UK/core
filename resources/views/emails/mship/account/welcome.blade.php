<p>
    Dear {!! $account->name !!},
</p>

<p>
    This email serves as confirmation that your central account with VATSIM UK has been created. We have received the
    following details about you:
</p>

<p>
    CID: {!! $account->account_id !!}<br/>
    Full Name: {!! $account->name !!}<br/>
    Primary Email: {!! $account->primary_email->email !!}<br/>
    Secondary Emails:<br/>
    @foreach($account->secondary_email as $e)
        -- {!! $e->email !!}<br/>
    @endforeach
    @if(count($account->secondary_email) < 1)
        No secondary emails registered.<br/>
    @endif
</p>

<p>
    Status: {!! $account->status_string !!}<br/>
    State: {!! $account->primary_state !!}<br/>
</p>

<p>
    ATC Qualification: {!! $account->qualification_atc !!}<br/>
    Pilot Qualification(s): {!! $account->qualifications_pilot_string !!}<br/>
</p>

<p>
    Now that your account has been created, you can login to any of our web services and these details will be
    transferred automatically.
</p>

<p>
    Furthermore, any updates you make to your details via the vatsim.net membership portal will be synchronised with our
    database in a timely manner, to ensure all our systems remain up to date.
</p>

<p>
    If any details are incorrect, or you have any concerns, please
    contact {!! link_to('mailto:community@vatsim-uk.co.uk', 'our community department') !!} who will be able to help you
    further.
</p>
