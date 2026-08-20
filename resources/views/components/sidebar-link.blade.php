@props(['href', 'active' => false])

<a href="{{ $href }}"
   @class([
       'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition',
       'text-gray-600 hover:bg-gray-50' => ! $active,
   ])
   @if ($active) style="background-color: #eaf5fe; color: #2f80ed;" @endif>
    {{ $slot }}
</a>
