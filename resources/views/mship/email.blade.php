@extends('layout')

@section('content')
    <div class="col-lg-8 col-lg-offset-2">
        <div class="panel panel-ukblue">
            <div class="panel-heading">Email a Member</div>
            <div class="panel-body">
                <p>You may use this to email division, visiting and transferring members. You may not use this form to
                    email other regional or international members, or inactive members.</p>
                <form action="{{ route('mship.email.post') }}" method="POST" class="form-horizontal">
                    @csrf
                <input name="recipient" id="recipient" type="hidden">
                <div class="form-group">
                    <label class="col-sm-4 control-label">Recipient</label>
                    <div class="col-sm-4">
                        <p id="recipient-display" style="display: none; cursor: pointer; text-decoration: underline;" class="form-control-static" data-toggle="modal" data-target="#recipientModal"></p>
                        <button type="button" class="btn btn-primary" id="recipient-button" data-toggle="modal" data-target="#recipientModal">
                            Choose Recipient
                        </button>

                    </div>
                </div>
                <div class="form-group">
                    <label for="subject" class="col-sm-4 control-label">Subject</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="subject" placeholder="Subject">
                    </div>
                </div>
                <div class="form-group">
                    <label for="message" class="col-sm-4 control-label">Message</label>
                    <div class="col-sm-4">
                            <textarea class="form-control" rows="10" name="message"
                                      placeholder="Enter your message here"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="hide-email"> Hide my email address<br><span style="overflow-wrap: break-word; hyphens: auto;">({{$_account->email}})</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-4">
                        <button type="submit" class="btn btn-default" id="send">Send</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="recipientModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Recipient Search</h4>
                </div>
                <div class="modal-body">
                    <div id="form-errors"></div>
                    <form id="search-form" class="form-horizontal">
                        <div class="form-group">
                            <label for="search-type" class="col-sm-4 control-label">Search by</label>
                            <div class="col-sm-4">
                                <select id="search-type" class="form-control" onchange="searchBy()">
                                    <option value="cid">CID</option>
                                    <option value="name">Name</option>
                                </select>
                            </div>
                        </div>
                        <div id="recipient-search-fg" class="form-group">
                            <label for="recipient-search" class="col-sm-4 control-label">CID</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="recipient-search"
                                       placeholder="Enter CID">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-4">
                                <button type="submit" class="btn btn-default" id="recipient-submit">Search</button>
                            </div>
                        </div>
                    </form>
                    <div id="results" style="display: none;">
                        <table class="table table-striped">
                            <tr>
                                <th>CID</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('scripts')
    <script type="text/javascript">
        $('#search-form').submit(function(event) {
            event.preventDefault();

            var data = {
                query: $('#recipient-search').val(),
                type: $('#search-type').val()
            };

            // get the list of potential recipients
            $.get('{{route('mship.email.recipient-search')}}', data, function (res) {
                // reset recipient table and errors
                $('#form-errors').html('');
                $('#results table tr:gt(0)').remove();
                $('#results').show();

                // add the rows to the table
                if (res.match === 'exact') {
                    status = '<span class="fa fa-check-circle"></span>';
                    action = getChooseButton(res.data.id, res.data.name);
                    $('#results table tbody').append(makeRecipientRow(res.data.id, res.data.name, status, action));
                } else if (res.match === 'partial') {
                    $.each(res.data, function (index, item) {
                        if (item.valid) {
                            status = '<span class="fa fa-circle"></span>';
                            action = getChooseButton(item.id, item.name);
                        } else {
                            status = '<span class="fa fa-remove"></span>';
                            action = item.error;
                        }
                        $('#results table tbody').append(makeRecipientRow(item.id, item.name, status, action));
                    });
                }
            }).fail(function (res) {
                // hide results table
                $('#results').hide();

                // display error
                var errorsHtml = '<div class="alert alert-danger"><ul>';
                $.each(res.responseJSON, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                errorsHtml += '</ul></di>';

                $('#form-errors').html(errorsHtml);
            });
        });

        $('#recipientModal').on('shown.bs.modal', function () {
            $('#recipient-search').select();
        });

        function makeRecipientRow(id, name, status, button) {
            return '<tr><td>' + id + '</td><td>' + name + '</td><td>' + status + '</td><td>' + button + '</td></tr>';
        }

        function getChooseButton(id, name) {
            var button = '<button type="button" class="btn btn-xs btn-primary" ';
            button += 'onclick="chooseRecipient(' + id + ', \'' + name + '\')">Choose</button>';

            return button;
        }

        function searchBy() {
            var type = $('#search-type :selected').text();
            $('#recipient-search-fg > label').html(type);
            $('#recipient-search-fg input').attr('placeholder', 'Enter ' + type);
            $('#recipient-search-fg input').val('');
        }

        function chooseRecipient(id, name) {
            $('#recipientModal').modal('hide');
            $('#recipient-display').show();
            $('#recipient').val(id);
            $('#recipient-display').html(id + ' - ' + name);
            $('#recipient-button').hide();
        }
    </script>
@stop
