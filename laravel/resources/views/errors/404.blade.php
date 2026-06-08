{{-- resources/views/errors/404.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen">
        <h1 class="text-6xl font-bold">404</h1>
        <p class="text-xl mt-4">Сторінку не знайдено</p>
        <a href="{{ url('/') }}" class="mt-6 btn btn-primary">
            На головну
        </a>
    </div>
@endsection