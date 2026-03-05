@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading">Training Place Offer</div>

        <div class="panel-body">

            @if(isset($responded))
                <div class="alert alert-{{ $responded === 'accepted' ? 'success' : 'info' }}">
                    @if($responded === 'accepted')
                        <strong>Place accepted!</strong> We'll be in touch shortly with next steps.
                    @else
                        <strong>Offer declined.</strong> Thank you for letting us know.
                    @endif
                </div>
            @else

                <p>You have been offered a training place for <strong>{{ $offer->trainingPosition->position?->name }} ({{ $offer->trainingPosition->position?->callsign }})</strong>.</p>
                <p>Your training place offer expires on <strong>{{ $offer->expires_at->format('d/m/Y H:i') }}</strong>.</p>

                <form role="form" method="POST" action="{{ route('mship.waiting-lists.place-offer.respond', $offer->token) }}" id="offer-form">
                    {{ csrf_field() }}
                    <input type="hidden" name="response" id="response-input" value="">

                    @error('response')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <div id="decline-reason-section" style="display:none;" class="form-group">
                        <label for="decline_reason">
                            Reason for declining <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="decline_reason"
                            id="decline_reason"
                            rows="4"
                            class="form-control"
                            placeholder="Please provide a reason for declining your training place offer..."
                        ></textarea>
                        @error('decline_reason')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" id="main-buttons">
                        <button type="button" class="btn btn-success" onclick="submitResponse('accepted')">
                            Accept Training Place
                        </button>
                        <button type="button" class="btn btn-danger" onclick="toggleDecline()">
                            Decline Training Place
                        </button>
                    </div>

                    <div id="confirm-decline" style="display:none;" class="form-group" style="margin-top: 15px;">
                        <hr>
                        <button type="button" class="btn btn-danger" onclick="submitResponse('declined')">
                            Confirm Decline
                        </button>
                        <button type="button" class="btn btn-link" onclick="toggleDecline()">Cancel</button>
                    </div>

                </form>
            @endif

        </div>
    </div>
@endsection

@section('scripts')
<script>
    function toggleDecline() {
        const section = document.getElementById('decline-reason-section');
        const confirm = document.getElementById('confirm-decline');
        const mainButtons = document.getElementById('main-buttons');

        const visible = section.style.display !== 'none';
        section.style.display = visible ? 'none' : 'block';
        confirm.style.display = visible ? 'none' : 'block';
        mainButtons.style.display = visible ? 'block' : 'none';
    }

    function submitResponse(response) {
        document.getElementById('response-input').value = response;
        document.getElementById('offer-form').submit();
    }
</script>
@endsection