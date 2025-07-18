/**
 * Infinite Scroll Module
 * Sayfalama ve daha fazla veri yükleme işlemlerini yönetir
 */
export class InfiniteScrollModule {
    constructor(apiClient, selectionModule) {
        this.apiClient = apiClient;
        this.selectionModule = selectionModule;
        this.isLoading = false;
        this.hasMoreData = true;
        this.currentOffset = 20; // İlk 20 zaten yüklü
        this.limit = 20;
        this.scrollInfiniteEnabled = false; // Scroll infinite loading varsayılan kapalı
    }

    /**
     * Daha fazla merkez yükle
     */
    async loadMoreMerkezler() {
        if (this.isLoading || !this.hasMoreData) return;

        this.isLoading = true;
        this.updateLoadingState(true);
        
        try {
            const data = await this.apiClient.loadMoreMerkezler(this.currentOffset, this.limit);
            
            if (data.merkezler && data.merkezler.length > 0) {
                this.appendMerkezlerToDOM(data.merkezler);
                this.currentOffset += this.limit;
                this.hasMoreData = data.hasMore;
                
                if (!this.hasMoreData) {
                    this.showEndOfDataMessage();
                }
            } else {
                this.hasMoreData = false;
                this.showEndOfDataMessage();
            }
            
        } catch (error) {
            console.error('Load More Error:', error);
            alert('Daha fazla merkez yüklenirken hata oluştu.');
        } finally {
            this.isLoading = false;
            this.updateLoadingState(false);
        }
    }

    /**
     * Merkezleri DOM'a ekle
     */
    appendMerkezlerToDOM(merkezler) {
        const container = document.getElementById('allMerkezlerContainer');
        if (!container) return;

        const cols = merkezler.map(merkez => {
            return `<div class="col">${this.createMerkezCardHTML(merkez)}</div>`;
        });

        container.insertAdjacentHTML('beforeend', cols.join(''));
        
        // Initialize rating displays for newly added centers
        if (window.ratingModule && window.ratingModule.initializeRatingDisplays) {
            // Small delay to ensure DOM is ready
            setTimeout(() => {
                window.ratingModule.initializeRatingDisplays();
            }, 100);
        }
    }

    /**
     * Merkez card HTML'i oluştur
     */
    createMerkezCardHTML(merkez) {
        const borderClass = merkez.border_class || 'border-secondary';
        const isLoggedIn = window.userLoggedIn || false;
        
        return `
            <div class="card ${borderClass} h-100 selectable-card position-relative" data-merkez-id="${merkez.id}" style="cursor: pointer; transition: all 0.3s ease;">
                <div class="card-body">
                    <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                        <input class="form-check-input all-merkez-checkbox" type="checkbox" id="all-merkez-${merkez.id}" data-merkez-id="${merkez.id}">
                    </div>
                    <h5 class="card-title pe-5">${merkez.title}</h5>
                    <p class="card-text">${merkez.content}</p>
                    <small class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i>${merkez.adres || 'Belirtilmemiş'}
                    </small>
                    
                    <!-- Rating Widget -->
                    <div class="rating-widget mt-3" onclick="event.stopPropagation()">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="rating-display">
                                ${this.generateRatingDisplay(merkez)}
                            </div>
                            <div class="rating-actions d-flex gap-1">
                                ${this.generateRatingActions(merkez.id, isLoggedIn)}
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="map-button-container position-absolute" style="bottom: 10px; right: 10px; display: none;">
                        <button class="btn btn-success btn-sm haritada-goster-btn" data-merkez-id="${merkez.id}">
                            <i class="fas fa-info-circle me-1"></i> Detay Görüntüle-Haritada Göster
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Rating display HTML'i generate et
     */
    generateRatingDisplay(merkez) {
        if (merkez.total_ratings && merkez.total_ratings > 0) {
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
     * Rating action buttons HTML'i generate et
     */
    generateRatingActions(merkezId, isLoggedIn) {
        if (isLoggedIn) {
            return `
                <button class="btn btn-sm btn-outline-danger favorite-btn" data-merkez-id="${merkezId}" title="Favorilere ekle">
                    <i class="far fa-heart"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary rate-btn" data-merkez-id="${merkezId}" title="Puanla">
                    <i class="fas fa-star"></i>
                </button>
            `;
        } else {
            return `
                <a href="/login" class="btn btn-sm btn-outline-danger" title="Favorilere eklemek için giriş yap">
                    <i class="far fa-heart"></i>
                </a>
                <a href="/login" class="btn btn-sm btn-outline-primary" title="Puanlamak için giriş yap">
                    <i class="fas fa-star"></i>
                </a>
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
     * Yükleme durumunu güncelle
     */
    updateLoadingState(isLoading) {
        const loadMoreContainer = document.getElementById('loadMoreContainer');
        const loadingIndicator = document.getElementById('loadingIndicator');
        
        if (isLoading) {
            if (loadMoreContainer) loadMoreContainer.style.display = 'none';
            if (loadingIndicator) loadingIndicator.style.display = 'block';
        } else {
            if (loadingIndicator) loadingIndicator.style.display = 'none';
            if (!this.hasMoreData) {
                if (loadMoreContainer) loadMoreContainer.style.display = 'none';
            } else {
                if (loadMoreContainer) loadMoreContainer.style.display = 'block';
            }
        }
    }

    /**
     * Veri bitti mesajını göster
     */
    showEndOfDataMessage() {
        const endOfDataElement = document.getElementById('endOfData');
        if (endOfDataElement) {
            endOfDataElement.style.display = 'block';
        }
        
        const loadMoreContainer = document.getElementById('loadMoreContainer');
        if (loadMoreContainer) {
            loadMoreContainer.style.display = 'none';
        }
    }

    /**
     * Load more butonunu ayarla
     */
    setupLoadMoreButton() {
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                this.loadMoreMerkezler();
            });
        }
    }

    /**
     * Scroll-based infinite loading ayarla
     */
    setupScrollInfiniteLoading() {
        if (!this.scrollInfiniteEnabled) {
            return; // Scroll infinite loading devre dışı
        }
        
        let ticking = false;
        
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    if (this.shouldLoadMore()) {
                        this.loadMoreMerkezler();
                    }
                    ticking = false;
                });
                ticking = true;
            }
        });
    }

    /**
     * Scroll infinite loading'i aç/kapat
     */
    setScrollInfiniteEnabled(enabled) {
        this.scrollInfiniteEnabled = enabled;
    }

    /**
     * Daha fazla yükleme gerekip gerekmediğini kontrol et
     */
    shouldLoadMore() {
        if (this.isLoading || !this.hasMoreData) return false;
        
        const scrollHeight = document.documentElement.scrollHeight;
        const scrollTop = document.documentElement.scrollTop;
        const clientHeight = document.documentElement.clientHeight;
        
        // Sayfa sonuna 200px kala yükle
        return (scrollTop + clientHeight) >= (scrollHeight - 200);
    }

    /**
     * Modülü resetle
     */
    reset() {
        this.isLoading = false;
        this.hasMoreData = true;
        this.currentOffset = 20;
        
        // UI durumunu resetle
        this.updateLoadingState(false);
        
        const endOfDataElement = document.getElementById('endOfData');
        if (endOfDataElement) {
            endOfDataElement.style.display = 'none';
        }
    }

    /**
     * Mevcut durum bilgisi
     */
    getStatus() {
        return {
            isLoading: this.isLoading,
            hasMoreData: this.hasMoreData,
            currentOffset: this.currentOffset,
            totalLoaded: this.currentOffset
        };
    }

    /**
     * Manuel olarak daha fazla veri olduğunu belirt
     */
    setHasMoreData(hasMore) {
        this.hasMoreData = hasMore;
        this.updateLoadingState(false);
    }

    /**
     * Offset'i manuel olarak ayarla
     */
    setOffset(offset) {
        this.currentOffset = offset;
    }
} 