        <header class="s-header">
            <div class="header-mobile">
                <span class="mobile-home-link"><a href="{{url('/')}}">{{$data['display_name']}}.</a></span>
                <button type="button" class="mobile-menu-toggle" aria-label="Open navigation menu" aria-expanded="false" aria-controls="main-navigation"><span aria-hidden="true">Menu</span></button>
            </div>

            <div id="main-navigation" class="row wide main-nav-wrap">
                <nav class="column lg-12 main-nav" aria-label="Main navigation">
                    <ul>
                        <li><a href="{{url('/')}}" class="home-link">{{$data['display_name']}}.</a></li>
                        <li class="current"><a href="#{{$anchors['intro']}}" class="smoothscroll">Intro</a></li>
                        <li><a href="#{{$anchors['about']}}" class="smoothscroll">About</a></li>
                        <li><a href="#{{$anchors['works']}}" class="smoothscroll">Works</a></li>
                        <li><a href="#{{$anchors['contact']}}" class="smoothscroll">Say Hello</a></li>
                    </ul>
                </nav>
            </div>
        </header> <!-- end s-header -->

