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
<header class="py-2" style="background-color:#34373b!important;">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
            <img src="{{ asset('images/bs-logo.png') }}" alt="Benim Şehrim Logosu" style="height:60px; object-fit:contain; cursor:pointer;" onclick="window.open('https://www.konya.bel.tr', '_blank')">
            <img src="{{ asset('images/kbb-logo.png') }}" alt="Konya BB Logosu" style="height:60px; object-fit:contain; cursor:pointer;" onclick="window.open('https://www.konya.bel.tr', '_blank')">
        </div>
        <h1>
  <a href="{{ url('/') }}" style="color: inherit; text-decoration: none;">
    Konya Büyükşehir Belediyesi Atık Merkezleri
  </a>
</h1>

    </div>
</header>

<!-- User Authentication Section -->
<div class="auth-section py-2" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
    <div class="container">
        <div class="d-flex justify-content-end">
            @guest
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-sign-in-alt me-1"></i> Giriş Yap
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus me-1"></i> Kayıt Ol
                    </a>
                </div>
            @else
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Hoş geldiniz, {{ Auth::user()->name }}</span>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-tachometer-alt me-1"></i> Panelim
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i> Çıkış
                        </button>
                    </form>
                </div>
            @endguest
        </div>
    </div>
</div>



<!-- Arama ve Navigasyon Bölümü -->
<div class="navigation-section bg-light py-4">
    <div class="container">
        <!-- Arama Barı -->
        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-8">
                <form method="GET" action="{{ route('atik-merkezleri.index') }}" class="search-form">
                    <div class="input-group input-group-lg">
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Atık Merkezi Bul"
                               value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search me-1"></i> Ara
                        </button>
                        @if(request('search'))
                            <a href="{{ route('atik-merkezleri.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Aksiyon Butonları -->
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <button type="button" 
                            class="btn btn-primary btn-lg" 
                            data-bs-toggle="modal" 
                            data-bs-target="#filtreModal">
                        <i class="fas fa-filter me-1"></i> FİLTRELE
                    </button>
                    <button type="button" id="konuma-gore-ara" class="btn btn-success btn-lg">
                        <i class="fas fa-location-arrow me-1"></i> KONUMUMA GÖRE BUL
                    </button>
                    <button type="button" 
                            class="btn btn-danger btn-lg"
                            data-bs-toggle="modal" 
                            data-bs-target="#youtubeModal">
                            <i class="fab fa-youtube"></i>
                    </button>
                    @if(request('filter'))
                        <a href="{{ route('atik-merkezleri.index') }}" class="btn btn-outline-danger btn-lg">
                            <i class="fas fa-times me-1"></i> Filtreyi Temizle
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Konuma Göre Sonuçlar -->
@if(isset($isLocationSearch) && $isLocationSearch && isset($merkezler))
    <div class="container mt-4 mb-5" data-user-location data-user-lat="{{ $userLat }}" data-user-lon="{{ $userLon }}">
        @if($merkezler->count() > 0)
            <div class="alert alert-success">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-map-marker-alt text-success me-2"></i>
                    <strong>Konumunuz tespit edildi!</strong>
                </div>
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    Size en yakın {{ $merkezler->count() }} atık merkezi mesafe sırasına göre listelendi.
                    <br>
                    <i class="fa fa-exclamation-circle me-1" aria-hidden="true"></i>
                    Uyarı: Konumunuz kullandığınız cihaza, tarayıcınıza, konum ayarlarınıza, veya ağ bağlantınıza bağlı olarak farklılık gösterebilir! 
                    <br>
                </small>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>
                    <i class="fas fa-location-arrow text-success me-2"></i>
                    Size En Yakın Atık Merkezleri
                </h4>
            </div>
            
            <div class="d-flex align-items-center gap-3 mb-4">
                <button id="selectAllFiltered" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-check-square me-1"></i> <span class="select-text">Tümünü Seç</span>
                </button>
                <button id="clearFilteredSelection" class="btn btn-outline-danger btn-sm" style="display: none;">
                    <i class="fas fa-times me-1"></i> Seçilenleri Temizle
                </button>
                <div id="selectedCount" class="badge bg-primary" style="display: none;">
                    <span id="countText">0</span> seçildi
                </div>
                <button id="showSelectedOnMap" class="btn btn-success btn-sm" style="display: none;">
                    <i class="fas fa-map-marked-alt me-1"></i> Seçilenleri Haritada Göster-Detaylarını Görüntüle
                </button>
            </div>
            
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5">
                @foreach($merkezler as $merkez)
                    <div class="col">
                        <div class="card {{ $merkez->border_class }} h-100 selectable-card position-relative" data-merkez-id="{{ $merkez->id }}" style="cursor: pointer; transition: all 0.3s ease;">
                            <div class="card-body">
                                <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                    <input class="form-check-input merkez-checkbox" type="checkbox" id="merkez-{{ $merkez->id }}" data-merkez-id="{{ $merkez->id }}">
                                </div>
                                                            <h5 class="card-title pe-5">{{ $merkez->title }}</h5>
                            <div class="mb-2">
                                                                    <span class="badge bg-success">
                                    <i class="fas fa-route me-1"></i>{{ number_format($merkez->distance, 1) }} km mesafede
                                </span>
                            </div>
                            <p class="card-text">{{ $merkez->content }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $merkez->adres }}
                                </small>
                            
                            <!-- Rating Widget -->
                            <div class="rating-widget mt-2" data-merkez-id="{{ $merkez->id }}" onclick="event.stopPropagation()">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="rating-display">
                                        <div class="stars-display">
                                            {!! $merkez->star_rating_html !!}
                                        </div>
                                    </div>
                                    <div class="action-buttons">
                                        @auth
                                            <button class="btn btn-sm btn-outline-danger favorite-btn" 
                                                    data-merkez-id="{{ $merkez->id }}"
                                                    title="Favorilere Ekle">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary rate-btn" 
                                                    data-merkez-id="{{ $merkez->id }}"
                                                    title="Puan Ver">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-danger" title="Favorilere eklemek için giriş yap">
                                                <i class="far fa-heart"></i>
                                            </a>
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary" title="Puanlamak için giriş yap">
                                                <i class="fas fa-star"></i>
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="map-button-container position-absolute" style="bottom: 10px; right: 10px; display: none;">
                            <button class="btn btn-success btn-sm haritada-goster-btn" data-merkez-id="{{ $merkez->id }}">
                                <i class="fas fa-info-circle me-1"></i> Detay Görüntüle-Haritada Göster
                            </button>
                        </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-warning mb-5">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Yakınlarda atık merkezi bulunamadı.
            </div>
        @endif
    </div>

<!-- Arama Sonuçları -->
@elseif(isset($merkezler) && request()->has('search'))
    <div class="container mt-4 mb-5">
        @if($merkezler->count() > 0)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>
                    <i class="fas fa-search text-primary me-2"></i>
                    Arama Sonuçları
                    <span class="badge bg-primary ms-2">{{ $merkezler->count() }}</span>
                </h4>
                <small class="text-muted">Aranan: "<strong>{{ request('search') }}</strong>"</small>
            </div>
            
            <div class="d-flex align-items-center gap-3 mb-4">
                <button id="selectAllFiltered" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-check-square me-1"></i> <span class="select-text">Tümünü Seç</span>
                </button>
                <button id="clearFilteredSelection" class="btn btn-outline-danger btn-sm" style="display: none;">
                    <i class="fas fa-times me-1"></i> Seçilenleri Temizle
                </button>
                <div id="selectedCount" class="badge bg-primary" style="display: none;">
                    <span id="countText">0</span> seçildi
                </div>
                <button id="showSelectedOnMap" class="btn btn-success btn-sm" style="display: none;">
                    <i class="fas fa-map-marked-alt me-1"></i> Seçilenleri Haritada Göster-Detaylarını Görüntüle
                </button>
            </div>
            
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5">
                @foreach($merkezler as $merkez)
                    <div class="col">
                        <div class="card {{ $merkez->border_class }} h-100 selectable-card position-relative" data-merkez-id="{{ $merkez->id }}" style="cursor: pointer; transition: all 0.3s ease;">
                            <div class="card-body">
                                <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                    <input class="form-check-input merkez-checkbox" type="checkbox" id="merkez-{{ $merkez->id }}" data-merkez-id="{{ $merkez->id }}">
                                </div>
                                <h5 class="card-title pe-5">{{ $merkez->title }}</h5>
                                <p class="card-text">{{ $merkez->content }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $merkez->adres }}
                                </small>
                                
                                <!-- Rating Widget -->
                                <div class="rating-widget mt-2" data-merkez-id="{{ $merkez->id }}" onclick="event.stopPropagation()">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="rating-display">
                                            <div class="stars-display">
                                                {!! $merkez->star_rating_html !!}
                                            </div>
                                        </div>
                                        <div class="action-buttons">
                                            @auth
                                                <button class="btn btn-sm btn-outline-danger favorite-btn" 
                                                        data-merkez-id="{{ $merkez->id }}"
                                                        title="Favorilere Ekle">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary rate-btn" 
                                                        data-merkez-id="{{ $merkez->id }}"
                                                        title="Puan Ver">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-danger" title="Favorilere eklemek için giriş yap">
                                                    <i class="far fa-heart"></i>
                                                </a>
                                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary" title="Puanlamak için giriş yap">
                                                    <i class="fas fa-star"></i>
                                                </a>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="map-button-container position-absolute" style="bottom: 10px; right: 10px; display: none;">
                                <button class="btn btn-success btn-sm haritada-goster-btn" data-merkez-id="{{ $merkez->id }}">
                                    <i class="fas fa-info-circle me-1"></i> Detay Görüntüle-Haritada Göster
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-warning mb-5">
                <i class="fas fa-exclamation-triangle me-2"></i>
                "<strong>{{ request('search') }}</strong>" için sonuç bulunamadı.
                <hr>
                <div class="mt-2">
                    <strong>İpucu:</strong> Mahalle, bölge veya önemli nokta adlarını kullanın
                    <br>
                    <small class="text-muted">Örnek: Yazır, Selçuklu, Bosna Hersek, Cumhuriyet</small>
                </div>
            </div>
        @endif
    </div>

<!-- Filtre Sonuçları -->
@elseif(isset($merkezler) && $merkezler->count())
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>
                <i class="fas fa-filter text-success me-2"></i>
                Filtrelenmiş Sonuçlar
                <span class="badge bg-success ms-2">{{ $merkezler->count() }}</span>
            </h4>
            <small class="text-muted">{{ count(request('filter', [])) }} filtre aktif: {{ implode(', ', request('filter', [])) }}</small>
        </div>
        
        @if(isset($debugInfo))
            <div class="alert alert-info alert-sm">
                <small>
                    <strong>Debug:</strong> Branch: {{ $debugInfo['branch'] }} | Filter: {{ json_encode($debugInfo['filter_value']) }}
                </small>
            </div>
        @endif
        
        <div class="d-flex align-items-center gap-3 mb-4">
            <button id="selectAllFiltered" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-check-square me-1"></i> <span class="select-text">Tümünü Seç</span>
            </button>
            <button id="clearFilteredSelection" class="btn btn-outline-danger btn-sm" style="display: none;">
                <i class="fas fa-times me-1"></i> Seçilenleri Temizle
            </button>
            <div id="selectedCount" class="badge bg-primary" style="display: none;">
                <span id="countText">0</span> seçildi
            </div>
            <button id="showSelectedOnMap" class="btn btn-success btn-sm" style="display: none;">
                <i class="fas fa-map-marked-alt me-1"></i> Seçilenleri Haritada Göster-Detaylarını Görüntüle
            </button>
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 g-4 mb-5">
            @foreach($merkezler as $merkez)
                <div class="col">
                    <div class="card {{ $merkez->border_class }} h-100 selectable-card position-relative" data-merkez-id="{{ $merkez->id }}" style="cursor: pointer; transition: all 0.3s ease;">
                        <div class="card-body">
                            <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                <input class="form-check-input merkez-checkbox" type="checkbox" id="merkez-{{ $merkez->id }}" data-merkez-id="{{ $merkez->id }}">
                            </div>
                            <h5 class="card-title pe-5">{{ $merkez->title }}</h5>
                            <p class="card-text">{{ $merkez->content }}</p>
                            @if($merkez->adres)
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $merkez->adres }}
                                </small>
                            @endif
                            
                            <!-- Rating Widget - Koordinat yerine -->
                            <div class="rating-widget mt-2" data-merkez-id="{{ $merkez->id }}" onclick="event.stopPropagation()">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="rating-display">
                                        <div class="stars-display">
                                            {!! $merkez->star_rating_html !!}
                                        </div>
                                    </div>
                                    <div class="action-buttons">
                                        @auth
                                            <button class="btn btn-sm btn-outline-danger favorite-btn" 
                                                    data-merkez-id="{{ $merkez->id }}"
                                                    title="Favorilere Ekle">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary rate-btn" 
                                                    data-merkez-id="{{ $merkez->id }}"
                                                    title="Puan Ver">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-danger" title="Favorilere eklemek için giriş yap">
                                                <i class="far fa-heart"></i>
                                            </a>
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary" title="Puanlamak için giriş yap">
                                                <i class="fas fa-star"></i>
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="map-button-container position-absolute" style="bottom: 10px; right: 10px; display: none;">
                            <button class="btn btn-success btn-sm haritada-goster-btn" data-merkez-id="{{ $merkez->id }}">
                                <i class="fas fa-info-circle me-1"></i> Detay Görüntüle-Haritada Göster
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@elseif(request()->has('filter'))
    <div class="container mt-4 mb-5">
        <div class="alert alert-warning mb-5">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Filtreye uyan bir atık merkezi bulunamadı.
            
            @if(isset($debugInfo))
                <hr>
                <small>
                    <strong>Debug Bilgisi:</strong><br>
                    Filter: {{ json_encode($debugInfo['filter_value']) }}<br>
                    Branch: {{ $debugInfo['branch'] }}<br>
                    Merkez Count: {{ $debugInfo['merkezler_count'] }}
                </small>
            @endif
        </div>
    </div>
@endif

@if(isset($tumMerkezler) && $tumMerkezler->count())
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>
                <i class="fas fa-building text-primary me-2"></i>
                Tüm Atık Merkezleri
            </h4>
            <div class="d-flex align-items-center gap-3">
                <button id="selectAllMerkezler" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-check-square me-1"></i> <span class="select-all-text">Tümünü Seç</span>
                </button>
                <button id="clearAllSelection" class="btn btn-outline-danger btn-sm" style="display: none;">
                    <i class="fas fa-times me-1"></i> Seçilenleri Temizle
                </button>
                <div id="allSelectedCount" class="badge bg-primary" style="display: none;">
                    <span id="allCountText">0</span> seçildi
                </div>
                <button id="showAllSelectedOnMap" class="btn btn-success btn-sm" style="display: none;">
                    <i class="fas fa-map-marked-alt me-1"></i> Seçilenleri Haritada Göster-Detaylarını Görüntüle
                </button>
            </div>
        </div>
        <div id="allMerkezlerContainer" class="row row-cols-1 row-cols-md-2 g-4">
            @foreach($tumMerkezler as $merkez)
                <div class="col">
                                            <div class="card {{ $merkez->border_class }} h-100 selectable-card position-relative" data-merkez-id="{{ $merkez->id }}" style="cursor: pointer; transition: all 0.3s ease;">
                            <div class="card-body">
                                <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                    <input class="form-check-input all-merkez-checkbox" type="checkbox" id="all-merkez-{{ $merkez->id }}" data-merkez-id="{{ $merkez->id }}">
                                </div>
                                <h5 class="card-title pe-5">{{ $merkez->title }}</h5>
                                <p class="card-text">{{ $merkez->content }}</p>
                                @if($merkez->adres)
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $merkez->adres }}
                                    </small>
                                @endif
                                
                                <!-- Rating Widget - Koordinat yerine -->
                                <div class="rating-widget mt-2" data-merkez-id="{{ $merkez->id }}" onclick="event.stopPropagation()">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="rating-display">
                                            <div class="stars-display">
                                                {!! $merkez->star_rating_html !!}
                                            </div>
                                        </div>
                                        <div class="action-buttons">
                                            @auth
                                                <button class="btn btn-sm btn-outline-danger favorite-btn" 
                                                        data-merkez-id="{{ $merkez->id }}"
                                                        title="Favorilere Ekle">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary rate-btn" 
                                                        data-merkez-id="{{ $merkez->id }}"
                                                        title="Puan Ver">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-danger" title="Favorilere eklemek için giriş yap">
                                                    <i class="far fa-heart"></i>
                                                </a>
                                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary" title="Puanlamak için giriş yap">
                                                    <i class="fas fa-star"></i>
                                                </a>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="map-button-container position-absolute" style="bottom: 10px; right: 10px; display: none;">
                                <button class="btn btn-success btn-sm haritada-goster-btn" data-merkez-id="{{ $merkez->id }}">
                                    <i class="fas fa-info-circle me-1"></i> Detay Görüntüle-Haritada Göster
                                </button>
                            </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Daha Fazla Yükle Buttonu -->
        <div id="loadMoreContainer" class="text-center mt-4">
            <button id="loadMoreBtn" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-plus-circle me-2"></i>Daha Fazla Merkez Yükle
            </button>
        </div>
        
        <!-- Loading indicator -->
        <div id="loadingIndicator" class="text-center mt-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
            <p class="mt-2">Daha fazla merkez yükleniyor...</p>
        </div>
        
        <!-- End of data message -->
        <div id="endOfData" class="text-center mt-4 mb-4" style="display: none;">
            <p class="text-muted">Tüm atık merkezleri yüklendi.</p>
        </div>
    </div>
@endif

<!-- Filtreleme Modalı -->
<div class="modal fade" id="filtreModal" tabindex="-1" aria-labelledby="filtreModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="filtreModalLabel">Atık Merkezi Türüne Göre Filtrele</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <form method="GET" action="{{ route('atik-merkezleri.index') }}">
        <div class="modal-body">
          <div class="mb-4">
            <div class="row row-cols-1 row-cols-md-2 g-2">
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="cam" id="filter-cam">
                  <label class="form-check-label" for="filter-cam">
                    CAM
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="kagit" id="filter-kağıt">
                  <label class="form-check-label" for="filter-kağıt">
                    KAĞIT
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="plastik" id="filter-plastik">
                  <label class="form-check-label" for="filter-plastik">
                    PLASTİK
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="metal" id="filter-metal">
                  <label class="form-check-label" for="filter-metal">
                    METAL
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="pil" id="filter-pil">
                  <label class="form-check-label" for="filter-pil">
                    PİL
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="bitkisel" id="filter-bitkisel">
                  <label class="form-check-label" for="filter-bitkisel">
                    BİTKİSEL ATIK YAĞ
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="atıkcam" id="filter-atıkcam">
                  <label class="form-check-label" for="filter-atıkcam">
                    ATIK CAM
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="tekstil" id="filter-tekstil">
                  <label class="form-check-label" for="filter-tekstil">
                    TEKSTİL KUMBARASI
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="gecici" id="filter-gecici">
                  <label class="form-check-label" for="filter-gecici">
                    ATIK GEÇİCİ DEPOLAMA ÜNİTESİ
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="ilac" id="filter-ilac">
                  <label class="form-check-label" for="filter-ilac">
                    ATIK İLAÇ
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="sinif1" id="filter-sinif1">
                  <label class="form-check-label" for="filter-sinif1">
                    1. SINIF ATIK GETİRME MERKEZİ
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="inert" id="filter-inert">
                  <label class="form-check-label" for="filter-inert">
                    İNERT ATIK
                  </label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="hafriyat" id="filter-hafriyat">
                  <label class="form-check-label" for="filter-hafriyat">
                    HAFRİYAT
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
          <button type="button" class="btn btn-primary" id="filterSubmitBtn">Listele</button>
        </div>
      </form>

      <script>
      // Modal form submission fix - Button click approach
      document.addEventListener('DOMContentLoaded', function() {
          const filterSubmitBtn = document.getElementById('filterSubmitBtn');
          const filterForm = document.querySelector('#filtreModal form');
          const modal = document.querySelector('#filtreModal');
          
          if (filterSubmitBtn && filterForm) {
              filterSubmitBtn.addEventListener('click', function(e) {
                  // Checked checkbox'ları topla
                  const checkboxes = filterForm.querySelectorAll('input[name="filter[]"]:checked');
                  const checkedFilters = Array.from(checkboxes).map(cb => cb.value);
                  
                  // Eğer hiç filtre seçilmemişse uyarı ver
                  if (checkedFilters.length === 0) {
                      alert('Lütfen en az bir filtre seçin!');
                      return false;
                  }
                  
                  // URL'i manuel olarak oluştur
                  const url = new URL(window.location.origin + '/');
                  checkedFilters.forEach(filter => {
                      url.searchParams.append('filter[]', filter);
                  });
                  
                  // Modal'ı kapat
                  const modalInstance = bootstrap.Modal.getInstance(modal);
                  if (modalInstance) {
                      modalInstance.hide();
                  }
                  
                  // Direkt yönlendir
                  window.location.href = url.toString();
              });
          } else {
              console.error('Filter button or form not found!');
          }
      });
      </script>
    </div>
  </div>
</div>

<!-- Detay ve Harita Modalı -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header pb-1" style="min-height: 80px;">
        <div class="w-100">
          <h5 class="modal-title fw-bold mb-3" id="mapModalLabel">
            <i class="fas fa-map-marker-alt me-2"></i>Atık Merkezi Bilgileri
          </h5>
          <!-- Tab Navigation -->
          <ul class="nav nav-tabs" id="modalTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active fw-bold" id="detay-tab" data-bs-toggle="tab" data-bs-target="#detay-content" type="button" role="tab" aria-controls="detay-content" aria-selected="true">
                <i class="fas fa-info-circle me-2"></i>DETAY
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link fw-bold" id="harita-tab" data-bs-toggle="tab" data-bs-target="#harita-content" type="button" role="tab" aria-controls="harita-content" aria-selected="false">
                <i class="fas fa-map me-2"></i>HARİTA
              </button>
            </li>
          </ul>
        </div>
        <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body p-0">
        <!-- Tab Content -->
        <div class="tab-content" id="modalTabContent">
          <!-- DETAY Tab -->
          <div class="tab-pane fade show active" id="detay-content" role="tabpanel" aria-labelledby="detay-tab">
            <div class="p-4" id="detay-container">
              <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Yükleniyor...</span>
                </div>
                <p class="mt-3 text-muted">Merkez bilgileri yükleniyor...</p>
              </div>
            </div>
          </div>
          <!-- HARİTA Tab -->
          <div class="tab-pane fade" id="harita-content" role="tabpanel" aria-labelledby="harita-tab">
            <div id="map" style="height: 500px; width: 100%;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- YouTube Video Modalı -->
<div class="modal fade" id="youtubeModal" tabindex="-1" aria-labelledby="youtubeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="youtubeModalLabel">
                    <i class="fab fa-youtube text-danger me-2"></i>
                    Atık Merkezleri Yönetim Tesisleri 3D Videosu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div class="ratio ratio-16x9">
                    <iframe src="https://www.youtube.com/embed/VBFfARZh9QI?start=2" 
                            title="Atık Merkezleri Yönetim Tesisleri 3D Videosu" 
                            allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>


<footer class="py-3 mt-auto" style="background-color: #34373b; color: white;">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
            <img src="{{ asset('images/bs-logo.png') }}" alt="Benim Şehrim Logosu" style="height:60px; object-fit:contain; cursor:pointer;" onclick="window.open('https://www.konya.bel.tr', '_blank')">
            <img src="{{ asset('images/kbb-logo.png') }}" alt="Konya BB Logosu" style="height:60px; object-fit:contain; cursor:pointer;" onclick="window.open('https://www.konya.bel.tr', '_blank')">
        </div>
        <p class="mb-1 text-center">&copy; 2025 Konya Büyükşehir Belediyesi</p>
        <div class="text-center">
            <small class="text-light" style="font-size: 0.6rem;">Copyright 2025, www.konya.bel.tr - Tüm Hakları Saklıdır - Bilgi İşlem Dairesi Başkanlığı</small>
        </div>
    </div>
</footer> 

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- User Authentication Status for JavaScript -->
    <div id="auth-data" 
         data-logged-in="{{ auth()->check() ? 'true' : 'false' }}"
         data-csrf-token="{{ csrf_token() }}"
         style="display: none;">
    </div>
    
    <script>
        // Get auth data from HTML attributes
        const authData = document.getElementById('auth-data');
        window.userLoggedIn = authData.dataset.loggedIn === 'true';
        window.authToken = authData.dataset.csrfToken;
        
        // DIREKT RATING SYSTEM - ES6 modül olmadan
        document.addEventListener('DOMContentLoaded', function() {
            // Rating button click events
            document.addEventListener('click', function(e) {
                const rateBtn = e.target.closest('.rate-btn');
                if (rateBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const merkezId = rateBtn.dataset.merkezId;
                    
                    if (!window.userLoggedIn) {
                        alert('Puanlama yapmak için giriş yapmalısınız!');
                        return;
                    }
                    
                    showRatingModal(merkezId);
                }
            });
            
            // ALTERNATIF: Direkt button'lara listener ekle
            setTimeout(() => {
                const rateButtons = document.querySelectorAll('.rate-btn');
                
                rateButtons.forEach((btn, index) => {
                    
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        if (!window.userLoggedIn) {
                            alert('Puanlama yapmak için giriş yapmalısınız!');
                            return;
                        }
                        
                        showRatingModal(this.dataset.merkezId);
                    });
                });
                

            }, 1000);
        });
        
        // Rating modal fonksiyonu
        function showRatingModal(merkezId) {
            
            // Mevcut modal varsa kaldır
            const existingModal = document.getElementById('ratingModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Modal HTML oluştur
            const modalHtml = `
                <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="ratingModalLabel">
                                    <i class="fas fa-star text-warning me-2"></i>Merkezi Puanla
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-4">
                                    <h6 class="mb-3">Bu merkezi nasıl değerlendirirsiniz?</h6>
                                    <div class="rating-stars my-3" id="modalRatingStars">
                                        <i class="fas fa-star rating-star text-muted me-1" data-rating="1" style="cursor: pointer; font-size: 2rem;"></i>
                                        <i class="fas fa-star rating-star text-muted me-1" data-rating="2" style="cursor: pointer; font-size: 2rem;"></i>
                                        <i class="fas fa-star rating-star text-muted me-1" data-rating="3" style="cursor: pointer; font-size: 2rem;"></i>
                                        <i class="fas fa-star rating-star text-muted me-1" data-rating="4" style="cursor: pointer; font-size: 2rem;"></i>
                                        <i class="fas fa-star rating-star text-muted me-1" data-rating="5" style="cursor: pointer; font-size: 2rem;"></i>
                                    </div>
                                    <small class="text-muted">Yıldızlara tıklayarak puanlayın</small>
                                </div>
                                <div class="mb-3">
                                    <label for="ratingComment" class="form-label">
                                        <i class="fas fa-comment me-1"></i>Yorumunuz (isteğe bağlı)
                                    </label>
                                    <textarea class="form-control" id="ratingComment" rows="3" 
                                        placeholder="Merkezle ilgili deneyiminizi paylaşın..."></textarea>
                                </div>
                                <input type="hidden" id="selectedRating" value="0">
                                <input type="hidden" id="ratingMerkezId" value="${merkezId}">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>İptal
                                </button>
                                <button type="button" class="btn btn-primary" id="submitRating">
                                    <i class="fas fa-paper-plane me-1"></i>Puanla
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // DOM'a ekle
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Star click events
            document.querySelectorAll('#modalRatingStars .rating-star').forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.dataset.rating;
                    document.getElementById('selectedRating').value = rating;
                    
                    // Yıldızları güncelle
                    document.querySelectorAll('#modalRatingStars .rating-star').forEach((s, index) => {
                        if (index < rating) {
                            s.classList.remove('text-muted');
                            s.classList.add('text-warning');
                        } else {
                            s.classList.remove('text-warning');
                            s.classList.add('text-muted');
                        }
                    });
                });
            });
            
            // Submit button event
            document.getElementById('submitRating').addEventListener('click', function() {
                const rating = document.getElementById('selectedRating').value;
                const comment = document.getElementById('ratingComment').value;
                
                if (!rating || rating < 1) {
                    alert('Lütfen bir puan seçin!');
                    return;
                }
                
                submitRating(merkezId, rating, comment);
            });
            
                         // Modal'ı göster
             
             try {
                 const modalElement = document.getElementById('ratingModal');
                 
                                   if (typeof bootstrap === 'undefined') {
                      console.error('Bootstrap not loaded');
                      return;
                  }
                  
                  const modal = new bootstrap.Modal(modalElement);
                  modal.show();
                 
                 // Fallback: Manuel olarak göster
                 setTimeout(() => {
                                           if (!modalElement.classList.contains('show')) {
                          
                          // Manuel gösterme - güçlü CSS
                          modalElement.style.display = 'block !important';
                          modalElement.style.position = 'fixed';
                          modalElement.style.top = '50%';
                          modalElement.style.left = '50%';
                          modalElement.style.transform = 'translate(-50%, -50%)';
                          modalElement.style.zIndex = '99999';
                          modalElement.style.backgroundColor = 'rgba(0,0,0,0.5)';
                          modalElement.classList.add('show');
                          document.body.classList.add('modal-open');
                          
                          // Backdrop ekle
                          const backdrop = document.createElement('div');
                          backdrop.className = 'modal-backdrop fade show';
                          backdrop.style.zIndex = '99998';
                          document.body.appendChild(backdrop);
                          

                          
                                      }
                 }, 100);
                 
             } catch (error) {
                 console.error('❌ Modal error:', error);
                 alert('Modal hatası: ' + error.message);
             }
        }
        
        // Rating submit fonksiyonu
        function submitRating(merkezId, rating, comment) {
            
            fetch('/api/ratings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.authToken,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    atik_merkezi_id: merkezId,
                    rating: rating,
                    comment: comment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.average_rating !== undefined) {
                    alert('Puanınız başarıyla kaydedildi!');
                    
                    // Modal'ı kapat
                    const modal = bootstrap.Modal.getInstance(document.getElementById('ratingModal'));
                    modal.hide();
                    
                    // Sayfayı yenile (rating'leri güncellemek için)
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Hata: ' + (data.message || 'Bilinmeyen hata'));
                }
            })
            .catch(error => {
                console.error('Rating submit error:', error);
                alert('Bir hata oluştu!');
            });
        }
    </script>
    
    <!-- ES6 Modules Re-enabled - RatingModule disabled in code -->
    <script type="module" src="{{ asset('js/atik-merkezleri.js') }}"></script>
    

    









</body>
</html>
