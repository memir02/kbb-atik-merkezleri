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
                                    
                                    <!-- Rating Widget -->
                                    <div class="rating-widget mt-3" data-merkez-id="${merkez.id}">
                                        <h6 class="text-muted mb-2"><i class="fas fa-star me-2"></i>Değerlendirme</h6>
                                        <div class="rating-display">
                                            ${this.generateRatingDisplay(merkez)}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
        `;
        
        detayHtml += `
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
                            
                            <!-- Rating Widget -->
                            <div class="rating-widget mt-3" data-merkez-id="${merkez.id}">
                                <div class="rating-display">
                                    ${this.generateRatingDisplay(merkez)}
                                </div>
                            </div>

                            <!-- Yorumlar Butonu -->
                            <button class="btn btn-outline-secondary mb-2" id="showCommentsBtn_${merkez.id}">
                                <i class="fas fa-comments me-1"></i> Yorumlar
                            </button>

                            <!-- Comments Section (başta gizli) -->
                            <div class="comments-section mt-3" data-merkez-id="${merkez.id}" style="display:none">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-comments me-1"></i>Yorumlar
                                    <span class="comments-count"></span>
                                </h6>
                                <div class="comments-container">
                                    <div class="comments-loading">
                                        <i class="fas fa-spinner fa-spin"></i> Yükleniyor...
                                    </div>
                                </div>
                            </div>
 
                        </div>
                    </div>
                </div>
            `;
        });
        
        detayHtml += `</div>`;
        detayContainer.innerHTML = detayHtml;
        
        // Her merkez için yorumlar butonuna tıklanınca yorumları yükle ve göster
        merkezler.forEach(merkez => {
            const showBtn = document.getElementById(`showCommentsBtn_${merkez.id}`);
            if (showBtn) {
                showBtn.addEventListener('click', () => {
                    const commentsSection = document.querySelector(`[data-merkez-id='${merkez.id}'].comments-section`);
                    if (commentsSection.style.display === 'none') {
                        commentsSection.style.display = 'block';
                        this.loadComments(merkez.id);
                    } else {
                        commentsSection.style.display = 'none';
                    }
                });
            }
        });
    }

    /**
     * Rating display HTML'i generate et
     */
    generateRatingDisplay(merkez) {
        if (merkez.total_ratings && merkez.total_ratings > 0) {
            // Yıldızları generate et
            const stars = this.generateStars(merkez.average_rating || 0);
            return `
                <div class="d-flex align-items-center">
                    <div class="stars-display me-2">
                        ${stars}
                    </div>
                    <small class="text-muted">
                        ${parseFloat(merkez.average_rating || 0).toFixed(1)} (${merkez.total_ratings} değerlendirme)
                    </small>
                </div>
            `;
        } else {
            return `
                <div class="text-muted small">
                    <i class="fas fa-star text-muted me-1"></i>Henüz değerlendirilmemiş
                </div>
            `;
        }
    }

    /**
     * Yıldız HTML'i generate et
     */
    generateStars(rating) {
        let stars = '';
        const fullStars = Math.floor(rating);
        const hasHalfStar = (rating % 1) >= 0.5;
        
        for (let i = 1; i <= 5; i++) {
            if (i <= fullStars) {
                stars += '<i class="fas fa-star text-warning" style="font-size: 0.9rem;"></i>';
            } else if (i === fullStars + 1 && hasHalfStar) {
                stars += '<i class="fas fa-star-half-alt text-warning" style="font-size: 0.9rem;"></i>';
            } else {
                stars += '<i class="far fa-star text-muted" style="font-size: 0.9rem;"></i>';
            }
        }
        
        return stars;
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
                // Yorumlar içeriğini temizle
                const yorumContent = document.getElementById('yorum-content-inner');
                if (yorumContent) yorumContent.innerHTML = '';
                // Tabları resetle (detay tabı aktif olsun)
                const detayTab = document.getElementById('detay-tab');
                const yorumTab = document.getElementById('yorum-tab');
                const detayContent = document.getElementById('detay-content');
                const yorumContentTab = document.getElementById('yorum-content');
                if (detayTab && yorumTab && detayContent && yorumContentTab) {
                    detayTab.classList.add('active');
                    yorumTab.classList.remove('active');
                    detayContent.classList.add('show', 'active');
                    yorumContentTab.classList.remove('show', 'active');
                }
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

    /**
     * Yorumları yükle
     */
    async loadComments(merkezId) {
        try {
            const response = await this.apiClient.get(`/api/atik-merkezleri/${merkezId}/comments`);
            
            if (response.success) {
                this.displayComments(merkezId, response.comments, response.total_comments);
            } else {
                this.displayComments(merkezId, [], 0);
            }
        } catch (error) {
            console.error('Error loading comments:', error);
            this.displayComments(merkezId, [], 0);
        }
    }

    /**
     * Yorumları görüntüle
     */
    displayComments(merkezId, comments, totalComments) {
        const commentsContainer = document.querySelector(`[data-merkez-id="${merkezId}"] .comments-container`);
        const commentsCount = document.querySelector(`[data-merkez-id="${merkezId}"] .comments-count`);
        
        if (!commentsContainer) return;

        if (totalComments === 0) {
            commentsContainer.innerHTML = `
                <div class="comments-empty">
                    <i class="fas fa-comment-slash"></i>
                    <p class="mb-0">Henüz yorum yapılmamış</p>
                    <small>İlk yorumu siz yapın!</small>
                </div>
            `;
            if (commentsCount) {
                commentsCount.textContent = '';
            }
        } else {
            let commentsHtml = '';
            comments.forEach(comment => {
                commentsHtml += `
                    <div class="comment-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <div class="stars-display">
                                    ${comment.stars_html}
                                </div>
                                <span class="user-name">${comment.user_name}</span>
                            </div>
                            <span class="comment-date">${comment.created_at}</span>
                        </div>
                        <p class="comment-text mb-0">${comment.comment}</p>
                    </div>
                `;
            });
            
            commentsContainer.innerHTML = commentsHtml;
            if (commentsCount) {
                commentsCount.textContent = `(${totalComments})`;
            }
        }
    }

    /**
     * Yorumları yükle ve #yorum-content içine bas
     */
    async loadCommentsToTab() {
        const modalData = this.getCurrentModalData();
        let merkezId = null;
        if (modalData && modalData.type === 'single' && modalData.merkez) {
            merkezId = modalData.merkez.id;
        } else if (modalData && modalData.type === 'multiple' && modalData.merkezler && modalData.merkezler.length === 1) {
            merkezId = modalData.merkezler[0].id;
        }
        const yorumContent = document.getElementById('yorum-content-inner');
        console.log('DEBUG | modalData:', modalData, 'merkezId:', merkezId, 'yorumContent:', yorumContent);
        if (!yorumContent || !merkezId) {
            yorumContent.innerHTML = `<div class='comments-empty'>Yorumlar yüklenemedi.</div>`;
            return;
        }
        yorumContent.innerHTML = `<div class='comments-loading'><i class='fas fa-spinner fa-spin'></i> Yorumlar yükleniyor...</div>`;
        try {
            const response = await this.apiClient.get(`/api/atik-merkezleri/${merkezId}/comments`);
            if (response.success) {
                this.displayCommentsInTab(response.comments, response.total_comments);
            } else {
                this.displayCommentsInTab([], 0);
            }
        } catch (error) {
            yorumContent.innerHTML = `<div class='comments-empty'>Yorumlar yüklenirken hata oluştu.</div>`;
        }
    }

    /**
     * Yorumları #yorum-content-inner içinde göster
     */
    displayCommentsInTab(comments, totalComments) {
        const yorumContent = document.getElementById('yorum-content-inner');
        if (!yorumContent) return;
        if (totalComments === 0) {
            yorumContent.innerHTML = `
                <div class="comments-empty">
                    <i class="fas fa-comment-slash"></i>
                    <p class="mb-0">Henüz yorum yapılmamış</p>
                    <small>İlk yorumu siz yapın!</small>
                </div>
            `;
        } else {
            let commentsHtml = '';
            comments.forEach(comment => {
                commentsHtml += `
                    <div class="comment-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <div class="stars-display">
                                    ${comment.stars_html}
                                </div>
                                <span class="user-name">${comment.user_name}</span>
                            </div>
                            <span class="comment-date">${comment.created_at}</span>
                        </div>
                        <p class="comment-text mb-0">${comment.comment}</p>
                    </div>
                `;
            });
            yorumContent.innerHTML = commentsHtml;
        }
    }
} 