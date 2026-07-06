@props([
    'label' => 'Or continue with email',
])

<div {{ $attributes->merge(['class' => 'relative my-5']) }}>
    <div class="absolute inset-0 flex items-center" aria-hidden="true">
        <div class="w-full border-t border-[#E8E8ED]"></div>
    </div>
    <div class="relative flex justify-center">
        <span class="bg-white px-4 text-sm font-medium text-[#64708B]">{{ $label }}</span>
    </div>
</div>
