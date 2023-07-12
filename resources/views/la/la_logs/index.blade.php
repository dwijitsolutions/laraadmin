@extends('la.layouts.app')

@section('contentheader_title')
    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/la_logs') }}">@lang('la_la_log.la_logs')</a> :
@endsection
@section('contentheader_description', app('translator')->get('la_la_log.la_log_listing'))
@section('section', app('translator')->get('la_la_log.la_logs'))
@section('sub_section', app('translator')->get('common.listing'))
@section('htmlheader_title', app('translator')->get('la_la_log.la_log_listing'))

@section('headerElems')

@endsection

@section('main-content')

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="box box-success">
        <!--<div class="box-header"></div>-->
        <div class="box-body">
            <table id="dt_la_logs" class="table table-bordered">
                <thead>
                    <tr class="success">
                        @foreach ($listing_cols as $col)
                            <th>{{ $module->fields[$col]['label'] ?? ucfirst($col) }}</th>
                        @endforeach
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        #dt_la_logs .col_exp {
            background: url('{{ asset('la-assets/plugins/datatables/images/details_open.png') }}') no-repeat center center;
            cursor: pointer;
            width: 20px;
            height: 20px;
            display: inline-block;
            vertical-align: bottom;
            float: right;
        }

        #dt_la_logs tr.shown .col_exp {
            background: url('{{ asset('la-assets/plugins/datatables/images/details_close.png') }}') no-repeat center center;
        }

        #dt_la_logs>thead>tr>th:first-child {
            min-width: 20px;
        }

        .table-data-loading {
            padding: 20px 0px;
            text-align: center;
            color: #605ca8;
            font-size: 20px;
        }

        .table-wrapper-inner {
            background: #ecf0f5;
        }

        .inner-data-table {
            padding-left: 50px;
            width: 100%;
        }

        .inner-data-table td {
            padding: 5px 0px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        var dt_la_logs = null;

        $(function() {
            dt_la_logs = $("#dt_la_logs").DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url(config('laraadmin.adminRoute') . '/la_log_dt_ajax') }}",
                language: {
                    lengthMenu: "_MENU_",
                    search: "_INPUT_",
                    searchPlaceholder: '@lang('common.search')'
                },
                columns: [
                    @foreach ($listing_cols as $col)
                        {
                            data: '{{ $col }}',
                            name: '{{ $col }}'
                        },
                    @endforeach {
                        data: 'created_at',
                        name: 'created_at',
                    },
                ],
                "order": [
                    [0, "desc"]
                ]
            });

            $('#dt_la_logs tbody').on('click', '.col_exp', function() {
                var tr = $(this).closest('tr');
                var row = dt_la_logs.row(tr);

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // Open this row

                    // Show Loading
                    row.child("<div class='table-data-loading'><i class='fa fa-refresh fa-spin'></i></div>").show();
                    tr.addClass('shown');
                    tr.next().addClass('table-wrapper-inner');

                    // Get Details from Server
                    $.ajax({
                        url: "{{ url(config('laraadmin.adminRoute') . '/get_lalog_details') }}/" + $(this).attr('log_id'),
                        method: 'POST',
                        headers: {
                            'X-CSRF-Token': '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            if (data.status == "success") {
                                var lalog = data.lalog;
                                var content = JSON.stringify(JSON.parse(lalog.content), undefined, 4);

                                row.child('<table class="inner-data-table" cellpadding="5" cellspacing="0" border="0">' +
                                    '<tr>' +
                                    '<td width=160><b>Title:</b></td>' +
                                    '<td style="color:#605ca8;font-weight:bold;">' + lalog.type + " - " + lalog.title +
                                    '</td>' +
                                    '</tr>' +
                                    '<tr>' +
                                    '<td><b>Content:</b></td>' +
                                    '<td><pre>' + content + '</pre></td>' +
                                    '</tr>' +
                                    '</table>').show();

                                tr.next().addClass('table-wrapper-inner');
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
