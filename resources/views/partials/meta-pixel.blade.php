{{-- Meta Pixel: PageView on every page, plus page events ($pageEvents) and events queued by the previous request. --}}
@if ($pixelId)
    @php($events = array_merge(\App\Support\MetaPixel::pending(), $pageEvents))
    <script nonce="{{ Vite::cspNonce() }}">
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', @json($pixelId));
        fbq('track', 'PageView');
        @foreach ($events as [$name, $params, $eventId])
            fbq('track', @json($name), @json((object) $params), {eventID: @json($eventId)});
        @endforeach
    </script>
    <noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id={{ urlencode($pixelId) }}&ev=PageView&noscript=1"></noscript>
@endif
