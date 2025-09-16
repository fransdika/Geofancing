<!DOCTYPE html>
<html data-layout="topnav">

<head>
    @include('layouts.partials.title-meta')

    @include('layouts.partials.head-css')
</head>

<body>

    <div class="wrapper">

        @include('layouts.partials.topbar')
        @include('layouts.partials.horizontal-nav')


        <div class="page-content">
            <div class="page-container">

                @yield('content')

            </div>
            @include('layouts.partials.footer')
        </div>

    </div>

    @include('layouts.partials.customizer')

    @include('layouts.partials.footer-scripts')
</body>

</html>
