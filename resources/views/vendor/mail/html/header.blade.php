<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@php
    $logoUrl = config('mail.logo_url');
@endphp
@if($logoUrl)
<img src="{{ $logoUrl }}" class="logo" alt="{{ config('app.name') }}" style="max-height: 72px;">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
