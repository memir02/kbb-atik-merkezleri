/**
 * Geolocation Module
 * GPS konum işlemlerini yönetir
 */
export class GeolocationModule {
    constructor() {
        this.isGettingLocation = false;
    }

    /**
     * Tarayıcı geolocation desteğini kontrol et
     */
    isSupported() {
        return 'geolocation' in navigator;
    }

    /**
     * Koordinatları doğrula
     */
    validateCoordinates(lat, lon) {
        return !isNaN(lat) && !isNaN(lon) && 
               lat >= -90 && lat <= 90 && 
               lon >= -180 && lon <= 180;
    }

    /**
     * Kullanıcının konumunu al
     */
    async getCurrentPosition(options = {}) {
        return new Promise((resolve, reject) => {
            if (!this.isSupported()) {
                reject(new Error('Tarayıcınız konum almayı desteklemiyor.'));
                return;
            }

            const defaultOptions = {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 300000
            };

            const geolocationOptions = { ...defaultOptions, ...options };

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    if (!this.validateCoordinates(lat, lon)) {
                        reject(new Error('Alınan koordinatlar geçersiz.'));
                        return;
                    }
                    
                    resolve({
                        lat: lat,
                        lon: lon,
                        accuracy: position.coords.accuracy,
                        timestamp: position.timestamp
                    });
                },
                (error) => {
                    reject(this.getLocationError(error));
                },
                geolocationOptions
            );
        });
    }

    /**
     * Geolocation hatalarını çevir
     */
    getLocationError(error) {
        let errorMessage = "Konum alınamadı.\n\n";
        let debugInfo = "";
        
        switch(error.code) {
            case error.PERMISSION_DENIED:
                errorMessage += "Konum izni reddedildi.";
                debugInfo = "Çözüm: Tarayıcının adres çubuğundaki konum icon'una tıklayın ve 'İzin Ver' seçin.";
                break;
            case error.POSITION_UNAVAILABLE:
                errorMessage += "Konum bilgisi mevcut değil.";
                debugInfo = "Çözüm: GPS'inizi açın, WiFi/mobil veriye bağlı olun, açık alanda deneyin.";
                break;
            case error.TIMEOUT:
                errorMessage += "Konum alma zaman aşımı (15 saniye).";
                debugInfo = "Çözüm: İnternet bağlantınızı kontrol edin, tekrar deneyin.";
                break;
            default:
                errorMessage += "Bilinmeyen hata oluştu.";
                debugInfo = "Hata kodu: " + error.code;
                break;
        }
        
        return new Error(errorMessage + "\n\n" + debugInfo);
    }

    /**
     * Konum butonunu yönet
     */
    setupLocationButton() {
        const konumBtn = document.getElementById("konuma-gore-ara");
        if (!konumBtn) return;

        konumBtn.addEventListener("click", async () => {
            if (this.isGettingLocation) return;

            this.isGettingLocation = true;
            this.updateButtonState(konumBtn, 'loading');
            
            try {
                const position = await this.getCurrentPosition();
                window.location.href = `/konuma-gore?lat=${position.lat}&lon=${position.lon}`;
            } catch (error) {
                alert(error.message);
                this.updateButtonState(konumBtn, 'error');
            } finally {
                this.isGettingLocation = false;
            }
        });
    }

    /**
     * Buton durumunu güncelle
     */
    updateButtonState(button, state) {
        switch (state) {
            case 'loading':
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Konum Alınıyor...';
                break;
            case 'error':
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-location-arrow me-1"></i> KONUMUMA GÖRE BUL';
                break;
            case 'success':
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-check me-1"></i> Konum Alındı!';
                setTimeout(() => {
                    button.innerHTML = '<i class="fas fa-location-arrow me-1"></i> KONUMUMA GÖRE BUL';
                }, 2000);
                break;
            default:
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-location-arrow me-1"></i> KONUMUMA GÖRE BUL';
        }
    }

    /**
     * İki nokta arasındaki mesafeyi hesapla (Haversine formülü)
     */
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Dünya'nın yarıçapı (km)
        const dLat = this.toRadians(lat2 - lat1);
        const dLon = this.toRadians(lon2 - lon1);
        
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(this.toRadians(lat1)) * Math.cos(this.toRadians(lat2)) * 
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        const distance = R * c;
        
        return Math.round(distance * 100) / 100; // 2 ondalık basamak
    }

    /**
     * Dereceyi radyana çevir
     */
    toRadians(degrees) {
        return degrees * (Math.PI / 180);
    }

    /**
     * Konum izni durumunu kontrol et
     */
    async checkPermissionStatus() {
        if (!navigator.permissions) {
            return 'unknown';
        }

        try {
            const permission = await navigator.permissions.query({name: 'geolocation'});
            return permission.state; // 'granted', 'denied', 'prompt'
        } catch (error) {
            return 'unknown';
        }
    }

    /**
     * Konum izni durumunu izle
     */
    watchPermissionStatus(callback) {
        if (!navigator.permissions) return null;

        navigator.permissions.query({name: 'geolocation'}).then(permission => {
            permission.addEventListener('change', () => {
                callback(permission.state);
            });
        });
    }
} 