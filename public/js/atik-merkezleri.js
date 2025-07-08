// Harita değişkenleri
let map = null;
let markers = [];

// HARİTA tab'ına geç (Global fonksiyon onclick için)
function switchToMapTab() {
    const haritaTab = document.getElementById('harita-tab');
    if (haritaTab) {
        haritaTab.click();
    }
}

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

// DOM yüklendiğinde çalışacak kod
document.addEventListener('DOMContentLoaded', function() {
    
    // Birleştirilmiş card selection fonksiyonu
    function updateCardSelection(card, isSelected, selectorType = 'filtered') {
        const mapButtonContainer = card.querySelector('.map-button-container');
        
        if (isSelected) {
            card.classList.add('selected');
            if (mapButtonContainer) {
                mapButtonContainer.style.display = 'block';
            }
        } else {
            card.classList.remove('selected');
            if (mapButtonContainer) {
                mapButtonContainer.style.display = 'none';
            }
        }
    }

    // Birleştirilmiş selected count güncelleme fonksiyonu
    function updateSelectedCount(selectorType = 'filtered') {
        const isAll = selectorType === 'all';
        const checkboxSelector = isAll ? '.all-merkez-checkbox:checked' : '.merkez-checkbox:checked';
        const selectedCheckboxes = document.querySelectorAll(checkboxSelector);
        const count = selectedCheckboxes.length;
        
        // Element referansları
        const selectedCount = document.getElementById(isAll ? 'allSelectedCount' : 'selectedCount');
        const countText = document.getElementById(isAll ? 'allCountText' : 'countText');
        const showSelectedBtn = document.getElementById(isAll ? 'showAllSelectedOnMap' : 'showSelectedOnMap');
        const clearBtn = document.getElementById(isAll ? 'clearAllSelection' : 'clearFilteredSelection');
        
        if (count > 0) {
            if (countText) countText.textContent = count;
            if (selectedCount) selectedCount.style.display = 'block';
            if (clearBtn) clearBtn.style.display = 'block';
            
            // Birden fazla merkez seçilmişse "Seçilenleri Haritada Göster" butonunu göster
            if (showSelectedBtn) {
                showSelectedBtn.style.display = count > 1 ? 'block' : 'none';
            }
        } else {
            if (selectedCount) selectedCount.style.display = 'none';
            if (showSelectedBtn) showSelectedBtn.style.display = 'none';
            if (clearBtn) clearBtn.style.display = 'none';
        }
    }

    // Birleştirilmiş selected merkez IDs alma fonksiyonu
    function getSelectedMerkezIds(selectorType = 'filtered') {
        const checkboxSelector = selectorType === 'all' ? '.all-merkez-checkbox:checked' : '.merkez-checkbox:checked';
        const selectedCheckboxes = document.querySelectorAll(checkboxSelector);
        return Array.from(selectedCheckboxes).map(checkbox => checkbox.getAttribute('data-merkez-id'));
    }

    // Birleştirilmiş temizleme fonksiyonu
    function clearSelection(selectorType = 'filtered') {
        const checkboxSelector = selectorType === 'all' ? '.all-merkez-checkbox:checked' : '.merkez-checkbox:checked';
        const checkboxes = document.querySelectorAll(checkboxSelector);
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            const card = checkbox.closest('.selectable-card');
            updateCardSelection(card, false, selectorType);
        });
        updateSelectedCount(selectorType);
    }

    // Birleştirilmiş event listener ekleme fonksiyonu
    function attachEventListeners(container, selectorType = 'filtered') {
        const isAll = selectorType === 'all';
        const checkboxClass = isAll ? '.all-merkez-checkbox' : '.merkez-checkbox';
        
        const cards = container.querySelectorAll('.selectable-card');
        const checkboxes = container.querySelectorAll(checkboxClass);
        const buttons = container.querySelectorAll('.haritada-goster-btn');

        // Card tıklama olayları
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.classList.contains('merkez-checkbox') || 
                    e.target.classList.contains('all-merkez-checkbox') ||
                    e.target.classList.contains('haritada-goster-btn') ||
                    e.target.closest('.haritada-goster-btn')) {
                    return;
                }

                const checkbox = this.querySelector(checkboxClass);
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    updateCardSelection(this, checkbox.checked, selectorType);
                    updateSelectedCount(selectorType);
                }
            });
        });

        // Checkbox değişim olayları
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                e.stopPropagation();
                const card = this.closest('.selectable-card');
                updateCardSelection(card, this.checked, selectorType);
                updateSelectedCount(selectorType);
            });
        });

        // Haritada göster buton olayları
        buttons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const merkezId = this.getAttribute('data-merkez-id');
                showOnMap(merkezId);
            });
        });
    }

    // İlk yükleme için event listener'ları ekle
    attachEventListeners(document, 'filtered'); // Filtrelenmiş sonuçlar için
    attachEventListeners(document.getElementById('allMerkezlerContainer') || document, 'all'); // Tüm merkezler için

    // Seçilenleri haritada göster butonları
    document.getElementById('showSelectedOnMap')?.addEventListener('click', function() {
        const selectedMerkezIds = getSelectedMerkezIds('filtered');
        if (selectedMerkezIds.length > 0) {
            showMultipleOnMap(selectedMerkezIds);
        }
    });

    document.getElementById('showAllSelectedOnMap')?.addEventListener('click', function() {
        const selectedMerkezIds = getSelectedMerkezIds('all');
        if (selectedMerkezIds.length > 0) {
            showMultipleOnMap(selectedMerkezIds);
        }
    });

    // Temizleme butonları
    document.getElementById('clearFilteredSelection')?.addEventListener('click', function() {
        clearSelection('filtered');
    });

    document.getElementById('clearAllSelection')?.addEventListener('click', function() {
        clearSelection('all');
    });

    // Yeni yüklenen kartlar için event listener'ları ekle (public fonksiyon)
    window.attachEventListenersToNewCards = function(container) {
        attachEventListeners(container, 'all');
    };

    function showOnMap(merkezId) {
        // Kullanıcının konumunu kontrol et
        const userLocationData = document.querySelector('[data-user-location]');
        let userLat = null;
        let userLon = null;
        
        if (userLocationData) {
            userLat = parseFloat(userLocationData.getAttribute('data-user-lat'));
            userLon = parseFloat(userLocationData.getAttribute('data-user-lon'));
        }
        
        // Backend'den merkez bilgilerini al
        fetch(`/api/merkez/${merkezId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Merkez bilgisi alınamadı');
                }
                return response.json();
            })
            .then(merkez => {
                // Modalı aç
                const mapModalElement = document.getElementById('mapModal');
                const mapModal = bootstrap.Modal.getOrCreateInstance(mapModalElement);
                
                // Modal title'ı güncelle
                let title = `<i class="fas fa-map-marker-alt me-2"></i>${merkez.title}`;
                if (userLat && userLon) {
                    title += ` <small class="text-muted">+ Konumunuz</small>`;
                }
                document.getElementById('mapModalLabel').innerHTML = title;
                
                // DETAY tab'ını aktif yap
                const detayTab = document.getElementById('detay-tab');
                const haritaTab = document.getElementById('harita-tab');
                const detayContent = document.getElementById('detay-content');
                const haritaContent = document.getElementById('harita-content');
                
                detayTab.classList.add('active');
                haritaTab.classList.remove('active');
                detayContent.classList.add('show', 'active');
                haritaContent.classList.remove('show', 'active');
                
                // DETAY tab'ını doldur
                loadMerkezDetay(merkez, userLat, userLon);
                
                // Harita verilerini global değişkende sakla (HARİTA tab'ı için)
                window.currentModalData = {
                    type: 'single',
                    merkez: merkez,
                    userLat: userLat,
                    userLon: userLon
                };
                
                mapModal.show();
            })
            .catch(error => {
                alert('Merkez bilgisi yüklenirken hata oluştu.');
            });
    }

    // DETAY tab'ını doldur
    function loadMerkezDetay(merkez, userLat, userLon) {
        const detayContainer = document.getElementById('detay-container');
        
        let detayHtml = `
            <div class="row">
                <div class="col-12">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-leaf me-2"></i>${merkez.title}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Açıklama</h6>
                                    <p class="mb-4">${merkez.content}</p>
                                    
                                    <h6 class="text-muted mb-3"><i class="fas fa-map-marker-alt me-2"></i>Adres</h6>
                                    <p class="mb-3">${merkez.adres}</p>
        `;
        
        // Konum bilgisi varsa ekle
        if (merkez.lat && merkez.lon) {
            detayHtml += `
                                    <h6 class="text-muted mb-3"><i class="fas fa-globe me-2"></i>Konum Koordinatları</h6>
                                    <p class="mb-3">
                                        <span class="badge bg-secondary me-2">Enlem: ${parseFloat(merkez.lat).toFixed(6)}°</span>
                                        <span class="badge bg-secondary">Boylam: ${parseFloat(merkez.lon).toFixed(6)}°</span>
                                    </p>
            `;
        }
        
        detayHtml += `
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
        `;
        
        // Mesafe bilgisi varsa ekle
        if (merkez.distance && userLat && userLon) {
            detayHtml += `
                                        <div class="mb-4">
                                            <div class="badge bg-success fs-6 py-2 px-3">
                                                <i class="fas fa-route me-2"></i>
                                                ${parseFloat(merkez.distance).toFixed(1)} km mesafede
                                            </div>
                                        </div>
            `;
        }
        
        // Konum bilgisi varsa haritada göster butonu ekle
        if (merkez.lat && merkez.lon) {
            detayHtml += `
                                        <button class="btn btn-primary btn-lg w-100 mb-3" onclick="switchToMapTab()">
                                            <i class="fas fa-map me-2"></i>Haritada Göster
                                        </button>
            `;
        }
        
        // Kullanıcı konumu varsa ekle
        if (userLat && userLon) {
            detayHtml += `
                                        <div class="alert alert-info">
                                            <h6 class="mb-2"><i class="fas fa-user-circle me-2"></i>Konumunuz</h6>
                                            <small>
                                                Enlem: ${userLat.toFixed(6)}°<br>
                                                Boylam: ${userLon.toFixed(6)}°
                                            </small>
                                        </div>
            `;
        }
        
        detayHtml += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        detayContainer.innerHTML = detayHtml;
    }

    // Birden fazla merkez için detay
    function loadMultipleMerkezDetay(merkezler, userLat, userLon) {
        const detayContainer = document.getElementById('detay-container');
        
        let detayHtml = `
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="alert alert-success">
                        <h5 class="mb-2">
                            <i class="fas fa-map-marked-alt me-2"></i>Seçili Atık Merkezleri (${merkezler.length})
                        </h5>
                        <p class="mb-0">Aşağıda seçtiğiniz ${merkezler.length} atık merkezinin detayları listelenmektedir.</p>
                        ${merkezler.filter(m => m.lat && m.lon).length > 0 ? `
                        <button class="btn btn-primary mt-2" onclick="switchToMapTab()">
                            <i class="fas fa-map me-2"></i>Tümünü Haritada Göster
                        </button>
                        ` : ''}
                    </div>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-md-2 g-4">
        `;
        
        merkezler.forEach((merkez, index) => {
            detayHtml += `
                <div class="col">
                    <div class="card h-100 ${merkez.lat && merkez.lon ? 'border-success' : 'border-warning'}">
                        <div class="card-header ${merkez.lat && merkez.lon ? 'bg-success text-white' : 'bg-warning text-dark'}">
                            <h6 class="mb-0">
                                <i class="fas fa-leaf me-2"></i>${merkez.title}
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="card-text">${merkez.content}</p>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>${merkez.adres}
                            </small>
                            
                            ${merkez.distance ? `
                            <div class="mt-2">
                                <span class="badge bg-success">
                                    <i class="fas fa-route me-1"></i>${parseFloat(merkez.distance).toFixed(1)} km
                                </span>
                            </div>
                            ` : ''}
                            
                            ${merkez.lat && merkez.lon ? `
                            <div class="mt-2">
                                <small class="text-muted">
                                    Enlem: ${parseFloat(merkez.lat).toFixed(4)}° | 
                                    Boylam: ${parseFloat(merkez.lon).toFixed(4)}°
                                </small>
                            </div>
                            ` : `
                            <div class="mt-2">
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Konum bilgisi bulunamadı
                                </small>
                            </div>
                            `}
                        </div>
                    </div>
                </div>
            `;
        });
        
        detayHtml += `
            </div>
        `;
        
        detayContainer.innerHTML = detayHtml;
    }

    function showMultipleOnMap(merkezIds) {
        // Kullanıcının konumunu kontrol et (konuma göre arama sayfasından geliyorsa)
        const userLocationData = document.querySelector('[data-user-location]');
        let userLat = null;
        let userLon = null;
        
        if (userLocationData) {
            userLat = parseFloat(userLocationData.getAttribute('data-user-lat'));
            userLon = parseFloat(userLocationData.getAttribute('data-user-lon'));
        }
        
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
            // Modalı aç
            const mapModalElement = document.getElementById('mapModal');
            const mapModal = bootstrap.Modal.getOrCreateInstance(mapModalElement);
            
            // Modal title'ı güncelle
            let title = `<i class="fas fa-map-marked-alt me-2"></i>Seçili Atık Merkezleri (${merkezler.length})`;
            if (userLat && userLon) {
                title += ` <small class="text-muted">+ Konumunuz</small>`;
            }
            document.getElementById('mapModalLabel').innerHTML = title;
            
            // DETAY tab'ını aktif yap
            const detayTab = document.getElementById('detay-tab');
            const haritaTab = document.getElementById('harita-tab');
            const detayContent = document.getElementById('detay-content');
            const haritaContent = document.getElementById('harita-content');
            
            detayTab.classList.add('active');
            haritaTab.classList.remove('active');
            detayContent.classList.add('show', 'active');
            haritaContent.classList.remove('show', 'active');
            
            // DETAY tab'ını doldur
            loadMultipleMerkezDetay(merkezler, userLat, userLon);
            
            // Harita verilerini global değişkende sakla (HARİTA tab'ı için)
            window.currentModalData = {
                type: 'multiple',
                merkezler: merkezler,
                userLat: userLat,
                userLon: userLon
            };
            
            mapModal.show();
        })
        .catch(error => {
            alert('Merkez bilgileri yüklenirken hata oluştu.');
        });
    }

    // HARİTA tab'ı için harita initialize et
    function initializeModalMap() {
        if (!window.currentModalData) return;
        
        const { type, userLat, userLon } = window.currentModalData;
        
        setTimeout(() => {
            initializeMap();
            clearMarkers();
            
            const group = new L.featureGroup();
            
            // Kullanıcının konumunu ekle (varsa)
            if (userLat && userLon) {
                const userIcon = L.divIcon({
                    html: '<i class="fas fa-user-circle" style="color:rgb(255, 0, 0); font-size: 40px;"></i>',
                    iconSize: [60, 60],
                    className: 'custom-div-icon'
                });
                
                const userMarker = L.marker([userLat, userLon], { icon: userIcon }).addTo(map);
                userMarker.bindPopup(`
                    <div class="text-center">
                        <b class="text-danger"><i class="fas fa-user-circle me-1"></i>Konumunuz</b><br>
                        <small class="text-muted">Enlem: ${userLat.toFixed(6)}°<br>Boylam: ${userLon.toFixed(6)}°</small>
                    </div>
                `);
                
                markers.push(userMarker);
                group.addLayer(userMarker);
            }
            
            if (type === 'single' && window.currentModalData.merkez) {
                const merkez = window.currentModalData.merkez;
                
                if (!merkez.lat || !merkez.lon) {
                    alert('Bu merkez için konum bilgisi bulunamadı.');
                    return;
                }
                
                // Atık merkezi marker'ı ekle
                const markerIcon = L.divIcon({
                    html: '<i class="fas fa-leaf" style="color:rgb(46, 160, 12); font-size: 40px;"></i>',
                    iconSize: [60, 60],
                    className: 'custom-div-icon'
                });
                
                const marker = L.marker([merkez.lat, merkez.lon], { icon: markerIcon }).addTo(map);
                
                let popupContent = `
                    <div class="text-center">
                        <b class="text-success">${merkez.title}</b><br>
                        <small class="text-muted">${merkez.content}</small><br>
                        <small><i class="fas fa-map-marker-alt"></i> ${merkez.adres}</small>
                `;
                
                // Eğer mesafe bilgisi varsa ekle
                if (merkez.distance) {
                    popupContent += `<br><span class="badge bg-success mt-1">
                        <i class="fas fa-route me-1"></i>${parseFloat(merkez.distance).toFixed(1)} km
                    </span>`;
                }
                
                popupContent += `</div>`;
                
                marker.bindPopup(popupContent).openPopup();
                markers.push(marker);
                group.addLayer(marker);
                
            } else if (type === 'multiple' && window.currentModalData.merkezler) {
                const validMerkezler = window.currentModalData.merkezler.filter(m => m.lat && m.lon);
                
                if (validMerkezler.length === 0) {
                    alert('Seçili merkezler için konum bilgisi bulunamadı.');
                    return;
                }
                
                validMerkezler.forEach((merkez, index) => {
                    // Atık merkezleri için yeşil marker
                    const markerIcon = L.divIcon({
                        html: '<i class="fas fa-leaf" style="color:rgb(46, 160, 12); font-size: 40px;"></i>',
                        iconSize: [60, 60],
                        className: 'custom-div-icon'
                    });
                    
                    const marker = L.marker([merkez.lat, merkez.lon], { icon: markerIcon }).addTo(map);
                    
                    let popupContent = `
                        <div class="text-center">
                            <b class="text-success">${merkez.title}</b><br>
                            <small class="text-muted">${merkez.content}</small><br>
                            <small><i class="fas fa-map-marker-alt"></i> ${merkez.adres}</small>
                    `;
                    
                    // Eğer mesafe bilgisi varsa ekle
                    if (merkez.distance) {
                        popupContent += `<br><span class="badge bg-success mt-1">
                            <i class="fas fa-route me-1"></i>${parseFloat(merkez.distance).toFixed(1)} km
                        </span>`;
                    }
                    
                    popupContent += `</div>`;
                    
                    marker.bindPopup(popupContent);
                    
                    markers.push(marker);
                    group.addLayer(marker);
                });
            }
            
            // Haritayı ayarla
            if (group.getLayers().length === 1) {
                map.setView(group.getLayers()[0].getLatLng(), 15);
            } else if (group.getLayers().length > 1) {
                if (userLat && userLon) {
                    // Hem kullanıcı hem merkez varsa ikisini de kapsayacak şekilde zoom yap
                    map.fitBounds(group.getBounds().pad(0.2));
                } else {
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            }
        }, 300);
    }

    // Harita modalı kapatıldığında temizleme işlemleri
    const mapModal = document.getElementById('mapModal');
    if (mapModal) {
        // Modal tamamen kapandığında
        mapModal.addEventListener('hidden.bs.modal', function () {
            // Global verileri temizle
            window.currentModalData = null;
            
            // Backdrop'u manuel olarak kaldır
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                
                // Body'den modal class'larını kaldır
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }, 100);
            
            // URL'de filtre parametresi varsa ana sayfaya dön
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('filter')) {
                // URL'den filtre parametrelerini temizle ve sayfayı yenile
                const url = new URL(window.location);
                url.searchParams.delete('filter');
                window.location.href = url.toString();
            } else {
                // Sadece seçimleri temizle
                clearSelection('all');
                clearSelection('filtered');
            }
            
            // Haritayı temizle
            if (map) {
                clearMarkers();
            }
            
            // DETAY tab'ını temizle
            const detayContainer = document.getElementById('detay-container');
            if (detayContainer) {
                detayContainer.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <p class="mt-3 text-muted">Merkez bilgileri yükleniyor...</p>
                    </div>
                `;
            }
        });
        
        // Modal kapanmaya başladığında da temizle
        mapModal.addEventListener('hide.bs.modal', function () {
            // Haritayı hemen temizle
            if (map) {
                clearMarkers();
            }
        });
    }

    // Arama formu Enter tuşu desteği
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    }

    // "Daha Fazla Yükle" buton event listener
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            loadMoreMerkezler();
        });
    }

    // Konuma göre arama butonu
    const konumBtn = document.getElementById("konuma-gore-ara");
    if (konumBtn) {
        konumBtn.addEventListener("click", function () {
            konumBtn.disabled = true;
            konumBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Konum Alınıyor...';
            
            if (!navigator.geolocation) {
                konumBtn.disabled = false;
                konumBtn.innerHTML = '<i class="fas fa-location-arrow me-1"></i> KONUMUMA GÖRE BUL';
                alert("Tarayıcınız konum almayı desteklemiyor.");
                return;
            }

            const options = {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 300000
            };
            
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    if (!lat || !lon || isNaN(lat) || isNaN(lon)) {
                        konumBtn.disabled = false;
                        konumBtn.innerHTML = '<i class="fas fa-location-arrow me-1"></i> KONUMUMA GÖRE BUL';
                        alert('Geçersiz konum bilgisi alındı. Lütfen tekrar deneyin.');
                        return;
                    }

                    if (lat < -90 || lat > 90 || lon < -180 || lon > 180) {
                        konumBtn.disabled = false;
                        konumBtn.innerHTML = '<i class="fas fa-location-arrow me-1"></i> KONUMUMA GÖRE BUL';
                        alert('Alınan koordinatlar geçersiz. Lütfen tekrar deneyin.');
                        return;
                    }
                    
                    window.location.href = `/konuma-gore?lat=${lat}&lon=${lon}`;
                }, 
                function (error) {
                    konumBtn.disabled = false;
                    konumBtn.innerHTML = '<i class="fas fa-location-arrow me-1"></i> KONUMUMA GÖRE BUL';
                    
                    let errorMessage = "Konum alınamadı.\n\n";
                    let debugInfo = "";
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += "Konum izni reddedildi.";
                            debugInfo = "Çözüm: Tarayıcının adres çubuğundaki konum icon'una tıklayın ve 'İzin Ver' seçin.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += "Konum bilgisi mevcut değil.";
                            debugInfo = "Çözüm: GPS'inizi açın, WiFi/mobil veriye bağlı olun, açık alanda deneyin.";
                            break;
                        case error.TIMEOUT:
                            errorMessage += "Konum alma zaman aşımı (15 saniye).";
                            debugInfo = "Çözüm: İnternet bağlantınızı kontrol edin, tekrar deneyin.";
                            break;
                        default:
                            errorMessage += "Bilinmeyen hata oluştu.";
                            debugInfo = "Hata kodu: " + error.code;
                            break;
                    }
                    
                    alert(errorMessage + "\n\n" + debugInfo);
                }, 
                options
            );
        });
    }

    // Infinite Scroll İmplementasyonu
    let isLoading = false;
    let hasMoreData = true;
    let currentOffset = 20;

    function loadMoreMerkezler() {
        if (isLoading || !hasMoreData) return;

        isLoading = true;
        
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        const loadMoreContainer = document.getElementById('loadMoreContainer');
        const loadingIndicator = document.getElementById('loadingIndicator');
        
        if (loadMoreContainer) loadMoreContainer.style.display = 'none';
        if (loadingIndicator) loadingIndicator.style.display = 'block';

        fetch(`/api/load-more?offset=${currentOffset}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Veriler yüklenemedi');
                }
                return response.json();
            })
            .then(data => {
                const container = document.getElementById('allMerkezlerContainer');
                
                data.merkezler.forEach(merkez => {
                    const colDiv = document.createElement('div');
                    colDiv.className = 'col';
                    colDiv.innerHTML = `
                        <div class="card border-primary h-100 selectable-card position-relative" data-merkez-id="${merkez.id}" style="cursor: pointer; transition: all 0.3s ease;">
                            <div class="card-body">
                                <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                    <input class="form-check-input all-merkez-checkbox" type="checkbox" id="all-merkez-${merkez.id}" data-merkez-id="${merkez.id}">
                                </div>
                                <h5 class="card-title pe-5">${merkez.title}</h5>
                                <p class="card-text">${merkez.content}</p>
                                <small class="text-muted">Adres: ${merkez.adres}</small>
                            </div>
                            <div class="map-button-container position-absolute" style="bottom: 10px; right: 10px; display: none;">
                                <button class="btn btn-success btn-sm haritada-goster-btn" data-merkez-id="${merkez.id}">
                                    <i class="fas fa-info-circle me-1"></i> Detay Görüntüle-Haritada Göster
                                </button>
                            </div>
                        </div>
                    `;
                    container.appendChild(colDiv);
                });

                window.attachEventListenersToNewCards(container);

                currentOffset += 20;
                hasMoreData = data.hasMore;

                if (!hasMoreData) {
                    document.getElementById('endOfData').style.display = 'block';
                } else {
                    if (loadMoreContainer) loadMoreContainer.style.display = 'block';
                }
            })
            .catch(error => {
                alert('Daha fazla merkez yüklenirken hata oluştu.');
                if (loadMoreContainer) loadMoreContainer.style.display = 'block';
            })
            .finally(() => {
                isLoading = false;
                if (loadingIndicator) loadingIndicator.style.display = 'none';
            });
    }

    // HARİTA tab'ına tıklandığında harita initialize et
    const haritaTab = document.getElementById('harita-tab');
    if (haritaTab) {
        haritaTab.addEventListener('shown.bs.tab', function (e) {
            initializeModalMap();
        });
    }
});