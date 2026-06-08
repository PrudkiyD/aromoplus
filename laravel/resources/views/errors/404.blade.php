<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>404 — Сторінку не знайдено</title>
    <meta name="description" content="Сторінку не знайдено. Поверніться на головну або скористайтесь пошуком.">

    {{-- Кажемо ботам не індексувати цю сторінку --}}
    <meta name="robots" content="noindex, nofollow">

    {{-- Canonical щоб не плодити дублі --}}
    <link rel="canonical" href="{{ url('/') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <main style="min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 2rem;">
        <div>
            <h1 style="font-size: 7rem; font-weight: 700; line-height: 1; margin: 0;">404</h1>
            <hr style="width: 3rem; margin: 1.5rem auto; border-color: currentColor; opacity: 0.2;">
            <p style="font-size: 1.25rem; margin: 0 0 0.5rem;">Сторінку не знайдено</p>
            <p style="opacity: 0.6; margin: 0 0 2rem;">Можливо, посилання застаріле або сторінку було переміщено.</p>
            <a href="{{ url('/') }}">← На головну</a>
        </div>
    </main>

</body>
</html>