
@extends('emails.messages.post')

@section('body')
<p>An {{ $endorsementRequest->typeForHumans }} request has been created by {{ $requester->name }} ({{ $requester->id }})
for member {{ $account->name }} ({{ $account->id }})
on {{ $endorsementRequest->endorsable->name }}.</p>

<p>Vist the <a href="{{ $requestIndexUrl }}">endorsement requests page</a> to view the request.</p>
@endsection
