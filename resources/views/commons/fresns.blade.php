<!doctype html>
<html lang="{{ App::setLocale($locale) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('WebPublishModule::fresns.name') }}</title>
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/static/css/fresns-panel.css">
    @stack('style')
</head>

<body>
    <header class="bg-body">
        @include('WebPublishModule::commons.header')
    </header>

    <main class="bg-body">
        @yield('content')
    </main>

    <footer>
        @include('WebPublishModule::commons.footer')
    </footer>

    <!--fresns tips-->
    <div class="fresns-tips">
        @include('FsView::commons.tips')
    </div>

    <script src="/static/js/bootstrap.bundle.min.js"></script>
    <script src="/static/js/jquery.min.js"></script>
    <script>
        // set timeout toast hide
        const setTimeoutToastHide = () => {
            $('.toast.show').each((k, v) => {
                setTimeout(function () {
                    $(v).hide();
                }, 1500);
            });
        };
        setTimeoutToastHide();

        // copyright-year
        var yearElement = document.querySelector('.copyright-year');
        var currentDate = new Date();
        var currentYear = currentDate.getFullYear();
        if (yearElement) {
            yearElement.textContent = currentYear;
        }
    </script>
    @stack('script')
</body>
</html>
