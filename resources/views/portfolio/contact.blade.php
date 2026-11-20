  <!-- ### contact
  ================================================== -->
  <section id="{{$anchors['contact']}}" class="s-contact target-section">

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
                  <a data-contact-cta="email" href="mailto:{{$data['email']}}" class="mailtoui">{{$data['email']}}</a> <br>
                  <span>{{$data['contact_location']}}</span><br>
                  <a data-contact-cta="phone" href="tel:{{$data['contact_number']}}">{{$data['contact_number']}}</a>
              </p>
          </div>
          <div class="column lg-4 md-5 tab-6 stack-on-550 contact-block">
              <h3 class="text-pretitle">Social</h3>
              <ul class="contact-social">
                @if ($data['linkedin_url'])<li><a href="{{ $data['linkedin_url'] }}" target="_blank" rel="noopener noreferrer" data-social="linkedin">LinkedIn</a></li>@endif
                @if ($data['facebook_url'])<li><a href="{{ $data['facebook_url'] }}" target="_blank" rel="noopener noreferrer" data-social="facebook">Facebook</a></li>@endif
                @if ($data['instagram_url'])<li><a href="{{ $data['instagram_url'] }}" target="_blank" rel="noopener noreferrer" data-social="instagram">Instagram</a></li>@endif
                @if ($data['whatsapp'])<li><a href="https://wa.me/{{ $data['whatsapp'] }}" target="_blank" rel="noopener noreferrer" data-social="whatsapp">WhatsApp</a></li>@endif
                @if ($data['github_url'])<li><a href="{{ $data['github_url'] }}" target="_blank" rel="noopener noreferrer" data-social="github">GitHub</a></li>@endif
              </ul>
          </div>
          <div class="column lg-4 md-12 contact-block">
              <a data-contact-cta="email" href="mailto:{{$data['email']}}" class="mailtoui btn btn--medium u-fullwidth contact-btn">Say Hello.</a>
              <a class="contact-resume" href="{{ url($resumePath) }}">Download Resume</a>
          </div>
      </div> <!-- end contact-bottom -->

  </section> <!-- end contact -->
