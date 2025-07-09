/**
 * Selection Module
 * Merkez seçim işlemlerini yönetir
 */
export class SelectionModule {
    constructor() {
        this.onSelectionChange = null; // Callback fonksiyonu
        this.isEventDelegationSetup = false; // Event delegation kuruldu mu?
        this.updateCountTimeout = null; // Debouncing için
    }

    /**
     * Global event delegation ayarla - dinamik içerik için
     */
    setupGlobalEventDelegation() {
        // Document seviyesinde event delegation
        document.addEventListener('click', (e) => {
            // Card tıklama kontrolü
            const card = e.target.closest('.selectable-card');
            if (card && !this.isElementToIgnore(e.target)) {
                this.handleCardClick(card);
                return;
            }

            // Haritada göster buton kontrolü
            const haritaBtn = e.target.closest('.haritada-goster-btn');
            if (haritaBtn) {
                e.stopPropagation();
                const merkezId = haritaBtn.getAttribute('data-merkez-id');
                if (this.onShowOnMapClick) {
                    this.onShowOnMapClick(merkezId);
                }
                return;
            }
        });

        // Checkbox change event'leri için delegation
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('merkez-checkbox') || 
                e.target.classList.contains('all-merkez-checkbox')) {
                e.stopPropagation();
                const card = e.target.closest('.selectable-card');
                const selectorType = e.target.classList.contains('all-merkez-checkbox') ? 'all' : 'filtered';
                this.updateCardSelection(card, e.target.checked, selectorType);
                this.updateSelectedCount(selectorType);
            }
        });
    }

    /**
     * Göz ardı edilecek elementleri kontrol et
     */
    isElementToIgnore(element) {
        return element.classList.contains('merkez-checkbox') || 
               element.classList.contains('all-merkez-checkbox') ||
               element.classList.contains('haritada-goster-btn') ||
               element.closest('.haritada-goster-btn') ||
               element.closest('.form-check');
    }

    /**
     * Card tıklama işlemi
     */
    handleCardClick(card) {
        // Hangi tip checkbox olduğunu belirle
        let checkbox = card.querySelector('.merkez-checkbox');
        let selectorType = 'filtered';
        
        if (!checkbox) {
            checkbox = card.querySelector('.all-merkez-checkbox');
            selectorType = 'all';
        }

        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            this.updateCardSelection(card, checkbox.checked, selectorType);
            this.updateSelectedCount(selectorType);
        }
    }

    /**
     * Callback fonksiyonu ata
     */
    setSelectionChangeCallback(callback) {
        this.onSelectionChange = callback;
    }

    /**
     * Card seçim durumunu güncelle
     */
    updateCardSelection(card, isSelected, selectorType = 'filtered') {
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

    /**
     * Seçilen merkez sayısını güncelle (debounced)
     */
    updateSelectedCount(selectorType = 'filtered') {
        // Debouncing: Çok hızlı güncellemeleri önle
        clearTimeout(this.updateCountTimeout);
        this.updateCountTimeout = setTimeout(() => {
            this._updateSelectedCountImmediate(selectorType);
        }, 50);
    }

    /**
     * Seçilen merkez sayısını hemen güncelle
     */
    _updateSelectedCountImmediate(selectorType = 'filtered') {
        const isAll = selectorType === 'all';
        const checkboxSelector = isAll ? '.all-merkez-checkbox:checked' : '.merkez-checkbox:checked';
        const allCheckboxSelector = isAll ? '.all-merkez-checkbox' : '.merkez-checkbox';
        const selectedCheckboxes = document.querySelectorAll(checkboxSelector);
        const allCheckboxes = document.querySelectorAll(allCheckboxSelector);
        const count = selectedCheckboxes.length;
        const totalCount = allCheckboxes.length;
        
        // Element referansları
        const selectedCount = document.getElementById(isAll ? 'allSelectedCount' : 'selectedCount');
        const countText = document.getElementById(isAll ? 'allCountText' : 'countText');
        const showSelectedBtn = document.getElementById(isAll ? 'showAllSelectedOnMap' : 'showSelectedOnMap');
        const clearBtn = document.getElementById(isAll ? 'clearAllSelection' : 'clearFilteredSelection');
        const selectAllBtn = document.getElementById(isAll ? 'selectAllMerkezler' : 'selectAllFiltered');
        
        // "Tümünü Seç" butonunun text'ini güncelle
        if (selectAllBtn) {
            const textElement = selectAllBtn.querySelector(isAll ? '.select-all-text' : '.select-text');
            const iconElement = selectAllBtn.querySelector('i');
            
            if (count === totalCount && totalCount > 0) {
                // Hepsi seçili
                if (textElement) textElement.textContent = 'Seçimi Kaldır';
                if (iconElement) {
                    iconElement.className = 'fas fa-minus-square me-1';
                }
                selectAllBtn.className = 'btn btn-outline-warning btn-sm';
            } else {
                // Hiçbiri veya kısmen seçili
                if (textElement) textElement.textContent = 'Tümünü Seç';
                if (iconElement) {
                    iconElement.className = 'fas fa-check-square me-1';
                }
                selectAllBtn.className = 'btn btn-outline-primary btn-sm';
            }
        }
        
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

        // Callback çağır
        if (this.onSelectionChange) {
            this.onSelectionChange(count, selectorType);
        }
    }

    /**
     * Seçili merkez ID'lerini al
     */
    getSelectedMerkezIds(selectorType = 'filtered') {
        const checkboxSelector = selectorType === 'all' ? '.all-merkez-checkbox:checked' : '.merkez-checkbox:checked';
        const selectedCheckboxes = document.querySelectorAll(checkboxSelector);
        return Array.from(selectedCheckboxes).map(checkbox => checkbox.getAttribute('data-merkez-id'));
    }

    /**
     * Seçimi temizle
     */
    clearSelection(selectorType = 'filtered') {
        const checkboxSelector = selectorType === 'all' ? '.all-merkez-checkbox:checked' : '.merkez-checkbox:checked';
        const checkboxes = document.querySelectorAll(checkboxSelector);
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            const card = checkbox.closest('.selectable-card');
            this.updateCardSelection(card, false, selectorType);
        });
        this.updateSelectedCount(selectorType);
    }

    /**
     * Tümünü seç/seçimi kaldır toggle
     */
    toggleSelectAll(selectorType = 'filtered') {
        const isAll = selectorType === 'all';
        const checkboxSelector = isAll ? '.all-merkez-checkbox' : '.merkez-checkbox';
        const checkedSelector = isAll ? '.all-merkez-checkbox:checked' : '.merkez-checkbox:checked';
        
        const allCheckboxes = document.querySelectorAll(checkboxSelector);
        const checkedCheckboxes = document.querySelectorAll(checkedSelector);
        
        // Eğer hepsi seçilmişse, hepsini kaldır; değilse hepsini seç
        const shouldSelectAll = checkedCheckboxes.length < allCheckboxes.length;
        
        allCheckboxes.forEach(checkbox => {
            checkbox.checked = shouldSelectAll;
            const card = checkbox.closest('.selectable-card');
            this.updateCardSelection(card, shouldSelectAll, selectorType);
        });
        
        this.updateSelectedCount(selectorType);
    }

    /**
     * Event listener'ları ekle (Geriye dönük uyumluluk için)
     * Artık global event delegation kullandığımız için bu fonksiyon opsiyonel
     */
    attachEventListeners(container, selectorType = 'filtered') {
        // Global event delegation kullandığımız için bu method artık boş
        // Ama geriye dönük uyumluluk için bırakıldı
    }

    /**
     * Global buton event listener'larını ekle
     */
    attachGlobalEventListeners() {
        // Event delegation'ı ilk kez kuruyoruz
        if (!this.isEventDelegationSetup) {
            this.setupGlobalEventDelegation();
            this.isEventDelegationSetup = true;
        }
        // Filtered selection butonları
        const showSelectedBtn = document.getElementById('showSelectedOnMap');
        const showAllSelectedBtn = document.getElementById('showAllSelectedOnMap');
        const clearFilteredBtn = document.getElementById('clearFilteredSelection');
        const clearAllBtn = document.getElementById('clearAllSelection');
        const selectAllFilteredBtn = document.getElementById('selectAllFiltered');
        const selectAllBtn = document.getElementById('selectAllMerkezler');

        if (showSelectedBtn) {
            showSelectedBtn.addEventListener('click', () => {
                const ids = this.getSelectedMerkezIds('filtered');
                if (this.onShowMultipleOnMap) {
                    this.onShowMultipleOnMap(ids);
                }
            });
        }

        if (showAllSelectedBtn) {
            showAllSelectedBtn.addEventListener('click', () => {
                const ids = this.getSelectedMerkezIds('all');
                if (this.onShowMultipleOnMap) {
                    this.onShowMultipleOnMap(ids);
                }
            });
        }

        if (clearFilteredBtn) {
            clearFilteredBtn.addEventListener('click', () => {
                this.clearSelection('filtered');
            });
        }

        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', () => {
                this.clearSelection('all');
            });
        }

        if (selectAllFilteredBtn) {
            selectAllFilteredBtn.addEventListener('click', () => {
                this.toggleSelectAll('filtered');
            });
        }

        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', () => {
                this.toggleSelectAll('all');
            });
        }
    }

    /**
     * Yeni card'lar için event listener ekle (infinite scroll için)
     * Global event delegation kullandığımız için artık otomatik
     */
    attachEventListenersToNewCards(container) {
        // Global event delegation kullandığımız için yeni card'lar otomatik çalışır
        // Sadece selection count'u güncelle
        setTimeout(() => {
            this.updateSelectedCount('all');
        }, 50);
    }

    /**
     * Callback fonksiyonları ata
     */
    setCallbacks(callbacks) {
        this.onShowOnMapClick = callbacks.onShowOnMapClick || null;
        this.onShowMultipleOnMap = callbacks.onShowMultipleOnMap || null;
    }
} 