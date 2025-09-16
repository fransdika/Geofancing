<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'MHR Geofancing')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons (global) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root { --bg:#ffffff; --text:#000000; --accent:#0d6efd; }
        body { background: var(--bg); color: var(--text); }
        body.dark-mode {
            --bg:#111827; --text:#f3f4f6; --accent:#3b82f6;
            background-color: var(--bg); color: var(--text);
        }
        .navbar-dark .navbar-nav .nav-link.active { font-weight:bold; color: var(--accent) !important; }
        main { min-height: calc(100vh - 56px); } /* keep content full-height under navbar */
    </style>

    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="{{ url('/mapTilerRadius') }}">MHR Geofancing</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('mapTilerRadius') ? 'active' : '' }}" href="{{ url('/mapTilerRadius') }}">
                    <i class="fa fa-globe"></i> PTD Actual Performance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('ptdMasterSetUp') ? 'active' : '' }}" href="{{ url('/ptdMasterSetUp') }}">
                    <i class="fa fa-road"></i> PTD Plan Setup
                </a>
            </li>
        </ul>
        <button id="toggleTheme" class="btn btn-sm btn-outline-light">
            <i class="fa fa-moon"></i> Dark Mode
        </button>
    </div>
</nav>

<main class="py-3">
    @yield('content')
</main>

<!-- Global Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function(){
    const KEY='theme';
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    function apply(mode){
        const isDark = mode==='dark' || (mode==='auto' && prefersDark);
        document.body.classList.toggle('dark-mode', isDark);
        const btn=document.getElementById('toggleTheme');
        if(btn) btn.innerHTML = isDark ? '<i class="fa fa-sun"></i> Light Mode' : '<i class="fa fa-moon"></i> Dark Mode';
    }
    let saved=localStorage.getItem(KEY); if(!saved){saved='auto'; localStorage.setItem(KEY,saved);}
    apply(saved);
    if(window.matchMedia){
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change',()=>{
            if((localStorage.getItem(KEY)||'auto')==='auto') apply('auto');
        });
    }
    $('#toggleTheme').on('click', function(){
        const cur=localStorage.getItem(KEY)||'auto';
        const next = cur==='light' ? 'dark' : (cur==='dark' ? 'auto' : 'light');
        localStorage.setItem(KEY,next); apply(next);
    });
})();
</script>

@stack('scripts')
</body>
</html>
