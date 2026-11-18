@if ($services)
<section id="{{ $anchors['services'] }}" class="portfolio-services target-section" aria-labelledby="services-heading">
    <div class="row">
        <div class="column lg-12">
            <h2 id="services-heading" class="text-pretitle">Freelance Services</h2>
            <div class="portfolio-content-grid">
                @foreach ($services as $service)
                    <article class="portfolio-content-card">
                        <h3>{{ $service['title'] }}</h3>
                        <p>{{ $service['description'] }}</p>
                        <ul class="portfolio-badges">
                            @foreach ($service['technologies'] as $technology)
                                <li>{{ $technology }}</li>
                            @endforeach
                        </ul>
                        <a href="#{{ $anchors['contact'] }}" aria-label="Discuss {{ $service['title'] }}">Discuss this service</a>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
