  <!-- ### about
  ================================================== -->
  <section id="{{$anchors['about']}}" class="s-about target-section">

      <div class="row about-info wide" data-animate-block>

          <div class="column lg-6 md-12 about-info__pic-block">
              <img loading="lazy" decoding="async" src="{{asset($data['photo'])}}"
                    srcset="{{asset($data['photo'])}} 1x, {{asset($data['photo_retina'])}} 2x" alt="{{$data['fullname']}}" width="600" height="600" class="about-info__pic" data-animate-el>
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
                  <a href="{{ url($resumePath) }}" class="btn btn--medium u-fullwidth" data-animate-el>Download CV</a>

              </div>
          </div>
      </div> <!-- about-info -->


@include('portfolio.skills')
@include('portfolio.experience')
  </section> <!-- end s-about -->
