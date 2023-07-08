@extends('la.layouts.app')

@section('contentheader_title')
    <a @ajaxload href="{{ url(config('laraadmin.adminRoute') . '/backups') }}">@lang('la_backup.backups')</a> :
@endsection
@section('contentheader_description', app('translator')->get('la_backup.backup_listing'))
@section('section', app('translator')->get('la_backup.backups'))
@section('sub_section', app('translator')->get('common.listing'))
@section('htmlheader_title', app('translator')->get('la_backup.backup_listing'))

@section('headerElems')
    @la_access('Backups', 'create')
        <button class="btn btn-success btn-sm pull-right" id="CreateBackup">@lang('la_backup.create_backup')</button>
    @endla_access
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
            <table id="dt_backups" class="table table-bordered">
                <thead>
                    <tr class="success">
                        @foreach ($listing_cols as $col)
                            <th>{{ $module->fields[$col]['label'] ?? ucfirst($col) }}</th>
                        @endforeach
                        @if ($show_actions)
                            <th>@lang('common.actions')</th>
                        @endif
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('styles')
@endpush

@push('scripts')
    <script>
        var dt_backups = null;

        $(function() {
            dt_backups = $("#dt_backups").DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url(config('laraadmin.adminRoute') . '/backup_dt_ajax') }}",
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
                    @endforeach
                    @if ($show_actions)
                        {
                            data: 'dt_action',
                            name: 'dt_action',
                        },
                    @endif
                ],
                @if ($show_actions)
                    columnDefs: [{
                        orderable: false,
                        targets: [-1]
                    }],
                @endif
            });

            @la_access("Backups", "create")
                $("#CreateBackup").on("click", function() {
                    $.ajax({
                        url: "{{ url(config('laraadmin.adminRoute') . '/create_backup_ajax') }}",
                        method: 'POST',
                        beforeSend: function() {
                            $("#CreateBackup").html('<i class="fa fa-refresh fa-spin"></i> @lang('la_backup.creating_backup')...');
                        },
                        headers: {
                            'X-CSRF-Token': $('input[name="_token"]').val()
                        },
                        success: function(data) {
                            if (data.status == "success") {
                                $("#CreateBackup").html('<i class="fa fa-check"></i> @lang('la_backup.backup_created')');
                                $('body').pgNotification({
                                    style: 'circle',
                                    title: '@lang('la_backup.backup_creation')',
                                    message: data.message,
                                    position: "top-right",
                                    timeout: 0,
                                    type: "success",
                                    thumbnail: '<img width="40" height="40" style="display: inline-block;" src="{{ asset('la-assets/img/laraadmin_logo_white.png') }}" data-src="assets/img/profiles/avatar.jpg" data-src-retina="assets/img/profiles/avatar2x.jpg" alt="">'
                                }).show();
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                $("#CreateBackup").html('Create Backup');
                                $('body').pgNotification({
                                    style: 'circle',
                                    title: '@lang('la_backup.backup_creation_failed')',
                                    message: data.message,
                                    position: "top-right",
                                    timeout: 0,
                                    type: "danger",
                                    thumbnail: '<img width="40" height="40" style="display: inline-block;" src="{{ asset('la-assets/img/laraadmin_logo_white.png') }}" data-src="assets/img/profiles/avatar.jpg" data-src-retina="assets/img/profiles/avatar2x.jpg" alt="">'
                                }).show();
                                console.error(data.output);
                            }
                        }
                    });
                });
            @endla_access
        });
    </script>
@endpush
