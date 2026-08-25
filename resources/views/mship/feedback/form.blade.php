@extends('layout')

@section('styles')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('scripts')
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js"
		integrity="sha384-8Lb23xW0dVl+HHrv90JF6PpwygXa7Z0zZIK9+RWorNDyubrG7Ppu7JJw32U8op0i" crossorigin="anonymous">
	</script>

	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
			$('.datetimepickercustom').datetimepicker();

			$('.searchable-select').select2({
				placeholder: "Select or search position...",
				allowClear: true,
				width: '100%'
			});

			var $pages = $('.feedback-page');
			var total = $pages.length;
			var idx = 0;

			// To check if a member was controlling at the time
			var isAtcForm = @json(isset($form) && $form->slug === 'atc');
			var checkAtcSessionUrl = @json(route('mship.feedback.check-atc-session'));
			var $cidField = $('[data-question-type="userlookup"]').find('input,select');
			var $datetimeField = $('[data-question-type="datetime"]').find('input');

			function renderPage() {
				$pages.hide().eq(idx).show();
				$('#feedbackStep').text(idx + 1);
				$('#feedbackPrev').toggle(idx > 0);
				$('#feedbackNext').toggle(idx < total - 1);
				$('#feedbackSubmit').toggle(idx === total - 1);
			}

			function pageValid() {
				var ok = true,
					first = null;
				$pages.eq(idx).find('.form-group[data-required="true"]').each(function() {
					var $g = $(this),
						filled = false;
					$g.find('input[type=radio],input[type=checkbox]').each(function() {
						if (this.checked) filled = true;
					});

					$g.find('input:not([type=radio]):not([type=checkbox]),textarea,select').each(function() {
						if ($.trim($(this).val() || '') !== '') filled = true;
					});

					$g.find('.select2-hidden-accessible').each(function() {
						if ($.trim($(this).val() || '') !== '') filled = true;
					});

					$g.toggleClass('has-error', !filled);
					if (!filled) {
						ok = false;
						first = first || $g;
					}
				});
				if (first) $('html,body').animate({
					scrollTop: first.offset().top - 100
				}, 200);
				return ok;
			}

			// Does the current page contain the CID and/or datetime fields?
			function pageHasSessionCheckFields() {
				return $pages.eq(idx).find('[data-question-type="userlookup"], [data-question-type="datetime"]')
					.length > 0;
			}

			function clearSessionError() {
				$('#feedbackSessionError').remove();
			}

			function showSessionError(message) {
				clearSessionError();
				$pages.eq(idx).prepend(
					'<div id="feedbackSessionError" class="alert alert-danger">' + message + '</div>'
				);
			}

			function checkAtcSessionAjax() {
				var cid = $cidField.val();
				var datetime = $datetimeField.val();

				if (!cid || !datetime) {
					return $.Deferred().resolve(true).promise();
				}

				var deferred = $.Deferred();
				var $next = $('#feedbackNext');
				$next.prop('disabled', true).data('original-text', $next.html()).html('Checking...');

				$.post(checkAtcSessionUrl, {
						_token: '{{ csrf_token() }}',
						cid: cid,
						datetime: datetime
					})
					.done(function(res) {
						if (res.valid) {
							clearSessionError();
							deferred.resolve(true);
						} else {
							showSessionError(res.message);
							deferred.resolve(false);
						}
					})
					.fail(function(xhr) {
						var msg = (xhr.responseJSON && xhr.responseJSON.message) ?
							xhr.responseJSON.message :
							'Could not verify this at the moment. Please try again.';
						showSessionError(msg);
						deferred.resolve(false);
					})
					.always(function() {
						$next.prop('disabled', false).html($next.data('original-text'));
					});

				return deferred.promise();
			}

			$('#feedbackNext').click(function() {
				if (!pageValid()) return;

				if (isAtcForm && pageHasSessionCheckFields()) {
					checkAtcSessionAjax().done(function(valid) {
						if (valid) {
							idx++;
							renderPage();
						}
					});
				} else {
					idx++;
					renderPage();
				}
			});

			$('#feedbackPrev').click(function() {
				clearSessionError();
				idx--;
				renderPage();
			});

			if (total) renderPage();
		});
	</script>
@endsection

@section('content')
	<div class="panel panel-ukblue">
		<div class="panel-heading">Submit Feedback</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-md-7 col-md-offset-2">
					@if (!isset($form))
						<form method="POST" action="{{ route('mship.feedback.new') }}">
							@csrf
							<p>
								<label for="feedback_type">What kind of feedback would you like to leave?</label>
								<select name="feedback_type" id="feedback_type" class="form-control">
									@foreach ($feedbackForms as $key => $value)
										<option value="{{ $key }}">{{ $value }}</option>
									@endforeach
								</select>
							</p>
							<p class="text-center">
								<button type="submit" class="btn btn-primary">Next <i class="fa fa-arrow-right"></i></button>
							</p>
						</form>
					@else
						<form method="POST" action="{{ route('mship.feedback.new.form.post', $form) }}" autocomplete="off">
							@csrf
							<p>
								@if ($form->targeted)
									Here you can submit anonymous feedback about a <b>UK</b> division member.
									Please try to explain your answers fully. Your identity is kept anonymous to staff &amp;
									the subject of the feedback. However, senior staff will be able to discover your
									identity in the case of abuse of this system.
								@else
									Here you can submit anonymous feedback.
									Please try to explain your answers fully. Your identity is kept anonymous to staff,
									however senior staff will be able to discover your identity in the case of abuse of this
									system.
								@endif
							</p>
							<p>
								The contents of your responses will be sent to the relevant team(s).
								@if ($form->targeted)
									It will not necessarily be sent directly to the subject of your feedback.
								@endif
							</p>
							<p>
								All questions are required unless an <i>(optional)</i> is displayed beside it.
							</p>
							<hr>

							@php $pages = $questions->groupBy('page'); @endphp

							<p class="text-center">Step <span id="feedbackStep">1</span> of {{ $pages->count() }}</p>
							@foreach ($pages as $pageQuestions)
								<div class="feedback-page" style="{{ $loop->first ? '' : 'display:none;' }}">
									@foreach ($pageQuestions as $question)
										<div class="form-group{{ $errors->has($question->slug) ? ' has-error' : '' }}"
											data-required="{{ $question->required ? 'true' : 'false' }}" data-question-type="{{ $question->type->name }}"
											data-question-slug="{{ $question->slug }}">
											<label for="{{ $question->slug }}">{!! $question->question . ($question->required ? '' : ' (optional)') !!}</label> </br>
											{!! $question->form_html !!}
										</div>
									@endforeach
								</div>
							@endforeach

							<div class="form-group text-center">
								<button type="button" id="feedbackPrev" class="btn btn-default" style="display:none;">Previous</button>
								<button type="button" id="feedbackNext" class="btn btn-primary">Next <i class="fa fa-arrow-right"></i></button>
								<button type="submit" id="feedbackSubmit" class="btn btn-success" style="display:none;">Submit</button>
							</div>
						</form>
					@endif
				</div>
			</div>
			<!-- Second Row [END] -->
			<!-- Content Of Panel [END] -->

		</div>
	</div>

@stop
