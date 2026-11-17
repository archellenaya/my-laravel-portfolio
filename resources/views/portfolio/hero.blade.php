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

              <h1 class="text-huge-title">
                  I am {{$data['display_name']}}, <br>
                  {{$data['role']}}<br>
                  based in {{$data['location']}}.
              </h1>
          </div>

          <ul class="intro-social">
             <li><a href="{{$data['linkedin_url']}}" target="_blank" rel="noopener noreferrer">Linkedin</a></li>
              <li><a href="{{$data['facebook_url']}}" target="_blank" rel="noopener noreferrer">Facebook</a></li>
              <li><a href="{{$data['instagram_url']}}" target="_blank" rel="noopener noreferrer">Instagram</a></li>
              <li><a href="https://wa.me/{{$data['whatsapp']}}" target="_blank" rel="noopener noreferrer">Whatsapp</a></li>
              <li><a href="{{$data['github_url']}}" target="_blank" rel="noopener noreferrer">Github</a></li>
          </ul>

      </div> <!-- end intro content -->

      <a href="#{{$anchors['about']}}" class="intro-scrolldown smoothscroll" aria-label="Read about me">
          <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd"><path d="M11 21.883l-6.235-7.527-.765.644 7.521 9 7.479-9-.764-.645-6.236 7.529v-21.884h-1v21.883z"/></svg>
      </a>

  </section> <!-- end s-intro -->
