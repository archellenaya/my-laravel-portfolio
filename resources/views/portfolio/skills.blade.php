<div class="row about-expertise" data-animate-block>
    <div class="column lg-12">
        <h2 class="text-pretitle" data-animate-el>Technical Skills</h2>
        <div class="portfolio-content-grid">
            @foreach ($skillGroups as $group)
                <article class="portfolio-content-card">
                    <h3>{{ $group['title'] }}</h3>
                    <ul>
                        @foreach ($group['items'] as $item)
                            <li>{{ $item['title'] }}@if ($item['description']): {{ $item['description'] }}@endif</li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </div>
</div>
