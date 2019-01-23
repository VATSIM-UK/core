<script src="https://code.jquery.com/jquery-3.2.1.min.js"
        integrity="sha384-xBuQ/xzmlsLoJpyjoggmTEz8OWUFM0/RC5BsqQBDX2v5cMvDHcMakNTNrHIW2I5f"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>

<!-- Morris.js charts -->
<script src='//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js'></script>
<script src='/AdminLTE/js/plugins/morris/morris.min.js'></script>
<!-- Sparkline -->
<script src='/AdminLTE/js/plugins/sparkline/jquery.sparkline.min.js'></script>
<!-- jvectormap -->
<script src='/AdminLTE/js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js'></script>
<script src='/AdminLTE/js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js'></script>
<!-- jQuery Knob Chart -->
<script src='/AdminLTE/js/plugins/knob/jquery.knob.js'></script>
<!-- daterangepicker -->
<script src='/AdminLTE/js/plugins/daterangepicker/moment.js'></script>
<script src='/AdminLTE/js/plugins/daterangepicker/daterangepicker.js'></script>
<!-- datepicker -->
<script src='/AdminLTE/js/plugins/datepicker/bootstrap-datepicker.js'></script>
<!-- timepicker -->
<script src='/AdminLTE/js/plugins/timepicker/bootstrap-timepicker.min.js'></script>
<!-- Bootstrap WYSIHTML5 -->
<script src='/AdminLTE/js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js'></script>
<!-- BootstrapSwitch -->
<script src='//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.0.2/js/bootstrap-switch.min.js'></script>
<!-- BootstrapSelect -->
<script src='//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.5.4/bootstrap-select.min.js'></script>
<!-- BootstrapConfirmation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-confirmation/1.0.5/bootstrap-confirmation.min.js"
        integrity="sha384-vMMciU9KnFBubM1yw+Q+6f68+ZHeeD0LPvydPm6xdw75vMiYRB03L7+4K5gGoh5w"
        crossorigin="anonymous"></script><!-- iCheck -->
<script src='/AdminLTE/js/plugins/iCheck/icheck.min.js'></script>

<!-- AdminLTE App -->
<script src='/AdminLTE/js/app.min.js'></script>

<script language="javascript" type="text/javascript">
    $('.selectpicker').selectpicker();
    $('[data-toggle="confirmation"]').confirmation({
        placement: "top",
        btnOkClass: "btn btn-xs btn-primary",
        btnCancelClass: "btn btn-xs",
        singleton: true,
        onConfirm: function (event, element) {
            onConfirm(event, element)
        },
    });

    // override this function to implement
    function onConfirm(event, element) {
    }

    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

    ga('create', 'UA-13128412-6', 'auto');
    ga('send', 'pageview');

</script>
@yield('scripts')