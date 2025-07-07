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

// DOM yüklendiğinde çalışacak kod
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
        const clearFilteredBtn = document.getElementById('clearFilteredSelection');
        
        if (count > 0) {
            countText.textContent = count;
            selectedCount.style.display = 'block';
            clearFilteredBtn.style.display = 'block';
            
            // Birden fazla merkez seçilmişse "Seçilenleri Haritada Göster" butonunu göster
            if (count > 1) {
                showSelectedOnMapBtn.style.display = 'block';
            } else {
                showSelectedOnMapBtn.style.display = 'none';
            }
        } else {
            selectedCount.style.display = 'none';
            showSelectedOnMapBtn.style.display = 'none';
            clearFilteredBtn.style.display = 'none';
        }
    }

    function getSelectedMerkezIds() {
        const selectedCheckboxes = document.querySelectorAll('.merkez-checkbox:checked');
        return Array.from(selectedCheckboxes).map(checkbox => checkbox.getAttribute('data-merkez-id'));
    }

    // Tüm merkezler için seçim fonksiyonları
    function getAllSelectedMerkezIds() {
        const selectedCheckboxes = document.querySelectorAll('.all-merkez-checkbox:checked');
        return Array.from(selectedCheckboxes).map(checkbox => checkbox.getAttribute('data-merkez-id'));
    }

    function updateAllSelectedCount() {
        const selectedCheckboxes = document.querySelectorAll('.all-merkez-checkbox:checked');
        const count = selectedCheckboxes.length;
        const allSelectedCount = document.getElementById('allSelectedCount');
        const allCountText = document.getElementById('allCountText');
        const showAllSelectedOnMapBtn = document.getElementById('showAllSelectedOnMap');
        const clearAllBtn = document.getElementById('clearAllSelection');
        
        if (count > 0) {
            allCountText.textContent = count;
            allSelectedCount.style.display = 'block';
            clearAllBtn.style.display = 'block';
            
            if (count > 1) {
                showAllSelectedOnMapBtn.style.display = 'block';
            } else {
                showAllSelectedOnMapBtn.style.display = 'none';
            }
        } else {
            allSelectedCount.style.display = 'none';
            showAllSelectedOnMapBtn.style.display = 'none';
            clearAllBtn.style.display = 'none';
        }
    }

    function updateAllCardSelection(card, isSelected) {
        const mapButtonContainer = card.querySelector('.map-button-container');
        
        if (isSelected) {
            card.classList.add('selected');
            mapButtonContainer.style.display = 'block';
        } else {
            card.classList.remove('selected');
            mapButtonContainer.style.display = 'none';
        }
    }

    // Yeni yüklenen kartlar için event listener'ları ekle
    function attachEventListenersToNewCards(container) {
        const newCards = container.querySelectorAll('.selectable-card');
        const newCheckboxes = container.querySelectorAll('.all-merkez-checkbox');
        const newButtons = container.querySelectorAll('.haritada-goster-btn');

        newCards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.classList.contains('all-merkez-checkbox') || 
                    e.target.classList.contains('haritada-goster-btn') ||
                    e.target.closest('.haritada-goster-btn')) {
                    return;
                }

                const checkbox = this.querySelector('.all-merkez-checkbox');
                checkbox.checked = !checkbox.checked;
                updateAllCardSelection(this, checkbox.checked);
                updateAllSelectedCount();
            });
        });

        newCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                e.stopPropagation();
                const card = this.closest('.selectable-card');
                updateAllCardSelection(card, this.checked);
                updateAllSelectedCount();
            });
        });

        newButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const merkezId = this.getAttribute('data-merkez-id');
                showOnMap(merkezId);
            });
        });
    }

    // Tüm merkezler için başlangıç event listener'ları
    const allCards = document.querySelectorAll('#allMerkezlerContainer .selectable-card');
    const allCheckboxes = document.querySelectorAll('.all-merkez-checkbox');
    
    allCards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.classList.contains('all-merkez-checkbox') || 
                e.target.classList.contains('haritada-goster-btn') ||
                e.target.closest('.haritada-goster-btn')) {
                return;
            }

            const checkbox = this.querySelector('.all-merkez-checkbox');
            checkbox.checked = !checkbox.checked;
            updateAllCardSelection(this, checkbox.checked);
            updateAllSelectedCount();
        });
    });

    allCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            e.stopPropagation();
            const card = this.closest('.selectable-card');
            updateAllCardSelection(card, this.checked);
            updateAllSelectedCount();
        });
    });

    // Tüm seçilenleri haritada göster butonu
    document.getElementById('showAllSelectedOnMap')?.addEventListener('click', function() {
        const selectedMerkezIds = getAllSelectedMerkezIds();
        if (selectedMerkezIds.length > 0) {
            showMultipleOnMap(selectedMerkezIds);
        }
    });

    // Filtrelenmiş sonuçları temizle butonu
    document.getElementById('clearFilteredSelection')?.addEventListener('click', function() {
        clearFilteredSelection();
    });

    // Tüm merkezleri temizle butonu
    document.getElementById('clearAllSelection')?.addEventListener('click', function() {
        clearAllSelection();
    });

    // Seçilenleri haritada göster butonu (filtrelenmiş)
    showSelectedOnMapBtn?.addEventListener('click', function() {
        const selectedMerkezIds = getSelectedMerkezIds();
        if (selectedMerkezIds.length > 0) {
            showMultipleOnMap(selectedMerkezIds);
        }
    });

    // Temizleme fonksiyonları
    function clearFilteredSelection() {
        // Tüm filtrelenmiş checkboxları temizle
        const checkboxes = document.querySelectorAll('.merkez-checkbox:checked');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            const card = checkbox.closest('.selectable-card');
            updateCardSelection(card, false);
        });
        updateSelectedCount();
    }

    function clearAllSelection() {
        // Tüm merkez checkboxlarını temizle
        const checkboxes = document.querySelectorAll('.all-merkez-checkbox:checked');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            const card = checkbox.closest('.selectable-card');
            updateAllCardSelection(card, false);
        });
        updateAllSelectedCount();
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
                const mapModalElement = document.getElementById('mapModal');
                const mapModal = bootstrap.Modal.getOrCreateInstance(mapModalElement);
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
            const mapModalElement = document.getElementById('mapModal');
            const mapModal = bootstrap.Modal.getOrCreateInstance(mapModalElement);
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

    // Harita modalı kapatıldığında temizleme işlemleri
    const mapModal = document.getElementById('mapModal');
    if (mapModal) {
        // Modal tamamen kapandığında
        mapModal.addEventListener('hidden.bs.modal', function () {
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
                if (typeof clearAllSelection === 'function') {
                    clearAllSelection();
                }
                if (typeof clearFilteredSelection === 'function') {
                    clearFilteredSelection();
                }
            }
            
            // Haritayı temizle
            if (map) {
                clearMarkers();
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

    // Infinite Scroll İmplementasyonu
    let isLoading = false;
    let hasMoreData = true;
    let currentOffset = 20; // İlk 20 zaten yüklendi

    function loadMoreMerkezler() {
        if (isLoading || !hasMoreData) return;

        isLoading = true;
        
        // Butonu gizle, loading indicator'ı göster
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
                                    <i class="fas fa-map-marker-alt me-1"></i> Haritada Göster
                                </button>
                            </div>
                        </div>
                    `;
                    container.appendChild(colDiv);
                });

                // Yeni kartlara event listener'ları ekle
                attachEventListenersToNewCards(container);

                currentOffset += 20;
                hasMoreData = data.hasMore;

                if (!hasMoreData) {
                    // Tüm merkezler yüklendi, buton yerine bitiş mesajını göster
                    document.getElementById('endOfData').style.display = 'block';
                } else {
                    // Hala daha fazla veri var, butonu tekrar göster
                    if (loadMoreContainer) loadMoreContainer.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Veri yükleme hatası:', error);
                alert('Daha fazla merkez yüklenirken hata oluştu.');
                // Hata durumunda butonu tekrar göster
                if (loadMoreContainer) loadMoreContainer.style.display = 'block';
            })
            .finally(() => {
                isLoading = false;
                if (loadingIndicator) loadingIndicator.style.display = 'none';
            });
    }
}); 