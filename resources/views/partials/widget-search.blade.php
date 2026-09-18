<section class="block">
    <form class="side-search" action="{{ route('search') }}" method="get" role="search">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search …" aria-label="Search">
        <button type="submit">SEARCH</button>
    </form>
</section>
