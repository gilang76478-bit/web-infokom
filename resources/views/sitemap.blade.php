{{-- Berkas sitemap.xml disusun tanpa tata letak dan tanpa spasi di depan
     deklarasi XML agar berkasnya sah. URL lolos-escaped oleh Blade sehingga
     tanda & pada string URL tetap valid sebagai XML. --}}
{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($entries as $entry)
    <url>
        <loc>{{ $entry['loc'] }}</loc>
        @if (! empty($entry['lastmod']))<lastmod>{{ $entry['lastmod'] }}</lastmod>
        @endif<changefreq>{{ $entry['changefreq'] }}</changefreq>
        <priority>{{ $entry['priority'] }}</priority>
    </url>
@endforeach
</urlset>
