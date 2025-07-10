import { getHeaderClass } from '../utils/borderUtils.js';

/**
 * Modal Module
 * Bootstrap modal işlemlerini yönetir
 */
export class ModalModule {
    constructor(apiClient) {
        this.apiClient = apiClient;
        this.currentModalData = null;
    }

    /**
     * Tek merkez için modal aç
     */
    async showSingleMerkezModal(merkezId) {
        // Kullanıcının konumunu kontrol et
        const userLocationData = document.querySelector('[data-user-location]');
        let userLat = null;
        let userLon = null;
        
        if (userLocationData) {
            userLat = parseFloat(userLocationData.getAttribute('data-user-lat'));
            userLon = parseFloat(userLocationData.getAttribute('data-user-lon'));
        }
        
        try {
            // Backend'den merkez bilgilerini al
            const merkez = await this.apiClient.getMerkez(merkezId);
            
            // Modal title'ı güncelle
            let title = `<i class="fas fa-map-marker-alt me-2"></i>${merkez.title}`;
            if (userLat && userLon) {
                title += ` <small class="text-muted">+ Konumunuz</small>`;
            }
            document.getElementById('mapModalLabel').innerHTML = title;
            
            // DETAY tab'ını aktif yap
            this.activateDetailTab();
            
            // DETAY tab'ını doldur
            this.loadMerkezDetay(merkez, userLat, userLon);
            
            // Harita verilerini global değişkende sakla
            this.currentModalData = {
                type: 'single',
                merkez: merkez,
                userLat: userLat,
                userLon: userLon
            };
            
            // Modal'ı aç
            this.openModal();
            
        } catch (error) {
            alert('Merkez bilgisi yüklenirken hata oluştu.');
            console.error('Modal Error:', error);
        }
    }

    /**
     * Birden fazla merkez için modal aç
     */
    async showMultipleMerkezModal(merkezIds) {
        if (!Array.isArray(merkezIds) || merkezIds.length === 0) {
            alert('Seçili merkez bulunamadı.');
            return;
        }

        // Kullanıcının konumunu kontrol et
        const userLocationData = document.querySelector('[data-user-location]');
        let userLat = null;
        let userLon = null;
        
        if (userLocationData) {
            userLat = parseFloat(userLocationData.getAttribute('data-user-lat'));
            userLon = parseFloat(userLocationData.getAttribute('data-user-lon'));
        }
        
        try {
            // Backend'den birden fazla merkez bilgisi al
            const merkezler = await this.apiClient.getMultipleMerkezler(merkezIds);
            
            // Modal title'ı güncelle
            let title = `<i class="fas fa-map-marked-alt me-2"></i>Seçili Atık Merkezleri (${merkezler.length})`;
            if (userLat && userLon) {
                title += ` <small class="text-muted">+ Konumunuz</small>`;
            }
            document.getElementById('mapModalLabel').innerHTML = title;
            
            // DETAY tab'ını aktif yap
            this.activateDetailTab();
            
            // DETAY tab'ını doldur
            this.loadMultipleMerkezDetay(merkezler, userLat, userLon);
            
            // Harita verilerini global değişkende sakla
            this.currentModalData = {
                type: 'multiple',
                merkezler: merkezler,
                userLat: userLat,
                userLon: userLon
            };
            
            // Modal'ı aç
            this.openModal();
            
        } catch (error) {
            alert('Merkez bilgileri yüklenirken hata oluştu.');
            console.error('Modal Error:', error);
        }
    }

    /**
     * Detay tab'ını aktif yap
     */
    activateDetailTab() {
        const detayTab = document.getElementById('detay-tab');
        const haritaTab = document.getElementById('harita-tab');
        const detayContent = document.getElementById('detay-content');
        const haritaContent = document.getElementById('harita-content');
        
        if (detayTab && haritaTab && detayContent && haritaContent) {
            detayTab.classList.add('active');
            haritaTab.classList.remove('active');
            detayContent.classList.add('show', 'active');
            haritaContent.classList.remove('show', 'active');
        }
    }

    /**
     * Tek merkez detayını yükle
     */
    loadMerkezDetay(merkez, userLat, userLon) {
        const detayContainer = document.getElementById('detay-container');
        if (!detayContainer) return;
        
        const borderClass = merkez.border_class || 'border-secondary';
        const headerClass = getHeaderClass(borderClass);
        let detayHtml = `
            <div class="row">
                <div class="col-12">
                    <div class="card ${borderClass}">
                        <div class="card-header ${headerClass}">
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
                                        <button class="btn btn-primary btn-lg w-100 mb-3" onclick="window.modalModule.switchToMapTab()">
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

    /**
     * Birden fazla merkez detayını yükle
     */
    loadMultipleMerkezDetay(merkezler, userLat, userLon) {
        const detayContainer = document.getElementById('detay-container');
        if (!detayContainer) return;
        
        let detayHtml = `
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="alert alert-success">
                        <h5 class="mb-2">
                            <i class="fas fa-map-marked-alt me-2"></i>Seçili Atık Merkezleri (${merkezler.length})
                        </h5>
                        <p class="mb-0">Aşağıda seçtiğiniz ${merkezler.length} atık merkezinin detayları listelenmektedir.</p>
                        ${merkezler.filter(m => m.lat && m.lon).length > 0 ? `
                        <button class="btn btn-primary mt-2" onclick="window.modalModule.switchToMapTab()">
                            <i class="fas fa-map me-2"></i>Tümünü Haritada Göster
                        </button>
                        ` : ''}
                    </div>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-md-2 g-4">
        `;
        
        merkezler.forEach((merkez, index) => {
            const borderClass = merkez.border_class || 'border-secondary';
            const headerClass = getHeaderClass(borderClass);
            detayHtml += `
                <div class="col">
                    <div class="card h-100 ${borderClass}">
                        <div class="card-header ${headerClass}">
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
        
        detayHtml += `</div>`;
        detayContainer.innerHTML = detayHtml;
    }

    /**
     * Modal'ı aç
     */
    openModal() {
        const mapModalElement = document.getElementById('mapModal');
        if (mapModalElement) {
            const mapModal = bootstrap.Modal.getOrCreateInstance(mapModalElement);
            mapModal.show();
        }
    }

    /**
     * Harita tab'ına geç
     */
    switchToMapTab() {
        const haritaTab = document.getElementById('harita-tab');
        if (haritaTab) {
            haritaTab.click();
        }
    }

    /**
     * Mevcut modal verilerini al
     */
    getCurrentModalData() {
        return this.currentModalData;
    }

    /**
     * Modal verilerini temizle
     */
    clearModalData() {
        this.currentModalData = null;
    }

    /**
     * Modal event listener'larını ayarla
     */
    setupModalEventListeners() {
        const mapModal = document.getElementById('mapModal');
        if (mapModal) {
            // Modal tamamen kapandığında
            mapModal.addEventListener('hidden.bs.modal', () => {
                this.clearModalData();
                
                // Backdrop'u manuel olarak kaldır
                setTimeout(() => {
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }, 100);
            });

            // Modal kapanmaya başladığında haritayı temizle
            mapModal.addEventListener('hide.bs.modal', () => {
                if (window.mapModule) {
                    window.mapModule.clearMarkers();
                }
            });
        }

        // YouTube modal - Video reset
        const youtubeModal = document.getElementById('youtubeModal');
        if (youtubeModal) {
            youtubeModal.addEventListener('hidden.bs.modal', () => {
                const iframe = youtubeModal.querySelector('iframe');
                if (iframe) {
                    const src = iframe.src;
                    iframe.src = '';
                    iframe.src = src;
                }
            });
        }
    }
} 