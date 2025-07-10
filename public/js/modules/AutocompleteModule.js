/**
 * AutocompleteModule
 * Arama önerileri ve otomatik tamamlama
 */

export class AutocompleteModule {
    constructor(apiClient) {
        console.log('🚀 AutocompleteModule: Initializing...');
        this.apiClient = apiClient;
        this.searchInput = null;
        this.suggestionsList = null;
        this.searchTimeout = null;
        this.isVisible = false;
        this.selectedIndex = -1;
        this.suggestions = [];
        
        // DOM ready olana kadar bekle
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                console.log('📄 DOM ready, setting up autocomplete...');
                this.setupAutocomplete();
            });
        } else {
            console.log('📄 DOM already ready, setting up autocomplete...');
            this.setupAutocomplete();
        }
    }

    /**
     * Autocomplete setup
     */
    setupAutocomplete() {
        this.searchInput = document.querySelector('input[name="search"]');
        if (!this.searchInput) {
            console.error('⚠️ Autocomplete: Search input not found!');
            return;
        }

        console.log('✅ Autocomplete: Search input found, initializing...');
        this.createSuggestionsContainer();
        this.attachEventListeners();
        // Popüler aramaları sayfa açılışında değil, focus'ta yükle
        console.log('✅ Autocomplete: Initialization completed!');
    }

    /**
     * Öneriler container'ı oluştur
     */
    createSuggestionsContainer() {
        // Eğer zaten varsa kaldır
        const existing = document.getElementById('search-suggestions');
        if (existing) existing.remove();

        // Yeni container oluştur
        this.suggestionsList = document.createElement('div');
        this.suggestionsList.id = 'search-suggestions';
        this.suggestionsList.className = 'autocomplete-suggestions';
        
        // Input elementinin pozisyon ve boyutunu al
        const inputRect = this.searchInput.getBoundingClientRect();
        
        this.suggestionsList.style.cssText = `
            position: fixed !important;
            top: ${inputRect.bottom}px !important;
            left: ${inputRect.left}px !important;
            width: ${inputRect.width}px !important;
            background: white !important;
            border: 1px solid #e9ecef !important;
            border-top: none !important;
            border-radius: 0 0 8px 8px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
            max-height: 300px !important;
            overflow-y: auto !important;
            z-index: 99999 !important;
            display: none !important;
            font-family: inherit !important;
            font-size: 14px !important;
        `;

        // Body'ye direkt ekle (parent sorunlarını bypass et)
        document.body.appendChild(this.suggestionsList);
        
        console.log('📦 Container created and added to body');
        console.log('📐 Input position:', inputRect);
        console.log('🎯 Container element:', this.suggestionsList);
    }

    /**
     * Event listener'ları ekle
     */
    attachEventListeners() {
        // Input değişikliği
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            const query = e.target.value.trim();

            if (query.length >= 2) {
                this.searchTimeout = setTimeout(() => {
                    this.loadSuggestions(query);
                }, 300);
            } else if (query.length === 0) {
                // Input boşaldığında popüler aramaları göster
                this.loadPopularSearches();
            } else {
                this.hideSuggestions();
            }
        });

        // Klavye navigasyonu
        this.searchInput.addEventListener('keydown', (e) => {
            this.handleKeyNavigation(e);
        });

        // Focus olayları - popüler aramaları yükle
        this.searchInput.addEventListener('focus', () => {
            const query = this.searchInput.value.trim();
            
            if (query.length === 0) {
                // Input boşsa popüler aramaları yükle
                this.loadPopularSearches();
            } else if (this.suggestions.length > 0) {
                // Input doluysa mevcut önerileri göster
                this.showSuggestions();
            }
        });

        // Document click - dışarı tıklanırsa kapat
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.input-group') && !e.target.closest('#search-suggestions')) {
                this.hideSuggestions();
            }
        });

        // Scroll ve resize'da pozisyonu güncelle
        window.addEventListener('scroll', () => {
            if (this.isVisible) {
                this.updatePosition();
            }
        });
        
        window.addEventListener('resize', () => {
            if (this.isVisible) {
                this.updatePosition();
            }
        });
    }

    /**
     * Klavye navigasyonu
     */
    handleKeyNavigation(e) {
        if (!this.isVisible) return;

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                this.updateSelection();
                break;

            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateSelection();
                break;

            case 'Enter':
                e.preventDefault();
                if (this.selectedIndex >= 0) {
                    this.selectSuggestion(this.suggestions[this.selectedIndex]);
                }
                break;

            case 'Escape':
                this.hideSuggestions();
                break;
        }
    }

    /**
     * Arama önerilerini yükle
     */
    async loadSuggestions(query) {
        try {
            console.log('🔍 Loading suggestions for:', query);
            const suggestions = await this.apiClient.searchSuggestions(query, 5);
            console.log('📥 Received suggestions:', suggestions);
            this.suggestions = suggestions.map(text => ({ text, type: 'suggestion' }));
            this.displaySuggestions();
        } catch (error) {
            console.error('❌ Öneri yükleme hatası:', error);
        }
    }

    /**
     * Popüler aramaları yükle
     */
    async loadPopularSearches() {
        try {
            console.log('🔥 Loading popular searches...');
            const popular = await this.apiClient.popularSearches();
            this.suggestions = popular.map(text => ({ text, type: 'popular' }));
            
            // Sadece input boşsa göster
            if (this.searchInput.value.trim() === '') {
                console.log('📥 Displaying popular searches:', this.suggestions.length);
                this.displaySuggestions();
            }
        } catch (error) {
            console.error('❌ Popüler arama yükleme hatası:', error);
        }
    }

    /**
     * Önerileri görüntüle
     */
    displaySuggestions() {
        console.log('🎨 Displaying suggestions:', this.suggestions.length, 'items');
        
        if (this.suggestions.length === 0) {
            console.log('📭 No suggestions to display');
            this.hideSuggestions();
            return;
        }

        let html = '';
        
        // Başlık ekle (opsiyonel)
        if (this.suggestions[0].type === 'popular') {
            html += `
                <div class="suggestion-header" style="
                    padding: 8px 16px;
                    font-size: 12px;
                    font-weight: 600;
                    color: #6c757d;
                    background: #f8f9fa;
                    border-bottom: 1px solid #e9ecef;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                ">Popüler Aramalar</div>
            `;
        }

        // Önerileri listele - ikon yok
        this.suggestions.forEach((suggestion, index) => {
            html += `
                <div class="suggestion-item" data-index="${index}" style="
                    padding: 12px 16px;
                    cursor: pointer;
                    border-bottom: 1px solid #f1f3f4;
                    transition: background-color 0.2s ease;
                    font-size: 14px;
                    color: #495057;
                    line-height: 1.4;
                " onmouseover="this.style.backgroundColor='#f8f9fa'" 
                  onmouseout="this.style.backgroundColor='transparent'">
                    ${this.highlightMatch(suggestion.text)}
                </div>
            `;
        });

        this.suggestionsList.innerHTML = html;
        this.addSuggestionClickListeners();
        this.showSuggestions();
        console.log('✅ Suggestions displayed and visible');
    }

    /**
     * Öneri tıklama listener'ları ekle
     */
    addSuggestionClickListeners() {
        const items = this.suggestionsList.querySelectorAll('.suggestion-item');
        items.forEach((item, index) => {
            item.addEventListener('click', () => {
                this.selectSuggestion(this.suggestions[index]);
            });

            item.addEventListener('mouseenter', () => {
                this.selectedIndex = index;
                this.updateSelection();
            });
        });
    }

    /**
     * Arama metnini vurgula
     */
    highlightMatch(text) {
        const query = this.searchInput.value.toLowerCase();
        if (!query || this.suggestions[0].type === 'popular') {
            return text;
        }

        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }

    /**
     * Seçimi güncelle
     */
    updateSelection() {
        const items = this.suggestionsList.querySelectorAll('.suggestion-item');
        items.forEach((item, index) => {
            if (index === this.selectedIndex) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });
    }

    /**
     * Öneriyi seç
     */
    selectSuggestion(suggestion) {
        this.searchInput.value = suggestion.text;
        this.hideSuggestions();
        
        // Arama formunu submit et
        const form = this.searchInput.closest('form');
        if (form) {
            form.submit();
        }
    }

    /**
     * Önerileri göster
     */
    showSuggestions() {
        console.log('👀 Showing suggestions dropdown');
        
        // Pozisyonu güncelle
        this.updatePosition();
        
        // Göster
        this.suggestionsList.style.display = 'block';
        this.isVisible = true;
        this.selectedIndex = -1;
        
        // Container'ın pozisyonunu kontrol et
        const rect = this.suggestionsList.getBoundingClientRect();
        console.log('📐 Suggestions container position:', rect);
        console.log('🎯 Container HTML:', this.suggestionsList.innerHTML);
        console.log('🎯 Container visible?', this.suggestionsList.offsetHeight > 0);
        console.log('🎯 In viewport?', rect.top >= 0 && rect.bottom <= window.innerHeight);
    }

    /**
     * Önerileri gizle
     */
    hideSuggestions() {
        this.suggestionsList.style.display = 'none';
        this.isVisible = false;
        this.selectedIndex = -1;
    }

    /**
     * Container pozisyonunu güncelle
     */
    updatePosition() {
        // Input elementinin pozisyon ve boyutunu al
        const inputRect = this.searchInput.getBoundingClientRect();
        
        this.suggestionsList.style.top = `${inputRect.bottom}px`;
        this.suggestionsList.style.left = `${inputRect.left}px`;
        this.suggestionsList.style.width = `${inputRect.width}px`;
        console.log('🔄 Position updated:', inputRect);
    }
}

// CSS stilleri ekle
const autocompleteStyles = `
    .autocomplete-suggestions {
        position: absolute !important;
        background: white !important;
        border: 2px solid #007bff !important;
        z-index: 9999 !important;
        width: 100% !important;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
    }

    .suggestion-header {
        padding: 8px 12px !important;
        font-size: 12px !important;
        font-weight: bold !important;
        color: #666 !important;
        background: #f8f9fa !important;
        border-bottom: 1px solid #eee !important;
        text-transform: uppercase !important;
    }

    .suggestion-item {
        padding: 8px 12px !important;
        cursor: pointer !important;
        border-bottom: 1px solid #f0f0f0 !important;
        display: flex !important;
        align-items: center !important;
        transition: background-color 0.2s !important;
        color: #333 !important;
        background: white !important;
    }

    .suggestion-item:hover,
    .suggestion-item.selected {
        background: #007bff !important;
        color: white !important;
    }

    .suggestion-icon {
        margin-right: 8px !important;
        font-size: 14px !important;
    }

    .suggestion-text {
        flex: 1 !important;
    }

    .suggestion-text strong {
        background: #fff3cd !important;
        padding: 1px 2px !important;
        border-radius: 2px !important;
        color: #333 !important;
    }

    .suggestion-item:hover .suggestion-text strong,
    .suggestion-item.selected .suggestion-text strong {
        background: rgba(255,255,255,0.8) !important;
        color: #333 !important;
    }
`;

// Stilleri head'e ekle
if (!document.getElementById('autocomplete-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'autocomplete-styles';
    styleSheet.textContent = autocompleteStyles;
    document.head.appendChild(styleSheet);
} 