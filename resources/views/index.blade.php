<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Konya Büyükşehir Belediyesi Atık Merkezleri</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
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
                               placeholder="Lokasyon ara... (örn: Yazır, Selçuklu, Cumhuriyet)"
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
                    <button type="button" class="btn btn-success btn-lg">
                        <i class="fas fa-location-arrow me-1"></i> KONUMUMA GÖRE BUL
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

<!-- Arama Sonuçları -->
@if(isset($merkezler) && request()->has('search'))
    <div class="container mt-4">
        @if($merkezler->count() > 0)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>
                    <i class="fas fa-search text-primary me-2"></i>
                    Arama Sonuçları
                    <span class="badge bg-primary ms-2">{{ $merkezler->count() }}</span>
                </h4>
                <small class="text-muted">Aranan: "<strong>{{ request('search') }}</strong>"</small>
            </div>
        @else
            <div class="alert alert-warning">
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
        @if($merkezler->count() > 0)
            <div class="d-flex align-items-center gap-3">
                <button id="clearFilteredSelection" class="btn btn-outline-danger btn-sm" style="display: none;">
                    <i class="fas fa-times me-1"></i> Seçilenleri Temizle
                </button>
                <div id="selectedCount" class="badge bg-primary" style="display: none;">
                    <span id="countText">0</span> seçildi
                </div>
                <button id="showSelectedOnMap" class="btn btn-success btn-sm" style="display: none;">
                    <i class="fas fa-map-marked-alt me-1"></i> Seçilenleri Haritada Göster
                </button>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            @foreach($merkezler as $merkez)
                <div class="col">
                    <div class="card border-primary h-100 selectable-card position-relative" data-merkez-id="{{ $merkez->id }}" style="cursor: pointer; transition: all 0.3s ease;">
                        <div class="card-body">
                            <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                <input class="form-check-input merkez-checkbox" type="checkbox" id="merkez-{{ $merkez->id }}" data-merkez-id="{{ $merkez->id }}">
                            </div>
                            <h5 class="card-title pe-5">{{ $merkez->title }}</h5>
                            <p class="card-text">{{ $merkez->content }}</p>
                            <small class="text-muted">Adres: {{ $merkez->adres }}</small>
                        </div>
                        <div class="map-button-container position-absolute" style="bottom: 10px; right: 10px; display: none;">
                            <button class="btn btn-success btn-sm haritada-goster-btn" data-merkez-id="{{ $merkez->id }}">
                                <i class="fas fa-map-marker-alt me-1"></i> Haritada Göster
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>

<!-- Filtre Sonuçları -->
@elseif(isset($merkezler) && $merkezler->count())
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>
                <i class="fas fa-filter text-success me-2"></i>
                Filtrelenmiş Sonuçlar
                <span class="badge bg-success ms-2">{{ $merkezler->count() }}</span>
            </h4>
            <small class="text-muted">{{ count(request('filter', [])) }} filtre aktif</small>
        </div>
            <div class="d-flex align-items-center gap-3">
                <button id="clearFilteredSelection" class="btn btn-outline-danger btn-sm" style="display: none;">
                    <i class="fas fa-times me-1"></i> Seçilenleri Temizle
                </button>
                <div id="selectedCount" class="badge bg-primary" style="display: none;">
                    <span id="countText">0</span> seçildi
                </div>
                <button id="showSelectedOnMap" class="btn btn-success btn-sm" style="display: none;">
                    <i class="fas fa-map-marked-alt me-1"></i> Seçilenleri Haritada Göster
                </button>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            @foreach($merkezler as $merkez)
                <div class="col">
                    <div class="card border-primary h-100 selectable-card position-relative" data-merkez-id="{{ $merkez->id }}" style="cursor: pointer; transition: all 0.3s ease;">
                        <div class="card-body">
                            <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                <input class="form-check-input merkez-checkbox" type="checkbox" id="merkez-{{ $merkez->id }}" data-merkez-id="{{ $merkez->id }}">
                            </div>
                            <h5 class="card-title pe-5">{{ $merkez->title }}</h5>
                            <p class="card-text">{{ $merkez->content }}</p>
                            <small class="text-muted">Adres: {{ $merkez->adres }}</small>
                        </div>
                        <div class="map-button-container position-absolute" style="bottom: 10px; right: 10px; display: none;">
                            <button class="btn btn-success btn-sm haritada-goster-btn" data-merkez-id="{{ $merkez->id }}">
                                <i class="fas fa-map-marker-alt me-1"></i> Haritada Göster
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@elseif(request()->has('filter'))
    <div class="container mt-4">
        <p class="text-danger">Filtrene uyan bir atık merkezi bulunamadı.</p>
    </div>
@endif

@if(isset($tumMerkezler) && $tumMerkezler->count())
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Tüm Atık Merkezleri</h4>
            <div class="d-flex align-items-center gap-3">
                <button id="clearAllSelection" class="btn btn-outline-danger btn-sm" style="display: none;">
                    <i class="fas fa-times me-1"></i> Seçilenleri Temizle
                </button>
                <div id="allSelectedCount" class="badge bg-primary" style="display: none;">
                    <span id="allCountText">0</span> seçildi
                </div>
                <button id="showAllSelectedOnMap" class="btn btn-success btn-sm" style="display: none;">
                    <i class="fas fa-map-marked-alt me-1"></i> Seçilenleri Haritada Göster
                </button>
            </div>
        </div>
        <div id="allMerkezlerContainer" class="row row-cols-1 row-cols-md-2 g-4">
            @foreach($tumMerkezler as $merkez)
                <div class="col">
                    <div class="card border-primary h-100 selectable-card position-relative" data-merkez-id="{{ $merkez->id }}" style="cursor: pointer; transition: all 0.3s ease;">
                        <div class="card-body">
                            <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                <input class="form-check-input all-merkez-checkbox" type="checkbox" id="all-merkez-{{ $merkez->id }}" data-merkez-id="{{ $merkez->id }}">
                            </div>
                            <h5 class="card-title pe-5">{{ $merkez->title }}</h5>
                            <p class="card-text">{{ $merkez->content }}</p>
                            <small class="text-muted">Adres: {{ $merkez->adres }}</small>
                        </div>
                        <div class="map-button-container position-absolute" style="bottom: 10px; right: 10px; display: none;">
                            <button class="btn btn-success btn-sm haritada-goster-btn" data-merkez-id="{{ $merkez->id }}">
                                <i class="fas fa-map-marker-alt me-1"></i> Haritada Göster
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
        <div id="endOfData" class="text-center mt-4" style="display: none;">
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
                  <input class="form-check-input" type="checkbox" name="filter[]" value="mobil" id="filter-mobil">
                  <label class="form-check-label" for="filter-mobil">
                    MOBİL ATIK GETİRME MERKEZİ (KAĞIT, PLASTİK, CAM, METAL, PİL)
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
                  <input class="form-check-input" type="checkbox" name="filter[]" value="cam" id="filter-cam">
                  <label class="form-check-label" for="filter-cam">
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
          <button type="submit" class="btn btn-primary">Listele</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Harita Modalı -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="mapModalLabel">
          <i class="fas fa-map-marker-alt me-2"></i>Atık Merkezleri Haritası
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body p-0">
        <div id="map" style="height: 500px; width: 100%; border-radius: 0 0 0.375rem 0.375rem;"></div>
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
    <!-- Atık Merkezleri Custom JS -->
    <script src="{{ asset('js/atik-merkezleri.js') }}"></script>








</body>
</html>
