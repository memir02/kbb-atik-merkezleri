/**
 * Map Module
 * Leaflet harita işlemlerini yönetir
 */
export class MapModule {
    constructor() {
        this.map = null;
        this.markers = [];
        this.defaultCenter = [37.8746, 32.4932]; // Konya merkezi
        this.defaultZoom = 11;
    }

    /**
     * Harita başlatma fonksiyonu
     */
    initializeMap(containerId = 'map') {
        if (this.map) return this.map; // Zaten başlatılmışsa tekrar başlatma
        
        // Konya merkezli başlat
        this.map = L.map(containerId).setView(this.defaultCenter, this.defaultZoom);
        
        // OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        return this.map;
    }

    /**
     * Markerları temizle
     */
    clearMarkers() {
        if (!this.map) return;
        
        this.markers.forEach(marker => this.map.removeLayer(marker));
        this.markers = [];
    }

    /**
     * Tekil marker ekle
     */
    addMarker(lat, lon, options = {}) {
        if (!this.map || !lat || !lon) return null;

        const {
            popup = '',
            icon = null,
            openPopup = false
        } = options;

        // Varsayılan yeşil atık merkezi ikonu
        const defaultIcon = L.divIcon({
            html: '<i class="fas fa-leaf" style="color:rgb(46, 160, 12); font-size: 40px;"></i>',
            iconSize: [60, 60],
            className: 'custom-div-icon'
        });

        const marker = L.marker([lat, lon], { 
            icon: icon || defaultIcon 
        }).addTo(this.map);
        
        if (popup) {
            marker.bindPopup(popup);
            if (openPopup) {
                marker.openPopup();
            }
        }
        
        this.markers.push(marker);
        return marker;
    }

    /**
     * Kullanıcı konumu marker'ı ekle
     */
    addUserLocationMarker(lat, lon, popup = '') {
        const userIcon = L.divIcon({
            html: '<i class="fas fa-user-circle" style="color:rgb(255, 0, 0); font-size: 40px;"></i>',
            iconSize: [60, 60],
            className: 'custom-div-icon'
        });

        const defaultPopup = `
            <div class="text-center">
                <b class="text-danger"><i class="fas fa-user-circle me-1"></i>Konumunuz</b>
            </div>
        `;

        return this.addMarker(lat, lon, {
            icon: userIcon,
            popup: popup || defaultPopup,
            openPopup: false
        });
    }

    /**
     * Atık merkezi marker'ı ekle
     */
    addAtikMerkeziMarker(merkez) {
        if (!merkez.lat || !merkez.lon) return null;

        let popupContent = `
            <div class="text-center">
                <b class="text-success">${merkez.title}</b><br>
                <small class="text-muted">${merkez.content}</small><br>
                <small><i class="fas fa-map-marker-alt"></i> ${merkez.adres}</small>
        `;
        
        // Mesafe bilgisi varsa ekle
        if (merkez.distance) {
            popupContent += `<br><span class="badge bg-success mt-1">
                <i class="fas fa-route me-1"></i>${parseFloat(merkez.distance).toFixed(1)} km
            </span>`;
        }
        
        popupContent += `</div>`;

        return this.addMarker(merkez.lat, merkez.lon, {
            popup: popupContent
        });
    }

    /**
     * Harita görünümünü ayarla
     */
    setView(lat, lon, zoom = this.defaultZoom) {
        if (!this.map) return;
        this.map.setView([lat, lon], zoom);
    }

    /**
     * Tüm marker'ları görünüme sığdır
     */
    fitBounds(padding = 0.1) {
        if (!this.map || this.markers.length === 0) return;

        if (this.markers.length === 1) {
            // Tek marker varsa ona zoom yap
            const latLng = this.markers[0].getLatLng();
            this.setView(latLng.lat, latLng.lng, 15);
        } else {
            // Birden fazla marker varsa hepsini kapsayacak şekilde zoom yap
            const group = new L.featureGroup(this.markers);
            this.map.fitBounds(group.getBounds().pad(padding));
        }
    }

    /**
     * HARİTA tab'ına geç (Global fonksiyon onclick için)
     */
    switchToMapTab() {
        const haritaTab = document.getElementById('harita-tab');
        if (haritaTab) {
            haritaTab.click();
        }
    }

    /**
     * Modal harita initialize et
     */
    initializeModalMap(modalData) {
        if (!modalData) return;
        
        const { type, userLat, userLon } = modalData;
        
        setTimeout(() => {
            this.initializeMap();
            this.clearMarkers();
            
            // Kullanıcının konumunu ekle (varsa)
            if (userLat && userLon) {
                this.addUserLocationMarker(userLat, userLon);
            }
            
            if (type === 'single' && modalData.merkez) {
                this.handleSingleMerkezModal(modalData.merkez);
            } else if (type === 'multiple' && modalData.merkezler) {
                this.handleMultipleMerkezModal(modalData.merkezler);
            }
            
            // Haritayı uygun şekilde ayarla
            this.fitBounds(userLat && userLon ? 0.2 : 0.1);
        }, 300);
    }

    /**
     * Tek merkez modal haritası
     */
    handleSingleMerkezModal(merkez) {
        if (!merkez.lat || !merkez.lon) {
            alert('Bu merkez için konum bilgisi bulunamadı.');
            return;
        }
        
        const marker = this.addAtikMerkeziMarker(merkez);
        if (marker) {
            marker.openPopup();
        }
    }

    /**
     * Çoklu merkez modal haritası
     */
    handleMultipleMerkezModal(merkezler) {
        const validMerkezler = merkezler.filter(m => m.lat && m.lon);
        
        if (validMerkezler.length === 0) {
            alert('Seçili merkezler için konum bilgisi bulunamadı.');
            return;
        }
        
        validMerkezler.forEach(merkez => {
            this.addAtikMerkeziMarker(merkez);
        });
    }

    /**
     * Haritayı yok et
     */
    destroy() {
        if (this.map) {
            this.clearMarkers();
            this.map.remove();
            this.map = null;
        }
    }
} 