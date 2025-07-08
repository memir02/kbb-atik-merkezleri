<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Konya Büyükşehir Belediyesi Atık Merkezleri</title>
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
                               placeholder="Atık merkezi ismine göre ya da adresine göre ara..."
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
                    <i class="fas fa-crosshairs me-1"></i>
                    Enlem: {{ number_format($userLat, 6) }}° | Boylam: {{ number_format($userLon, 6) }}°
                    <br>
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
            
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5">
                @foreach($merkezler as $merkez)
                    <div class="col">
                        <div class="card border-success h-100 selectable-card position-relative" data-merkez-id="{{ $merkez->id }}" style="cursor: pointer; transition: all 0.3s ease;">
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
                        </div>
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
            
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5">
                @foreach($merkezler as $merkez)
                    <div class="col">
                        <div class="card border-primary h-100 selectable-card position-relative" data-merkez-id="{{ $merkez->id }}" style="cursor: pointer; transition: all 0.3s ease;">
                            <div class="card-body">
                                <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                    <input class="form-check-input merkez-checkbox" type="checkbox" id="merkez-{{ $merkez->id }}" data-merkez-id="{{ $merkez->id }}">
                                </div>
                                <h5 class="card-title pe-5">{{ $merkez->title }}</h5>
                                <p class="card-text">{{ $merkez->content }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $merkez->adres }}
                                </small>
                            </div>
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
            <small class="text-muted">{{ count(request('filter', [])) }} filtre aktif</small>
        </div>
        
        <div class="d-flex align-items-center gap-3 mb-4">
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
        
        <div class="row row-cols-1 row-cols-md-2 g-4 mb-5">
            @foreach($merkezler as $merkez)
                <div class="col">
                    <div class="card border-primary h-100 selectable-card position-relative" data-merkez-id="{{ $merkez->id }}" style="cursor: pointer; transition: all 0.3s ease;">
                        <div class="card-body">
                            <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                <input class="form-check-input merkez-checkbox" type="checkbox" id="merkez-{{ $merkez->id }}" data-merkez-id="{{ $merkez->id }}">
                            </div>
                            <h5 class="card-title pe-5">{{ $merkez->title }}</h5>
                            <p class="card-text">{{ $merkez->content }}</p>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $merkez->adres }}
                            </small>
                        </div>
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
                    <div class="card border-primary h-100 selectable-card position-relative" data-merkez-id="{{ $merkez->id }}" style="cursor: pointer; transition: all 0.3s ease;">
                        <div class="card-body">
                            <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                <input class="form-check-input all-merkez-checkbox" type="checkbox" id="all-merkez-{{ $merkez->id }}" data-merkez-id="{{ $merkez->id }}">
                            </div>
                            <h5 class="card-title pe-5">{{ $merkez->title }}</h5>
                            <p class="card-text">{{ $merkez->content }}</p>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $merkez->adres }}
                            </small>
                        </div>
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
