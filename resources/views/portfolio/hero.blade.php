  <!-- ### intro
  ================================================== -->
  <section id="{{$anchors['intro']}}" class="s-intro target-section">

      <div class="row intro-content wide">

          <figure class="intro-portrait">
              <img src="{{asset($data['headshot'])}}" alt="Formal portrait of {{$data['fullname']}}" width="600" height="600" fetchpriority="high" decoding="async">
          </figure>

          <div class="column intro-copy">
              <div class="text-pretitle with-line">
                  {{$data['greeting']}}
              </div>

              <h1 class="text-huge-title">Hi, I’m {{ $data['display_name'] }}.</h1>
              <p class="hero-specialization">{{ $data['role'] }} · {{ $hero['specialization'] }}</p>
              <p class="hero-summary">{{ $data['summary'] }}</p>
              <ul class="portfolio-badges">
                  <li>{{ $hero['experience_label'] }}</li>
                  <li>Based in {{ $data['location'] }}</li>
                  @if ($availability['remote'] === true)<li>Available for remote employment</li>@endif
                  @if ($availability['freelance'] === true)<li>Available for freelance projects</li>@endif
              </ul>
              <div class="hero-actions">
                  <a href="{{ url($resumePath) }}">Download Resume</a>
                  <a href="#{{ $anchors['works'] }}">View Projects</a>
                  <a href="#{{ $anchors['contact'] }}" data-contact-cta="hero">Contact</a>
              </div>
          </div>

          <ul class="intro-social">
             @if ($data['linkedin_url'])<li><a href="{{ $data['linkedin_url'] }}" target="_blank" rel="noopener noreferrer" data-social="linkedin">LinkedIn</a></li>@endif
              @if ($data['facebook_url'])<li><a href="{{ $data['facebook_url'] }}" target="_blank" rel="noopener noreferrer" data-social="facebook">Facebook</a></li>@endif
              @if ($data['instagram_url'])<li><a href="{{ $data['instagram_url'] }}" target="_blank" rel="noopener noreferrer" data-social="instagram">Instagram</a></li>@endif
              @if ($data['whatsapp'])<li><a href="https://wa.me/{{ $data['whatsapp'] }}" target="_blank" rel="noopener noreferrer" data-social="whatsapp">WhatsApp</a></li>@endif
              @if ($data['github_url'])<li><a href="{{ $data['github_url'] }}" target="_blank" rel="noopener noreferrer" data-social="github">GitHub</a></li>@endif
          </ul>

      </div> <!-- end intro content -->

      <a href="#{{$anchors['about']}}" class="intro-scrolldown smoothscroll" aria-label="Read about me">
          <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd"><path d="M11 21.883l-6.235-7.527-.765.644 7.521 9 7.479-9-.764-.645-6.236 7.529v-21.884h-1v21.883z"/></svg>
      </a>

  </section> <!-- end s-intro -->
