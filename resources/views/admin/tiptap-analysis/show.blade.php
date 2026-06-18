<x-admin-layout>
    <x-slot name="header">{{ $sectionTitle }}</x-slot>

    @include('admin.tiptap-analysis.partials.styles')

    <div class="mb-6">
        <a href="{{ route('admin.tiptap-analysis.index') }}"
           class="inline-flex items-center gap-2 text-sm font-bold text-fin-primary hover:text-fin-lavender transition mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            Back to TipTap Analytics
        </a>
        <h2 class="text-xl md:text-2xl font-black text-white">{{ $sectionTitle }}</h2>
        <p class="text-sm text-white/45 mt-1">{{ $sectionSubtitle }}</p>
    </div>

    @include('admin.tiptap-analysis.partials.filters')

    <div class="tiptap-analysis-content">
        @include('admin.tiptap-analysis.sections.'.$activeSection)
    </div>

    @include('admin.tiptap-analysis.partials.analysis-engine', ['activeSection' => $activeSection])
</x-admin-layout>
