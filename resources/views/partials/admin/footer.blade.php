@php
use App\Models\Utility;
@endphp



<!-- [ Main Content ] end -->
<footer class="dash-footer">
    <div class="footer-wrapper">
        <div class="py-1">
{{--            <span class="text-muted">  {{(Utility::getValByName('footer_text')) ? Utility::getValByName('footer_text') :  __('Copyright AccountGo') }} {{ date('Y') }}</span>--}}
            <span class="text-muted">  {{  __('Copyright Impulsion') }} {{ date('Y') }}</span>
        </div>
        <div class="py-1">
    {{--            <ul class="list-inline m-0">--}}
    {{--                <li class="list-inline-item">--}}
    {{--                    <a class="link-secondary" href="javascript:">Joseph William</a>--}}
    {{--                </li>--}}
    {{--                <li class="list-inline-item">--}}
    {{--                    <a class="link-secondary" href="javascript:">About Us</a>--}}
    {{--                </li>--}}
    {{--                <li class="list-inline-item">--}}
    {{--                    <a class="link-secondary" href="javascript:">Blog</a>--}}
    {{--                </li>--}}
    {{--                <li class="list-inline-item">--}}
    {{--                    <a class="link-secondary" href="javascript:">Library</a>--}}
    {{--                </li>--}}
    {{--            </ul>--}}
        </div>
    </div>
</footer>



<!-- Warning Section Ends -->
<!-- Required Js -->
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery.form.js') }}"></script>
<script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/dash.js') }}"></script>

<script src="{{asset('assets/js/plugins/datepicker-full.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/dropzone-amd-module.min.js')}}"></script>

<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>

<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>

<!-- sweet alert Js -->
{{--<script src="{{ asset('assets/js/plugins/sweetalert.min.js') }}"></script>--}}


<!--Botstrap switch-->
<script src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>


<!-- Apex Chart -->
<script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>

<script src="{{ asset('js/custom.js') }}"></script>

@if($message = Session::get('success'))
    <script>
        show_toastr('success', '{!! $message !!}');
    </script>
@endif
@if($message = Session::get('error'))
    <script>
        show_toastr('error', '{!! $message !!}');
    </script>
@endif
@stack('script-page')

@if(\App\Models\Utility::getValByName1('gdpr_cookie') == 'on')
    <script type="text/javascript">
        var defaults = {
            'messageLocales': {
                /*'en': 'We use cookies to make sure you can have the best experience on our website. If you continue to use this site we assume that you will be happy with it.'*/
                'en': "{{\App\Models\Utility::getValByName1('cookie_text')}}"
            },
            'buttonLocales': {
                'en': 'Ok'
            },
            'cookieNoticePosition': 'bottom',
            'learnMoreLinkEnabled': false,
            'learnMoreLinkHref': '/cookie-banner-information.html',
            'learnMoreLinkText': {
                'it': 'Saperne di più',
                'en': 'Learn more',
                'de': 'Mehr erfahren',
                'fr': 'En savoir plus'
            },
            'buttonLocales': {
                'en': 'Ok'
            },
            'expiresIn': 30,
            'buttonBgColor': '#d35400',
            'buttonTextColor': '#fff',
            'noticeBgColor': '#000000',
            'noticeTextColor': '#fff',
            'linkColor': '#009fdd'
        };
    </script>
    <script src="{{ asset('assets/js/cookie.notice.js')}}"></script>
@endif



<script>

    feather.replace();
    // var pctoggle = document.querySelector("#pct-toggler");
    // if (pctoggle) {
    //     pctoggle.addEventListener("click", function () {
    //         if (
    //             !document.querySelector(".pct-customizer").classList.contains("active")
    //         ) {
    //             document.querySelector(".pct-customizer").classList.add("active");
    //         } else {
    //             document.querySelector(".pct-customizer").classList.remove("active");
    //         }
    //     });
    // }

    // var themescolors = document.querySelectorAll(".themes-color > a");
    // for (var h = 0; h < themescolors.length; h++) {
    //     var c = themescolors[h];
    //
    //     c.addEventListener("click", function (event) {
    //         var targetElement = event.target;
    //         if (targetElement.tagName == "SPAN") {
    //             targetElement = targetElement.parentNode;
    //         }
    //         var temp = targetElement.getAttribute("data-value");
    //         removeClassByPrefix(document.querySelector("body"), "theme-");
    //         document.querySelector("body").classList.add(temp);
    //     });
    // }

    // var custthemebg = document.querySelector("#cust-theme-bg");
    // custthemebg.addEventListener("click", function () {
    //     if (custthemebg.checked) {
    //         document.querySelector(".dash-sidebar").classList.add("transprent-bg");
    //         document
    //             .querySelector(".dash-header:not(.dash-mob-header)")
    //             .classList.add("transprent-bg");
    //     } else {
    //         document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
    //         document
    //             .querySelector(".dash-header:not(.dash-mob-header)")
    //             .classList.remove("transprent-bg");
    //     }
    // });



    function removeClassByPrefix(node, prefix) {
        for (let i = 0; i < node.classList.length; i++) {
            let value = node.classList[i];
            if (value.startsWith(prefix)) {
                node.classList.remove(value);
            }
        }
    }
</script>
