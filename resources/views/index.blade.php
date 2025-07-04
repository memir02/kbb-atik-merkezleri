<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Konya Büyükşehir Belediyesi Atık Merkezleri</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            font-family: 'Roboto Slab', Arial, sans-serif;
            background-color: #F5F5F5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color:rgb(57, 66, 77);
            color: white;
            padding: 0.5rem 0;
            text-align: center;
        }

        .logo-wrapper {
            display: flex;
            justify-content: center;
            gap: 2rem;
            align-items: center;
            margin-bottom: 1rem;
        }

        .logo-wrapper img {
            height: 60px;
            object-fit: contain;
        }

        header h1 {
            font-size: 1.5rem;
            margin: 0;
        }

        header img {
            height: 50px;
            margin-right: 1rem;
        }

        .container {
            padding: 2rem;
        }

        footer {
            background-color: #34373b;
            color: white;
            padding: 1rem;
            text-align: center;
            margin-top: 2rem;
        }

        /* Checkbox animasyonu yavaşlatma */
        .form-check-input {
            transition: all 0.5s ease-in-out;
        }

        .form-check-input:checked {
            animation: checkSlowTick 0.5s ease-in-out;
        }

        @keyframes checkSlowTick {
            0% {
                transform: scale(0.8);
                opacity: 0.7;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.9;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Seçilebilir card stilleri */
        .selectable-card {
            transition: all 0.3s ease;
        }

        .selectable-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .selectable-card.selected {
            border-color: #198754 !important;
            background-color: #f8fff9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
        }

        .merkez-checkbox {
            z-index: 10;
        }

        .map-button-container {
            z-index: 5;
        }

        .haritada-goster-btn {
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
<header class="py-2" style="background-color:#34373b!important;">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center gap-0.5 mb-2">
            <img src="{{ asset('images/bs-logo.png') }}" alt="Benim Şehrim Logosu" style="height:60px; object-fit:contain; cursor:pointer;" onclick="window.open('https://www.konya.bel.tr', '_blank')">
            <img src="{{ asset('images/kbb-logo.png') }}" alt="Konya BB Logosu" style="height:60px; object-fit:contain; cursor:pointer;" onclick="window.open('https://www.konya.bel.tr', '_blank')">
        </div>
        <h1 class="h4 m-0 text-center">Konya Büyükşehir Belediyesi Atık Merkezleri</h1>
    </div>
</header>
@if(isset($merkezler) && $merkezler->count())
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Filtrelenmiş Sonuçlar</h4>
            <div class="d-flex align-items-center gap-3">
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


<div class="container my-5">
    <div class="row justify-content-center align-items-center" style="min-height: 120px;">
        <div class="col-12 col-md-6 d-flex flex-column align-items-center border-end border-2 border-dashed justify-content-center">
            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#filtreModal">
                FİLTRELE
            </button>
        </div>
        <div class="col-12 col-md-6 d-flex flex-column align-items-center justify-content-center">
            <button type="button" class="btn btn-success btn-lg">
                KONUMUMA GÖRE BUL
            </button>
        </div>
    </div>
</div>

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
    <div class="container text-center">
        <p class="mb-1">&copy; 2025 Konya Büyükşehir Belediyesi</p>
        <small class="text-light" style="font-size: 0.6rem;">Copyright 2025, www.konya.bel.tr - Tüm Hakları Saklıdır - Bilgi İşlem Dairesi Başkanlığı</small>
    </div>
</footer> 

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    // Harita değişkenleri
    let map = null;
    let markers = [];

    // Harita başlatma fonksiyonu
    function initializeMap() {
        if (map) return; // Zaten başlatılmışsa tekrar başlatma
        
        // Konya merkezli başlat
        map = L.map('map').setView([37.8746, 32.4932], 11);
        
        // OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    }

    // Markerları temizle
    function clearMarkers() {
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
    }

    // Basit filtreleme (sadece frontend)
    document.getElementById('contentFilter')?.addEventListener('change', function() {
        var value = this.value;
        var merkezler = document.querySelectorAll('.merkez');
        merkezler.forEach(function(merkez) {
            if (!value || merkez.getAttribute('data-content').includes(value)) {
                merkez.style.display = '';
            } else {
                merkez.style.display = 'none';
            }
        });
    });

    // Seçilebilir card fonksiyonları
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.selectable-card');
        const checkboxes = document.querySelectorAll('.merkez-checkbox');
        const selectedCount = document.getElementById('selectedCount');
        const countText = document.getElementById('countText');
        const showSelectedOnMapBtn = document.getElementById('showSelectedOnMap');

        // Card tıklama olayları
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Checkbox'a veya butona tıklanmışsa cardı seçme
                if (e.target.classList.contains('merkez-checkbox') || 
                    e.target.classList.contains('haritada-goster-btn') ||
                    e.target.closest('.haritada-goster-btn')) {
                    return;
                }

                const checkbox = this.querySelector('.merkez-checkbox');
                checkbox.checked = !checkbox.checked;
                updateCardSelection(this, checkbox.checked);
                updateSelectedCount();
            });
        });

        // Checkbox değişim olayları
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                e.stopPropagation();
                const card = this.closest('.selectable-card');
                updateCardSelection(card, this.checked);
                updateSelectedCount();
            });
        });

        // Haritada göster buton olayları
        document.querySelectorAll('.haritada-goster-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const merkezId = this.getAttribute('data-merkez-id');
                showOnMap(merkezId);
            });
        });

        // Seçilenleri haritada göster butonu
        showSelectedOnMapBtn?.addEventListener('click', function() {
            const selectedMerkezIds = getSelectedMerkezIds();
            if (selectedMerkezIds.length > 0) {
                showMultipleOnMap(selectedMerkezIds);
            }
        });

        function updateCardSelection(card, isSelected) {
            const mapButtonContainer = card.querySelector('.map-button-container');
            
            if (isSelected) {
                card.classList.add('selected');
                mapButtonContainer.style.display = 'block';
            } else {
                card.classList.remove('selected');
                mapButtonContainer.style.display = 'none';
            }
        }

        function updateSelectedCount() {
            const selectedCheckboxes = document.querySelectorAll('.merkez-checkbox:checked');
            const count = selectedCheckboxes.length;
            
            if (count > 0) {
                countText.textContent = count;
                selectedCount.style.display = 'block';
                
                // Birden fazla merkez seçilmişse "Seçilenleri Haritada Göster" butonunu göster
                if (count > 1) {
                    showSelectedOnMapBtn.style.display = 'block';
                } else {
                    showSelectedOnMapBtn.style.display = 'none';
                }
            } else {
                selectedCount.style.display = 'none';
                showSelectedOnMapBtn.style.display = 'none';
            }
        }

        function getSelectedMerkezIds() {
            const selectedCheckboxes = document.querySelectorAll('.merkez-checkbox:checked');
            return Array.from(selectedCheckboxes).map(checkbox => checkbox.getAttribute('data-merkez-id'));
        }

        function showOnMap(merkezId) {
            // Backend'den merkez bilgilerini al
            fetch(`/api/merkez/${merkezId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Merkez bilgisi alınamadı');
                    }
                    return response.json();
                })
                .then(merkez => {
                    if (!merkez.lat || !merkez.lon) {
                        alert('Bu merkez için konum bilgisi bulunamadı.');
                        return;
                    }
                    
                    // Harita modalını aç
                    const mapModal = new bootstrap.Modal(document.getElementById('mapModal'));
                    document.getElementById('mapModalLabel').innerHTML = 
                        `<i class="fas fa-map-marker-alt me-2"></i>${merkez.title}`;
                    mapModal.show();
                    
                    // Harita başlat
                    setTimeout(() => {
                        initializeMap();
                        clearMarkers();
                        
                        // Marker ekle
                        const marker = L.marker([merkez.lat, merkez.lon]).addTo(map);
                        marker.bindPopup(`
                            <div class="text-center">
                                <b class="text-success">${merkez.title}</b><br>
                                <small class="text-muted">${merkez.content}</small><br>
                                <small><i class="fas fa-map-marker-alt"></i> ${merkez.adres}</small>
                            </div>
                        `).openPopup();
                        
                        // Haritayı merkeze odakla
                        map.setView([merkez.lat, merkez.lon], 15);
                        markers.push(marker);
                    }, 300);
                })
                .catch(error => {
                    console.error('Merkez bilgisi alınamadı:', error);
                    alert('Merkez bilgisi yüklenirken hata oluştu.');
                });
        }

        function showMultipleOnMap(merkezIds) {
            // Backend'den birden fazla merkez bilgisi al
            fetch('/api/merkezler', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ids: merkezIds })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Merkez bilgileri alınamadı');
                }
                return response.json();
            })
            .then(merkezler => {
                const validMerkezler = merkezler.filter(m => m.lat && m.lon);
                
                if (validMerkezler.length === 0) {
                    alert('Seçili merkezler için konum bilgisi bulunamadı.');
                    return;
                }
                
                // Harita modalını aç
                const mapModal = new bootstrap.Modal(document.getElementById('mapModal'));
                document.getElementById('mapModalLabel').innerHTML = 
                    `<i class="fas fa-map-marked-alt me-2"></i>Seçili Atık Merkezleri (${validMerkezler.length})`;
                mapModal.show();
                
                setTimeout(() => {
                    initializeMap();
                    clearMarkers();
                    
                    // Tüm merkezler için marker ekle
                    const group = new L.featureGroup();
                    
                    validMerkezler.forEach((merkez, index) => {
                        // Farklı renkli markerlar için
                        const markerColor = index === 0 ? 'red' : 'blue';
                        
                        const marker = L.marker([merkez.lat, merkez.lon]).addTo(map);
                        marker.bindPopup(`
                            <div class="text-center">
                                <b class="text-success">${merkez.title}</b><br>
                                <small class="text-muted">${merkez.content}</small><br>
                                <small><i class="fas fa-map-marker-alt"></i> ${merkez.adres}</small>
                            </div>
                        `);
                        
                        markers.push(marker);
                        group.addLayer(marker);
                    });
                    
                    // Tüm markerları kapsayacak şekilde zoom ayarla
                    if (validMerkezler.length === 1) {
                        map.setView([validMerkezler[0].lat, validMerkezler[0].lon], 15);
                    } else {
                        map.fitBounds(group.getBounds().pad(0.1));
                    }
                }, 300);
            })
            .catch(error => {
                console.error('Merkez bilgileri alınamadı:', error);
                alert('Merkez bilgileri yüklenirken hata oluştu.');
            });
        }
    });
    </script>
</body>
</html>
