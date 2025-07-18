/**
 * Atık Merkezleri - Ana Koordinatör
 * Tüm modülleri bir araya getirir ve uygulamayı başlatır
 */

// Browser compatibility check
if (!window.customElements || !window.fetch || !window.Promise) {
    alert('Tarayıcınız güncel değil. Bu uygulamayı çalıştırmak için lütfen tarayıcınızı güncelleyin.');
}

// Modülleri import et
import { ApiClient } from './modules/ApiClient.js';
import { MapModule } from './modules/MapModule.js';
import { SelectionModule } from './modules/SelectionModule.js';
import { ModalModule } from './modules/ModalModule.js';
import { GeolocationModule } from './modules/GeolocationModule.js';
import { InfiniteScrollModule } from './modules/InfiniteScrollModule.js';
import { AutocompleteModule } from './modules/AutocompleteModule.js';
import { RatingModule } from './modules/RatingModule.js';

/**
 * Ana uygulama sınıfı
 */
class AtikMerkezleriApp {
    constructor() {
        this.modules = {};
        this.isInitialized = false;
    }

    /**
     * Uygulamayı başlat
     */
    async init() {
        if (this.isInitialized) return;

        try {
            // Modülleri oluştur
            this.createModules();
            
            // Modül bağımlılıklarını ayarla
            this.setupModuleDependencies();
            
            // Event listener'ları ayarla
            this.setupEventListeners();
            
            // Content filter'ını ayarla
            this.setupContentFilter();
            
            // Global erişilebilirlik için window'a ekle
            this.setupGlobalAccess();
            
            this.isInitialized = true;
            // Uygulama başarıyla başlatıldı
            
        } catch (error) {
            console.error('Uygulama başlatılırken hata oluştu:', error);
        }
    }

    /**
     * Modülleri oluştur
     */
    createModules() {
        this.modules.api = new ApiClient();
        this.modules.map = new MapModule();
        this.modules.selection = new SelectionModule();
        this.modules.modal = new ModalModule(this.modules.api);
        this.modules.geolocation = new GeolocationModule();
        this.modules.infiniteScroll = new InfiniteScrollModule(this.modules.api, this.modules.selection);
        this.modules.autocomplete = new AutocompleteModule(this.modules.api);
    }

    /**
     * Modül bağımlılıklarını ayarla
     */
    setupModuleDependencies() {
        // SelectionModule callback'lerini ayarla
        this.modules.selection.setCallbacks({
            onShowOnMapClick: (merkezId) => {
                this.modules.modal.showSingleMerkezModal(merkezId);
            },
            onShowMultipleOnMap: (merkezIds) => {
                this.modules.modal.showMultipleMerkezModal(merkezIds);
            }
        });

        // ModalModule'de harita tab'ına geçiş callback'i
        this.modules.modal.switchToMapTab = () => {
            const haritaTab = document.getElementById('harita-tab');
            if (haritaTab) {
                haritaTab.click();
                
                // Harita tab'ı aktif olduğunda modal haritasını başlat
                setTimeout(() => {
                    const modalData = this.modules.modal.getCurrentModalData();
                    if (modalData) {
                        this.modules.map.initializeModalMap(modalData);
                    }
                }, 100);
            }
        };
    }

    /**
     * Event listener'ları ayarla
     */
    setupEventListeners() {
        // DOM yüklendiğinde çalışacak event listener'lar
        document.addEventListener('DOMContentLoaded', () => {
            this.setupDOMEventListeners();
        });

        // Harita tab'ı click event'i
        const haritaTab = document.getElementById('harita-tab');
        if (haritaTab) {
            haritaTab.addEventListener('click', () => {
                setTimeout(() => {
                    const modalData = this.modules.modal.getCurrentModalData();
                    if (modalData) {
                        this.modules.map.initializeModalMap(modalData);
                    }
                }, 100);
            });
        }

        // Yorumlar tab'ı click event'i
        const yorumTab = document.getElementById('yorum-tab');
        if (yorumTab) {
            yorumTab.addEventListener('click', () => {
                setTimeout(() => {
                    this.modules.modal.loadCommentsToTab();
                }, 100);
            });
        }
    }

    /**
     * DOM event listener'ları ayarla
     */
    setupDOMEventListeners() {
        // Selection modül global event listener'larını ayarla
        this.modules.selection.attachGlobalEventListeners();

        // Geolocation butonunu ayarla
        this.modules.geolocation.setupLocationButton();
        
        // Infinite scroll'u ayarla
        this.modules.infiniteScroll.setupLoadMoreButton();
        this.modules.infiniteScroll.setupScrollInfiniteLoading();
        
        // Modal event listener'larını ayarla
        this.modules.modal.setupModalEventListeners();
        
        // İlk sayfa yüklendiğinde selection count'ları güncelle
        setTimeout(() => {
            this.modules.selection.updateSelectedCount('filtered');
            this.modules.selection.updateSelectedCount('all');
        }, 100);
    }

    /**
     * Content filter'ını ayarla
     */
    setupContentFilter() {
        const contentFilter = document.getElementById('contentFilter');
        if (contentFilter) {
            contentFilter.addEventListener('change', (e) => {
                const value = e.target.value;
                const merkezler = document.querySelectorAll('.merkez');
                
                merkezler.forEach((merkez) => {
                    if (!value || merkez.getAttribute('data-content').includes(value)) {
                        merkez.style.display = '';
                    } else {
                        merkez.style.display = 'none';
                    }
                });
                
                // Filtreleme sonrası selection count'u güncelle
                setTimeout(() => {
                    this.modules.selection.updateSelectedCount('filtered');
                }, 50);
            });
        }
    }

    /**
     * Global erişilebilirlik için window'a ekle
     */
    setupGlobalAccess() {
        // Geriye dönük uyumluluk için global fonksiyonlar
        window.switchToMapTab = () => this.modules.map.switchToMapTab();
        window.showOnMap = (merkezId) => this.modules.modal.showSingleMerkezModal(merkezId);
        window.showMultipleOnMap = (merkezIds) => this.modules.modal.showMultipleMerkezModal(merkezIds);
        window.loadMoreMerkezler = () => this.modules.infiniteScroll.loadMoreMerkezler();
        
        // Modüllere erişim için
        window.apiClient = this.modules.api;
        window.mapModule = this.modules.map;
        window.selectionModule = this.modules.selection;
        window.modalModule = this.modules.modal;
        window.geolocationModule = this.modules.geolocation;
        window.infiniteScrollModule = this.modules.infiniteScroll;
        window.autocompleteModule = this.modules.autocomplete;
        // window.ratingModule = this.modules.rating; // Disabled - using inline rating system
        
        // Ana app referansı
        window.atikMerkezleriApp = this;
    }

    /**
     * Modül referanslarını al
     */
    getModules() {
        return this.modules;
    }

    /**
     * Belirli bir modülü al
     */
    getModule(moduleName) {
        return this.modules[moduleName];
    }

    /**
     * Uygulamayı yeniden başlat
     */
    restart() {
        this.isInitialized = false;
        this.init();
    }

    /**
     * Debug bilgisi
     */
    getDebugInfo() {
        return {
            isInitialized: this.isInitialized,
            modules: Object.keys(this.modules),
            selectionStatus: this.modules.selection ? {
                filteredCount: this.modules.selection.getSelectedMerkezIds('filtered').length,
                allCount: this.modules.selection.getSelectedMerkezIds('all').length
            } : null,
            infiniteScrollStatus: this.modules.infiniteScroll ? this.modules.infiniteScroll.getStatus() : null
        };
    }
}

// Uygulamayı başlat
const app = new AtikMerkezleriApp();

// DOM yüklendikten sonra uygulamayı başlat
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => app.init());
} else {
    app.init();
}

// Export for ES6 modules
export default app;