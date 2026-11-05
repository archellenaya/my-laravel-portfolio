<!DOCTYPE html>
<html class="no-js" lang="en">
<head> 
    <!--- basic page needs
    ================================================== -->
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
    <title>{{$data['page_title']}}</title>
    <meta name="description" content="{{$data['meta_description']}}">
    <meta name="author" content="{{$data['fullname']}}">

    <!-- mobile specific metas
    ================================================== -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preload" as="image" href="{{asset($data['headshot'])}}" fetchpriority="high">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Public+Sans:wght@400;500;600;700&display=swap">

    <!-- CSS
    ================================================== -->
    <link rel="stylesheet" href="{{asset('css/vendor.css')}}">
    <link rel="stylesheet" href="{{asset('css/styles.css')}}">

    <!-- favicons
    ================================================== -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('favicon-16x16.png')}}">
    <link rel="manifest" href="{{asset('site.webmanifest')}}">

</head>

<body id="top">
    <a class="skip-link" href="#main-content">Skip to content</a> 
 
    <!-- # page wrap
    ================================================== -->
    <div class="s-pagewrap"> 
        <div class="circles" aria-hidden="true">
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
                <span class="mobile-home-link"><a href="{{url('/')}}">{{$data['display_name']}}.</a></span>
                <button type="button" class="mobile-menu-toggle" aria-label="Open navigation menu" aria-expanded="false" aria-controls="main-navigation"><span aria-hidden="true">Menu</span></button>
            </div>

            <div id="main-navigation" class="row wide main-nav-wrap">
                <nav class="column lg-12 main-nav" aria-label="Main navigation">
                    <ul>
                        <li><a href="{{url('/')}}" class="home-link">{{$data['display_name']}}.</a></li>
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
        <main id="main-content" class="s-content" tabindex="-1">
            <div class="particle-backdrop" aria-hidden="true">
                <canvas class="hero-particles" aria-hidden="true"></canvas>
                <template id="globe-logos">
                    @foreach($tools as $tool)
                    <img src="{{asset($tool->image)}}" alt="{{$tool->title}}" data-appearance="{{$tool->appearance}}">
                    @endforeach
                </template>
            </div>
 
            @yield('content') 

        </main> <!-- end s-content --> 
        <!-- ## footer
        ================================================== -->
        <footer class="s-footer"> 
            <div class="row">
                <div class="column ss-copyright">
                    <span>© {{date('Y')}} {{$data['display_name']}}</span> 
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
    <script defer src="{{asset('js/main.js')}}"></script>
    <script defer src="{{asset('js/particles.js')}}"></script>
</body>
</html>