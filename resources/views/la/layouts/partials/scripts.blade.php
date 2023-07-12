<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 2.2.3 + Vue.js 2.1.4 -->
<script src="{{ asset('la-assets/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/vue.js/vue.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/vue.js/vue-resource.min.js') }}"></script>

<script src="{{ asset('la-assets/plugins/bootstrap-slider/bootstrap-slider.js') }}"></script>
<script src="{{ asset('la-assets/plugins/jQueryUI/jquery-ui.js') }}"></script>
<script src="{{ asset('la-assets/plugins/nestable/jquery.nestable.js') }}"></script>

<!-- Bootstrap 3.3.2 JS -->
<script src="{{ asset('la-assets/js/bootstrap.min.js') }}" type="text/javascript"></script>

<!-- Libraries -->
<script src="{{ asset('la-assets/plugins/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/datatables/js/datatables.bootstrap.js') }}"></script>
<script src="{{ asset('la-assets/plugins/jquery-validation/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('la-assets/plugins/select2/select2.full.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('la-assets/plugins/bootstrap-datetimepicker/moment.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('la-assets/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.js') }}" type="text/javascript"></script>
<script src="{{ asset('la-assets/plugins/typeahead/typeahead.bundle.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/summernote/summernote.min.js') }}"></script>
{{-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDvaUg89uMNUQ3CSkUpio6dD0IudZ2ZWmQ&libraries=places"></script> --}}
{{-- <script src="{{ asset('la-assets/plugins/locationpicker/locationpicker.jquery.min.js') }}"></script> --}}
<script src="{{ asset('la-assets/plugins/colorpicker/bootstrap-colorpicker.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/bootstrap-timepicker/bootstrap-timepicker.min.js') }}"></script>
<script>
    paceOptions = {
        ajax: {
            ignoreURLs: ['{{ env('SOCKET_SERVER') . ':' . env('SOCKET_PORT') }}']
        }
    }
</script>
<script src="{{ asset('la-assets/plugins/pace/pace.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/pjax/jquery.pjax.js') }}"></script>
<script src="{{ asset('la-assets/plugins/socket.io/socket.io.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/stickytabs/jquery.stickytabs.js') }}" type="text/javascript"></script>
<script src="{{ asset('la-assets/plugins/slimScroll/jquery.slimscroll.min.js') }}" type="text/javascript"></script>

<script src="{{ asset('/la-assets/plugins/bootstrap-editable/bootstrap-editable.min.js') }}"></script>

<!-- Iconpicker -->
<script src="{{ asset('la-assets/plugins/iconpicker/fontawesome-iconpicker.js') }}"></script>


<!-- script for dashboard -->
<!-- daterangepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
<script src="{{ asset('la-assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Morris.js charts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="{{ asset('la-assets/plugins/morris/morris.min.js') }}"></script>
<!-- jvectormap -->
<script src="{{ asset('la-assets/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
<!-- Bootstrap WYSIHTML5 -->
<script src="{{ asset('la-assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}"></script>
<!-- Sparkline -->
<script src="{{ asset('la-assets/plugins/sparkline/jquery.sparkline.min.js') }}"></script>
<!-- end script for dashboard -->

<!-- Main App -->
<script src="{{ asset('la-assets/js/app.js') }}?{{ filemtime(base_path('public/la-assets/js/app.js')) }}" type="text/javascript"></script>

@stack('scripts')
