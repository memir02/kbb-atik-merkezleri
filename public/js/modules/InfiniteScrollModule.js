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
     * Yeni merkezleri DOM'a ekle
     */
    appendMerkezlerToDOM(merkezler) {
        const container = document.getElementById('allMerkezlerContainer');
        if (!container) return;

        merkezler.forEach(merkez => {
            const colDiv = document.createElement('div');
            colDiv.className = 'col';
            colDiv.innerHTML = this.createMerkezCardHTML(merkez);
            container.appendChild(colDiv);
        });

        // Yeni card'lar için event listener ekle
        if (this.selectionModule) {
            this.selectionModule.attachEventListenersToNewCards(container);
        }
    }

    /**
     * Merkez card HTML'i oluştur
     */
    createMerkezCardHTML(merkez) {
        const borderClass = merkez.border_class || 'border-secondary';
        return `
            <div class="card ${borderClass} h-100 selectable-card position-relative" data-merkez-id="${merkez.id}" style="cursor: pointer; transition: all 0.3s ease;">
                <div class="card-body">
                    <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                        <input class="form-check-input all-merkez-checkbox" type="checkbox" id="all-merkez-${merkez.id}" data-merkez-id="${merkez.id}">
                    </div>
                    <h5 class="card-title pe-5">${merkez.title}</h5>
                    <p class="card-text">${merkez.content}</p>
                    <small class="text-muted">Adres: ${merkez.adres || 'Belirtilmemiş'}</small>
                </div>
                <div class="map-button-container position-absolute" style="bottom: 10px; right: 10px; display: none;">
                    <button class="btn btn-success btn-sm haritada-goster-btn" data-merkez-id="${merkez.id}">
                        <i class="fas fa-info-circle me-1"></i> Detay Görüntüle-Haritada Göster
                    </button>
                </div>
            </div>
        `;
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