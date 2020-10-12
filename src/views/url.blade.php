<url>
@if (! empty($tag->url))
    <loc>{{ url($tag->url) }}</loc>
@endif
@if (! empty($tag->lastModificationDate))
    <lastmod>{{ $tag->lastModificationDate->format(DateTime::ATOM) }}</lastmod>
@endif
@if (! empty($tag->changeFrequency))
    <changefreq>{{ $tag->changeFrequency }}</changefreq>
@endif
@if (isset($tag->image) && ! empty($tag->image))
    <image:image>
        <image:loc>{{ $tag->image['url'] }}</image:loc>
    </image:image>
@endif
@if (! empty($tag->priority))
    <priority>{{ number_format($tag->priority,1) }}</priority>
@endif
</url>
