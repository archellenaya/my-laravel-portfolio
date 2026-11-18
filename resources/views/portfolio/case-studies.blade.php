@if ($featuredProjects)
    <section aria-labelledby="case-studies-heading">
        <h3 id="case-studies-heading">Featured Technical Case Studies</h3>
        <div class="portfolio-content-grid">
            @foreach ($featuredProjects as $study)
                <article class="portfolio-content-card">
                    <h4>{{ $study['title'] }}</h4>
                    <p>{{ $study['description'] }}</p>
                    <ul class="portfolio-badges">
                        @foreach ($study['technologies'] as $technology)
                            <li>{{ $technology }}</li>
                        @endforeach
                    </ul>
                    <dl>
                        @foreach ($study['details'] as $detail)
                            <dt>{{ $detail['label'] }}</dt>
                            <dd>{{ $detail['text'] }}</dd>
                        @endforeach
                    </dl>
                    @foreach ($study['screenshots'] as $image)
                        <img loading="lazy" decoding="async" src="{{ $image['url'] }}" alt="{{ $image['alt'] }}">
                    @endforeach
                    @foreach ($study['links'] as $link)
                        <p><a href="{{ $link['url'] }}" rel="noopener noreferrer" target="_blank">{{ $link['label'] }}</a></p>
                    @endforeach
                </article>
            @endforeach
        </div>
    </section>
@endif
