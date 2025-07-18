{{-- Sonuçlar Component'i - Tam Fonksiyonel --}}
@props(['merkezler' => null, 'tumMerkezler' => null, 'searchTerm' => null, 'isLocationSearch' => false, 'userLat' => null, 'userLon' => null])

{{-- Konuma Göre Sonuçlar --}}
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
                                                @if(in_array($merkez->id, $favoriMerkezler ?? []))
                                                    <i class="fas fa-heart text-danger"></i>
                                                @else
                                                    <i class="far fa-heart"></i>
                                                @endif
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

{{-- Arama Sonuçları --}}
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
                                                    @if(in_array($merkez->id, $favoriMerkezler ?? []))
                                                        <i class="fas fa-heart text-danger"></i>
                                                    @else
                                                        <i class="far fa-heart"></i>
                                                    @endif
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

{{-- Filtre Sonuçları --}}
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
                                <br>
                            @endif
                
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
                                                @if(in_array($merkez->id, $favoriMerkezler ?? []))
                                                    <i class="fas fa-heart text-danger"></i>
                                                @else
                                                    <i class="far fa-heart"></i>
                                                @endif
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
        </div>
    </div>
@endif

{{-- Tüm Merkezler Bölümü --}}
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
                                <br>
                            @endif
                            
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
                                                @if(in_array($merkez->id, $favoriMerkezler ?? []))
                                                    <i class="fas fa-heart text-danger"></i>
                                                @else
                                                    <i class="far fa-heart"></i>
                                                @endif
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