<x-layout>
    <x-slot name="title">
        my BBS
    </x-slot>

    <h1>
        <span>my BBS</span>
        <a href="{{ route('posts.create') }}">[Add]</a>
    </h1>
    <ul>
        @forelse ($posts as $post)
            <a href="{{ route('posts.show', $post) }}">
                <li>{{ $post->title }}</li>
            </a>
        @empty
            <li>No post</li>
        @endforelse
    </ul>
</x-layout>
