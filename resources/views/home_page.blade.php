@extends('layouts.app')

@section('content')
   
  <!-- ### intro
  ================================================== -->
  <section id="intro" class="s-intro target-section">

      <div class="row intro-content wide">

          <div class="column">
              <div class="text-pretitle with-line">
                  {{$data['greeting']}}
              </div>

              <h1 class="text-huge-title">
                  I am {{$data['display_name']}}, <br>
                  {{$data['role']}}<br>
                  based in {{$data['location']}}.
              </h1>
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
              <img src="{{asset($data['photo'])}}" 
                    srcset="{{asset($data['photo'])}} 1x, {{asset($data['photo_retina'])}} 2x" alt="{{$data['fullname']}}" class="about-info__pic" data-animate-el>
          </div>

          <div class="column lg-6 md-12">
              <div class="about-info__text" >

                  <h2 class="text-pretitle with-line" data-animate-el>
                      About
                  </h2>
                  <p class="attention-getter" data-animate-el>
                      {{$data['summary']}}
                  </p>
                  <p data-animate-el>
                      {{$data['about']}}
                  </p>
                  <a href="{{ url('/download-cv') }}" class="btn btn--medium u-fullwidth" data-animate-el>Download CV</a>

              </div>
          </div>
      </div> <!-- about-info -->


      <div class="row about-expertise" data-animate-block>
          <div class="column lg-12">

              <h2 class="text-pretitle" data-animate-el>Expertise</h2>

              <ul class="skills-list h1" data-animate-el>
                  @foreach($expertise as $item)
                      <li>{{$item->title}}</li>
                  @endforeach
              </ul>  
          </div>
          <div class="column lg-12" data-animate-el>
              <h3>Technical Skills</h3>
              <dl>
                  @foreach($skills as $item)
                      <dt>{{$item->title}}</dt><dd>{{$item->description}}</dd>
                  @endforeach
              </dl>
          </div>
      </div> <!-- end about-expertise -->


      <div class="row about-timelines" data-animate-block>

          <div class="column lg-6 tab-12">

              <h2 class="text-pretitle" data-animate-el>
                  Experience
              </h2>

              <div class="timeline" data-animate-el>

                  @foreach($experiences as $item)
                  <div class="timeline__block">
                      <div class="timeline__bullet"></div>
                      <div class="timeline__header">
                          <h4 class="timeline__title">{{$item->title}}</h4>
                          <h5 class="timeline__meta">{{$item->organization}}</h5>
                          <p class="timeline__timeframe">{{$item->period}}</p>
                      </div>
                      <div class="timeline__desc"><ul>
                          @foreach(explode("\n", $item->description ?? '') as $line)
                              @if(trim($line))<li>{{$line}}</li>@endif
                          @endforeach
                      </ul></div>
                  </div>
                  @endforeach
              </div> <!-- end timeline -->

          </div> <!-- end column -->

          <div class="column lg-6 tab-12">

              <h2 class="text-pretitle" data-animate-el>
                  Education
              </h2>

              <div class="timeline" data-animate-el>

                  @foreach($education as $item)
                  <div class="timeline__block">
                      <div class="timeline__bullet"></div>
                      <div class="timeline__header">
                          <h4 class="timeline__title">{{$item->title}}</h4>
                          <h5 class="timeline__meta">{{$item->organization}}</h5>
                          <p class="timeline__timeframe">{{$item->period}}</p>
                      </div>
                      <div class="timeline__desc"><ul>
                          @foreach(explode("\n", $item->description ?? '') as $line)
                              @if(trim($line))<li>{{$line}}</li>@endif
                          @endforeach
                      </ul></div>
                  </div>
                  @endforeach
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
                  Selected Projects
              </h2>
              <p class="h1" data-animate-el>
                {{$data['works_intro']}}
                
              </p>
              <small><b>Note:</b> {{$data['confidentiality']}}</small>
                <ul class="folio-list row block-lg-one-half block-stack-on-1000">
                    @php
                        $counter = 1;
                    @endphp
                   @if($projects)
                        @foreach ($projects as $project) 
                            <li class="folio-list__item column" data-animate-el>
                                <a class="folio-list__item-link" href="#modal-0{{$counter++}}">
                                    <div class="folio-list__item-pic">
                                        <img src="{{asset($project['image'])}}"
                                                srcset="{{asset($project['image'])}} 1x, {{$project['image']}} 2x"  alt="">
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
                                <a class="folio-list__proj-link" href="#modal-0{{$counter - 1}}" title="View project details">
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
                        <img src="{{asset($project['image'])}}" alt="">
            
                        <div class="modal-popup__desc" style="text-align: left;">
                            <h5>{{$project['title']}}</h5>
                            <p>{{$project['description']}}</p>
                            <ul class="modal-popup__cat">
                                @foreach (array_filter(array_map('trim', explode(',', $project['tags'] ?? ''))) as $tag) 
                                    <li>{{$tag}}</li>
                                @endforeach
                            </ul>
                        </div>

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
                  {{$data['contact_intro']}}
              </p>
          </div>
      </div> <!-- end contact-top -->

      <div class="row contact-bottom">
          <div class="column lg-4 md-6 tab-6 stack-on-550 contact-block">
              <h3 class="text-pretitle">Reach me at</h3>
              <p class="contact-links">
                  <a href="mailto:{{$data['email']}}" class="mailtoui">{{$data['email']}}</a> <br>
                  <span>{{$data['contact_location']}}</span><br>
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