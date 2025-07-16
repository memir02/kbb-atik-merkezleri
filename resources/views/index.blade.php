<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Konya Büyükşehir Belediyesi Atık Merkezleri</title>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" as="style">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Atık Merkezleri Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/atik-merkezleri.css') }}">
</head>
<body>



{{-- Header Component --}}
@include('index-components.header')

{{-- Kimlik (Auth) Component --}}
@include('index-components.kimlik')

{{-- Navigasyon Component --}}
@include('index-components.navigasyon')

{{-- Sonuçlar Component - Artık Tam Fonksiyonel --}}
@include('index-components.sonuclar', [
    'merkezler' => $merkezler ?? null,
    'tumMerkezler' => $tumMerkezler ?? null,
    'isLocationSearch' => $isLocationSearch ?? false,
    'userLat' => $userLat ?? null,
    'userLon' => $userLon ?? null
])

{{-- Modaller Component --}}
@include('index-components.modaller')

{{-- Footer Component --}}
@include('index-components.footer')

{{-- JavaScript Component --}}
@include('index-components.javascript')

</body>
</html>
