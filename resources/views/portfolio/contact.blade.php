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
                  <a href="mailto:{{$data['email']}}" class="mailtoui">{{$data['email']}}</a> <br>
                  <span>{{$data['contact_location']}}</span><br>
                  <a href="tel:{{$data['contact_number']}}">{{$data['contact_number']}}</a>
              </p>
          </div>
          <div class="column lg-4 md-5 tab-6 stack-on-550 contact-block">
              <h3 class="text-pretitle">Social</h3>
              <ul class="contact-social">
                <li><a href="{{$data['linkedin_url']}}" target="_blank" rel="noopener noreferrer">Linkedin</a></li>
                <li><a href="{{$data['facebook_url']}}" target="_blank" rel="noopener noreferrer">Facebook</a></li>
                <li><a href="{{$data['instagram_url']}}" target="_blank" rel="noopener noreferrer">Instagram</a></li>
                <li><a href="https://wa.me/{{$data['whatsapp']}}" target="_blank" rel="noopener noreferrer">Whatsapp</a></li>
                <li><a href="{{$data['github_url']}}" target="_blank" rel="noopener noreferrer">Github</a></li>
              </ul>
          </div>
          <div class="column lg-4 md-12 contact-block">
              <a href="mailto:{{$data['email']}}" class="mailtoui btn btn--medium u-fullwidth contact-btn">Say Hello.</a>
          </div>
      </div> <!-- end contact-bottom -->

  </section> <!-- end contact -->
