<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ別勤怠一覧 - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
</head>

<body>
    @include('layouts.header', ['headerType' => $headerType ?? 'admin'])

    <main>
        <h2>スタッフ別勤怠一覧</h2>
    </main>
</body>

</html>
