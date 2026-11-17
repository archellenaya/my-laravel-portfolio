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
                          <h3 class="timeline__title">{{$item->title}}</h3>
                          <p class="timeline__meta">{{$item->organization}}</p>
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
                          <h3 class="timeline__title">{{$item->title}}</h3>
                          <p class="timeline__meta">{{$item->organization}}</p>
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
