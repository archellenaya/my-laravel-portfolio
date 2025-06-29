@extends('layouts.app')

@section('content')
   
  <!-- ### intro
  ================================================== -->
  <section id="intro" class="s-intro target-section">

      <div class="row intro-content wide">

          <div class="column">
              <div class="text-pretitle with-line">
                  Hello World
              </div>

              <h2 class="text-huge-title">
                  I am {{$data['display_name']}}, <br>
                  Laravel Backend<br>
                  developer based<br>  
                  in Philippines.
              </h2>
          </div>

          <ul class="intro-social"> 
             <li><a href="{{$data['linkedin_url']}}" target="__blank">Linkedin</a></li>
              <li><a href="{{$data['facebook_url']}}" target="__blank">Facebook</a></li>
              <li><a href="{{$data['instagram_url']}}" target="__blank">Instagram</a></li>
              <li><a href="https://wa.me/{{$data['whatsapp']}}" target="__blank">Whatsapp</a></li>
              <li><a href="{{$data['github_url']}}" target="__blank">Github</a></li>
          </ul>

      </div> <!-- end intro content -->

      <a href="#about" class="intro-scrolldown smoothscroll">
          <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd"><path d="M11 21.883l-6.235-7.527-.765.644 7.521 9 7.479-9-.764-.645-6.236 7.529v-21.884h-1v21.883z"/></svg>
      </a>

  </section> <!-- end s-intro -->


  <!-- ### about
  ================================================== -->
  <section id="about" class="s-about target-section"> 

      <div class="row about-info wide" data-animate-block>

          <div class="column lg-6 md-12 about-info__pic-block">
              <img src="{{asset('images/about-photo.jpg')}}" 
                    srcset="{{asset('images/about-photo.jpg')}} 1x, {{asset('images/about-photo@2x.jpg')}} 2x" alt="" class="about-info__pic" data-animate-el>
          </div>

          <div class="column lg-6 md-12">
              <div class="about-info__text" >

                  <h2 class="text-pretitle with-line" data-animate-el>
                      About
                  </h2>
                  <p class="attention-getter" data-animate-el>
                      Backend-focused full-stack developer with over 6 years of professional experience, specializing in Laravel, Drupal, and WordPress development. Skilled in building and maintaining web applications, RESTful APIs, and custom CMS modules/plugins. Reliable, independent, and solutions-driven — with a strong preference for written communication and consistent delivery.
                      {{-- <br><span><i><small>Note: Many of my recent projects are part of private repositories under my current employer. While I can't share full source code, I'm actively working on releasing selected personal projects and code samples.</small></i></span> --}}
                  </p>
                  <a href="{{ asset("download-cv") }}" class="btn btn--medium u-fullwidth" data-animate-el>Download CV</a>

              </div>
          </div>
      </div> <!-- about-info -->


      <div class="row about-expertise" data-animate-block>
          <div class="column lg-12">

              <h2 class="text-pretitle" data-animate-el>Expertise</h2>

              <ul class="skills-list h1" data-animate-el>
                  <li>API Integrations</li>
                  <li>Wordpress</li>
                  <li>Drupal</li>
                  <li>Laravel</li> 
                  <li>Laravel Lumen</li> 
              </ul>
              <p>Experienced in:</p>
              <ul>
                  <li>Building large-scale Laravel applications</li>
                  <li>Developing and integrating RESTful APIs</li>
                  <li>Developing Wordpress Plugin</li>
                  <li>Creating custom CMS modules in Drupal</li>
                  <li>Using Git/GitHub and Bitbucket for version control</li>  
                  <li>Using Docker in building Drupal CMS</li>  
              </ul>
          </div>
      </div> <!-- end about-expertise -->


      <div class="row about-timelines" data-animate-block>

          <div class="column lg-6 tab-12">

              <h2 class="text-pretitle" data-animate-el>
                  Experience
              </h2>

              <div class="timeline" data-animate-el>

                  <div class="timeline__block">
                      <div class="timeline__bullet"></div>
                      <div class="timeline__header"> 
                        <h4 class="timeline__title"><span>Web Developer</h4>
                        <h5 class="timeline__meta">Webee Ltd/Born Digital - Malta, Europe</h5>
                        <p class="timeline__timeframe">September 18, 2018 – Present</p>
                      </div>
                      <div class="timeline__desc"> 
                            <ul>
                                <li>Maintained and improved backend systems for internal and client-facing platforms using Laravel.</li>
                                <li>Integrated REST APIs for WordPress WooCommerce and Laravel-based systems. Developed and maintained Drupal modules and forms using Paragraphs.</li>
                                <li>Developed and maintained Drupal modules and forms using Paragraphs.</li> 
                                <li>Collaborated with international project managers and developers across Malta and the Philippines.</li>
                                <li>Wrote clean, testable code for CMS-based websites and custom applications.</li> 
                            </ul>
                        </div>
                  </div> 

              </div> <!-- end timeline -->

          </div> <!-- end column -->

          <div class="column lg-6 tab-12">

              <h2 class="text-pretitle" data-animate-el>
                  Education
              </h2>

              <div class="timeline" data-animate-el>

                  <div class="timeline__block">
                      <div class="timeline__bullet"></div>
                      <div class="timeline__header"> 
                        <h4 class="timeline__title">Bachelor of Science in Computer Science, <span><small>Major in Software Engineering</small></span></h4> 
                        <h5 class="timeline__meta">Mindanao State University - Marawi City</h5>
                        <p class="timeline__timeframe">Graduated: June 2018</p>
                      </div>
                      <div class="timeline__desc"> 
                        <ul>
                            <li style="text-align: justify;">Studied core programming in Java and C++</li> 
                            <li style="text-align: justify;">Object Oriented Programming (Class/Object, Encapsulation, Inheritance, Polymorphism, etc)</li>
                            <li style="text-align: justify;">Data Structures (linked list, stacks and queues, arrays, graphs and trees)</li>
                            <li style="text-align: justify;">Completed coursework in web development, software engineering, and database management, Expert System, Data Mining</li>
                        </ul>
                      </div>
                  </div> 
                  

              </div> <!-- end timeline -->
              
          </div> <!-- end column -->


      </div> <!-- end about-timelines -->

  </section> <!-- end s-about -->


  <!-- ### works
  ================================================== -->
  <section id="works" class="s-works target-section">


      <div class="row works-portfolio">

          <div class="column lg-12" data-animate-block>

              <h2 class="text-pretitle" data-animate-el>
                  Recent Works
              </h2>
              <p class="h1" data-animate-el>
                I’ve been working on some exciting projects lately—feel free to take a look!
                
              </p>
              <small><b>Note:</b> While most of my professional projects are under NDA or in private repositories, I’m currently preparing public demos and code samples to showcase select skills and contributions.</small>
                <ul class="folio-list row block-lg-one-half block-stack-on-1000">
                    @php
                        $counter = 1;
                    @endphp
                   @if($projects)
                        @foreach ($projects as $project) 
                            <li class="folio-list__item column" data-animate-el>
                                <a class="folio-list__item-link" href="#modal-0{{$counter++}}">
                                    <div class="folio-list__item-pic">
                                        <img src="{{asset($project['image']['tile'])}}"
                                                srcset="{{asset($project['image']['tile'])}} 1x, {{$project['image']['tile']}} 2x"  alt="">
                                    </div>
                                    
                                    <div class="folio-list__item-text">
                                        <div class="folio-list__item-cat">
                                            {{$project['category']}}
                                        </div>
                                        <div class="folio-list__item-title">
                                            {{$project['title']}}
                                        </div>
                                    </div>
                                </a>
                                <a class="folio-list__proj-link" href="#" title="project link">
                                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.14645 3.14645C8.34171 2.95118 8.65829 2.95118 8.85355 3.14645L12.8536 7.14645C13.0488 7.34171 13.0488 7.65829 12.8536 7.85355L8.85355 11.8536C8.65829 12.0488 8.34171 12.0488 8.14645 11.8536C7.95118 11.6583 7.95118 11.3417 8.14645 11.1464L11.2929 8H2.5C2.22386 8 2 7.77614 2 7.5C2 7.22386 2.22386 7 2.5 7H11.2929L8.14645 3.85355C7.95118 3.65829 7.95118 3.34171 8.14645 3.14645Z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                                </a>
                            </li> 
                        @endforeach
                    @endif 
              </ul> <!-- end folio-list --> 
          </div> <!-- end column -->


          <!-- Modal Templates Popup
          -------------------------------------------- -->
        @php
            $counter = 1;
        @endphp
        @if($projects)
            @foreach ($projects as $project) 
                <div id="modal-0{{$counter++}}" hidden>
                    <div class="modal-popup" style="text-align: center;">
                        <img src="{{asset($project['image']['gallery'])}}" alt="">
            
                        <div class="modal-popup__desc" style="text-align: left;">
                            <h5>{{$project['title']}}</h5>
                            <p>{{$project['description']}}</p>
                            <ul class="modal-popup__cat">
                                @foreach ($project['tags'] as $tag) 
                                    <li>{{$tag}}</li>
                                @endforeach
                            </ul>
                        </div>
                        @foreach ($project['project_links'] as $link) 
                            <a href="{{$link['url']}}" class="modal-popup__details" target="__blank">{{$link['title']}}</a>
                        @endforeach
                    </div>
                </div> <!-- end modal -->
            @endforeach
        @endif   
      </div> <!-- end works-portfolio -->
 
  </section> <!-- end s-works -->


  <!-- ### contact
  ================================================== -->
  <section id="contact" class="s-contact target-section">

      <div class="row contact-top">
          <div class="column lg-12">
              <h2 class="text-pretitle">
                  Get In Touch
              </h2>

              <p class="h1">
                  Have a project or question? Let’s connect! I’m a full-stack developer with 6+ years of experience in Laravel, Drupal, and WordPress.
              </p>
          </div>
      </div> <!-- end contact-top -->

      <div class="row contact-bottom">
          <div class="column lg-4 md-6 tab-6 stack-on-550 contact-block">
              <h3 class="text-pretitle">Reach me at</h3>
              <p class="contact-links">
                  <a href="mailto:{{$data['email']}}" class="mailtoui">{{$data['email']}}</a> <br>
                  <a href="tel:{{$data['contact_number']}}">{{$data['contact_number']}}</a>
              </p>
          </div>
          <div class="column lg-4 md-5 tab-6 stack-on-550 contact-block">
              <h3 class="text-pretitle">Social</h3>
              <ul class="contact-social">
                <li><a href="{{$data['linkedin_url']}}" target="__blank">Linkedin</a></li>
                <li><a href="{{$data['facebook_url']}}" target="__blank">Facebook</a></li>
                <li><a href="{{$data['instagram_url']}}" target="__blank">Instagram</a></li>
                <li><a href="https://wa.me/{{$data['whatsapp']}}" target="__blank">Whatsapp</a></li>
                <li><a href="{{$data['github_url']}}" target="__blank">Github</a></li>
              </ul>
          </div>
          <div class="column lg-4 md-12 contact-block">
              <a href="mailto:{{$data['email']}}" class="mailtoui btn btn--medium u-fullwidth contact-btn">Say Hello.</a>
          </div>
      </div> <!-- end contact-bottom -->

  </section> <!-- end contact -->
@endsection