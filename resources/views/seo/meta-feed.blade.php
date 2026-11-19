{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
<channel>
    <title>Lavenin</title>
    <link>{{ url('/') }}</link>
    <description>Lavenin baby toys</description>
@foreach ($products as $p)
    <item>
        <g:id>{{ $p['id'] }}</g:id>
        <g:title>{{ $p['title'] }}</g:title>
        <g:description>{{ $p['description'] }}</g:description>
        <g:availability>{{ $p['availability'] }}</g:availability>
        <g:condition>new</g:condition>
        <g:price>{{ $p['price'] }}</g:price>
@if ($p['sale_price'])
        <g:sale_price>{{ $p['sale_price'] }}</g:sale_price>
@endif
        <g:link>{{ $p['link'] }}</g:link>
        <g:image_link>{{ $p['image_link'] }}</g:image_link>
@if ($p['additional_image_link'])
        <g:additional_image_link>{{ $p['additional_image_link'] }}</g:additional_image_link>
@endif
        <g:brand>Lavenin</g:brand>
@if ($p['product_type'])
        <g:product_type>{{ $p['product_type'] }}</g:product_type>
@endif
        <g:quantity_to_sell_on_facebook>{{ $p['quantity_to_sell_on_facebook'] }}</g:quantity_to_sell_on_facebook>
    </item>
@endforeach
</channel>
</rss>
