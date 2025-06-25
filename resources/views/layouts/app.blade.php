<!DOCTYPE html>
<html class="no-js ss-preload" lang="en">
<head> 
    <!--- basic page needs
    ================================================== -->
    <meta charset="utf-8">
    <title>{{config('app.name')}}</title>
    <meta name="description" content="Full Stack Web & Software Developer with 6+ years of experience in Laravel, Drupal, and WordPress. Building scalable, efficient web solutions.">
    <meta name="author" content="{{$data['fullname']}}">

    <!-- mobile specific metas
    ================================================== -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS
    ================================================== -->
    <link rel="stylesheet" href="{{asset('css/vendor.css')}}">
    <link rel="stylesheet" href="{{asset('css/styles.css')}}">

    <!-- favicons
    ================================================== -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('favicon-16x16.png')}}">
    <link rel="manifest" href=""{{asset('site.webmanifest')}}">

</head>

<body id="top"> 
    <!-- # preloader
    ================================================== -->
    <div id="preloader">
        <div id="loader">
        </div>
    </div> 
    <!-- # page wrap
    ================================================== -->
    <div class="s-pagewrap"> 
        <div class="circles">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>  
        <!-- ## site header 
        ================================================== -->
        <header class="s-header"> 
            <div class="header-mobile">
                <span class="mobile-home-link"><a href="{{url('/')}}">Arch.</a></span>
                <a class="mobile-menu-toggle" href="#0"><span>Menu</span></a>
            </div>

            <div class="row wide main-nav-wrap">
                <nav class="column lg-12 main-nav">
                    <ul>
                        <li><a href="{{url('/')}}" class="home-link">Arch.</a></li>
                        <li class="current"><a href="#intro" class="smoothscroll">Intro</a></li>
                        <li><a href="#about" class="smoothscroll">About</a></li>
                        <li><a href="#works" class="smoothscroll">Works</a></li>
                        <li><a href="#contact" class="smoothscroll">Say Hello</a></li>
                    </ul>
                </nav>
            </div> 
        </header> <!-- end s-header -->
 
        <!-- ## main content
        ==================================================- -->
        <main class="s-content">
 
            @yield('content') 

        </main> <!-- end s-content --> 
        <!-- ## footer
        ================================================== -->
        <footer class="s-footer"> 
            <div class="row">
                <div class="column ss-copyright">
                    <span>© Copyright Arch</span> 
                    <span>Template adapted from <a href="https://www.styleshout.com/" target="_blank">StyleShout</a></span>
                </div>

                <div class="ss-go-top">
                    <a class="smoothscroll" title="Back to Top" href="#top">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M11 2.206l-6.235 7.528-.765-.645 7.521-9 7.479 9-.764.646-6.236-7.53v21.884h-1v-21.883z"/></svg>
                    </a>
                </div>
            </div> 
        </footer> <!-- end s-footer --> 
    </div> <!-- end -s-pagewrap --> 
    <!-- Java Script
    ================================================== -->
    <script src="{{asset('js/plugins.js')}}"></script>
    <script src="{{asset('js/main.js')}}"></script>
</body>
</html>