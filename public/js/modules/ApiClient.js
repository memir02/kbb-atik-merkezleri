/**
 * API Client Module
 * Tüm backend API çağrılarını yönetir
 */
export class ApiClient {
    constructor() {
        this.baseUrl = '/api/atik-merkezleri';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    /**
     * Gelişmiş error handling
     */
    handleApiError(error, context = '') {
        let userMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
        
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            userMessage = 'İnternet bağlantınızı kontrol edin.';
        } else if (error.message.includes('404')) {
            userMessage = 'İstenen veri bulunamadı.';
        } else if (error.message.includes('500')) {
            userMessage = 'Sunucu hatası. Lütfen daha sonra tekrar deneyin.';
        } else if (error.message.includes('timeout')) {
            userMessage = 'İstek zaman aşımına uğradı. Tekrar deneyin.';
        }
        
        console.error(`API Error (${context}):`, error);
        return new Error(userMessage);
    }

    /**
     * Tek merkez bilgisi getir
     */
    async getMerkez(id) {
        try {
            const response = await fetch(`${this.baseUrl}/${id}`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: Merkez bilgisi alınamadı`);
            }
            const data = await response.json();
            return data.data || data; // Yeni API formatını destekle
        } catch (error) {
            throw this.handleApiError(error, 'getMerkez');
        }
    }

    /**
     * Birden fazla merkez bilgisi getir
     */
    async getMultipleMerkezler(ids) {
        try {
            const response = await fetch(`${this.baseUrl}/multiple`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ ids })
            });
            
            if (!response.ok) {
                throw new Error('Merkez bilgileri alınamadı');
            }
            
            const data = await response.json();
            return data.data || data; // Yeni API formatını destekle
        } catch (error) {
            console.error('API Error (getMultipleMerkezler):', error);
            throw error;
        }
    }

    /**
     * Infinite scroll için daha fazla merkez getir
     */
    async loadMoreMerkezler(offset = 0, limit = 20) {
        try {
            const response = await fetch(`${this.baseUrl}/load-more?offset=${offset}&limit=${limit}`);
            if (!response.ok) {
                throw new Error('Veriler yüklenemedi');
            }
            const responseData = await response.json();
            return responseData.data || responseData; // Yeni API formatını destekle
        } catch (error) {
            console.error('API Error (loadMoreMerkezler):', error);
            throw error;
        }
    }

    /**
     * Konum bazlı en yakın merkezleri getir
     */
    async getNearestMerkezler(lat, lon, limit = 10) {
        try {
            const response = await fetch(`${this.baseUrl}/nearest`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ lat, lon, limit })
            });
            
            if (!response.ok) {
                throw new Error('En yakın merkezler alınamadı');
            }
            
            const data = await response.json();
            return data.data || data;
        } catch (error) {
            console.error('API Error (getNearestMerkezler):', error);
            throw error;
        }
    }

    /**
     * Arama önerileri API
     */
    async searchSuggestions(query, limit = 5) {
        try {
            const params = new URLSearchParams();
            params.append('q', query);
            params.append('limit', limit);

            const response = await fetch(`${this.baseUrl}/search/suggestions?${params}`);
            if (!response.ok) {
                throw new Error('Öneri alma başarısız');
            }
            
            const data = await response.json();
            return data.data || [];
        } catch (error) {
            console.error('API Error (suggestions):', error);
            return [];
        }
    }

    /**
     * Popüler aramalar API
     */
    async popularSearches() {
        try {
            const response = await fetch(`${this.baseUrl}/search/popular`);
            if (!response.ok) {
                throw new Error('Popüler aramalar alma başarısız');
            }
            
            const data = await response.json();
            return data.data || [];
        } catch (error) {
            console.error('API Error (popular searches):', error);
            return [];
        }
    }

    /**
     * Arama API
     */
    async search(searchTerm, filters = []) {
        try {
            const params = new URLSearchParams();
            if (searchTerm) params.append('q', searchTerm);
            if (filters.length > 0) {
                filters.forEach(filter => params.append('filters[]', filter));
            }

            const response = await fetch(`${this.baseUrl}/search?${params}`);
            if (!response.ok) {
                throw new Error('Arama başarısız');
            }
            
            const data = await response.json();
            return data.data || data;
        } catch (error) {
            console.error('API Error (search):', error);
            throw error;
        }
    }
} 