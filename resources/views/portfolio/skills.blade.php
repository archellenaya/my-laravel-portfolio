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
