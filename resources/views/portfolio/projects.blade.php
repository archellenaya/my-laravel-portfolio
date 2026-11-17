  <!-- ### works
  ================================================== -->
  <section id="{{$anchors['works']}}" class="s-works target-section">


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
                   @if($projects)
                        @foreach ($projects as $project)
                            <li class="folio-list__item column" data-animate-el>
                                <a class="folio-list__item-link" href="#{{$project['modal_id']}}">
                                    <div class="folio-list__item-pic">
                                        <img loading="lazy" decoding="async" src="{{$project['image_url']}}"
                                                srcset="{{$project['image_url']}} 1x, {{$project['image_url']}} 2x" alt="{{$project['title']}}" width="456" height="306">
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
                                <a class="folio-list__proj-link" href="#{{$project['modal_id']}}" aria-label="View details: {{$project['title']}}">
                                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.14645 3.14645C8.34171 2.95118 8.65829 2.95118 8.85355 3.14645L12.8536 7.14645C13.0488 7.34171 13.0488 7.65829 12.8536 7.85355L8.85355 11.8536C8.65829 12.0488 8.34171 12.0488 8.14645 11.8536C7.95118 11.6583 7.95118 11.3417 8.14645 11.1464L11.2929 8H2.5C2.22386 8 2 7.77614 2 7.5C2 7.22386 2.22386 7 2.5 7H11.2929L8.14645 3.85355C7.95118 3.65829 7.95118 3.34171 8.14645 3.14645Z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                                </a>
                            </li>
                        @endforeach
                    @endif
              </ul> <!-- end folio-list -->
          </div> <!-- end column -->


          <!-- Modal Templates Popup
          -------------------------------------------- -->
        @if($projects)
            @foreach ($projects as $project)
                <dialog id="{{$project['modal_id']}}" class="project-dialog" aria-label="{{$project['title']}}">
                    <div class="modal-popup" style="text-align: center;">
                        <button type="button" class="dialog-close" data-close-dialog>Close project</button>
                        <img loading="lazy" decoding="async" src="{{$project['image_url']}}" alt="{{$project['title']}}">

                        <div class="modal-popup__desc" style="text-align: left;">
                            <h2>{{$project['title']}}</h2>
                            <p>{{$project['description']}}</p>
                            <ul class="modal-popup__cat">
                                @foreach ($project['tags_list'] as $tag)
                                    <li>{{$tag}}</li>
                                @endforeach
                            </ul>
                            @if($project['website_link'])
                                <a href="{{$project['website_link']}}" target="_blank" rel="noopener noreferrer">Visit {{$project['title']}} website</a>
                            @endif
                        </div>

                    </div>
                </dialog> <!-- end modal -->
            @endforeach
        @endif
      </div> <!-- end works-portfolio -->

  </section> <!-- end s-works -->
