@php $cats = \App\Models\Category::active()->limit(10)->get(); @endphp

@if ($cats->isNotEmpty())
    <section class="block">
        @include('partials.block-head', ['title' => 'বিভাগসমূহ'])
        <div class="chip-row">
            @foreach ($cats as $cat)
                <a class="chip" href="{{ route('category.show', $cat) }}">{{ $cat->name }}</a>
            @endforeach
        </div>
    </section>
@endif
