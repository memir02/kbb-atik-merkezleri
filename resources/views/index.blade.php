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
    
    <!-- Yorum metinleri için acil CSS düzeltmesi -->
    <style>
        .comment-item .comment-text {
            color: #ffffff !important;
            background-color: rgba(13, 110, 253, 0.15) !important;
            padding: 0.75rem !important;
            border-radius: 0.375rem !important;
            border-left: 4px solid #0d6efd !important;
            border: 1px solid rgba(13, 110, 253, 0.4) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2) !important;
            line-height: 1.5 !important;
            margin-top: 0.5rem !important;
        }
        .modal .comment-item .comment-text,
        #yorum-content-inner .comment-item .comment-text {
            color: #ffffff !important;
            background-color: rgba(13, 110, 253, 0.15) !important;
        }
    </style>
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
    'userLon' => $userLon ?? null,
    'favoriMerkezler' => $favoriMerkezler ?? []
])

{{-- Modaller Component --}}
@include('index-components.modaller')

{{-- Footer Component --}}
@include('index-components.footer')

{{-- JavaScript Component --}}
@include('index-components.javascript')

</body>
</html>
