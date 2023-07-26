<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'WageDollar')
<img src="https://wagedollars.com/img/logo.png" class="logo" alt="WageDollar Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
