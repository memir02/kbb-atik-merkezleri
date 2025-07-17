<!-- JavaScript Bölümü -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- User Authentication Status for JavaScript -->
<div id="auth-data" 
     data-logged-in="{{ auth()->check() ? 'true' : 'false' }}"
     data-csrf-token="{{ csrf_token() }}"
     style="display: none;">
</div>

<script>
    // Get auth data from HTML attributes
    const authData = document.getElementById('auth-data');
    window.userLoggedIn = authData.dataset.loggedIn === 'true';
    window.authToken = authData.dataset.csrfToken;
    
    // DIREKT RATING SYSTEM - ES6 modül olmadan
    document.addEventListener('DOMContentLoaded', function() {
        // Rating button click events
        document.addEventListener('click', function(e) {
            const rateBtn = e.target.closest('.rate-btn');
            if (rateBtn) {
                e.preventDefault();
                e.stopPropagation();
                
                const merkezId = rateBtn.dataset.merkezId;
                
                if (!window.userLoggedIn) {
                    showBootstrapAlert('Puanlama yapmak için giriş yapmalısınız!', 'warning');
                    return;
                }
                
                showRatingModal(merkezId);
            }
        });
        // ALTERNATIF: Direkt button'lara listener ekle
        setTimeout(() => {
            const rateButtons = document.querySelectorAll('.rate-btn');
            
            rateButtons.forEach((btn, index) => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (!window.userLoggedIn) {
                        showBootstrapAlert('Puanlama yapmak için giriş yapmalısınız!', 'warning');
                        return;
                    }
                    
                    showRatingModal(this.dataset.merkezId);
                });
            });

            // FAVORİ BUTONLARI İÇİN DE EKLE
            const favoriteButtons = document.querySelectorAll('.favorite-btn');
            favoriteButtons.forEach((btn) => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!window.userLoggedIn) {
                        showBootstrapAlert('Favorilere eklemek için giriş yapmalısınız!', 'warning');
                        return;
                    }
                    toggleFavorite(this.dataset.merkezId, this);
                });
            });
        }, 1000);
    });
    
    // Rating modal fonksiyonu - TEMİZ VE BACKDROP-SAFE
    function showRatingModal(merkezId) {
        // Önce mevcut modal'ları ve backdrop'ları temizle
        cleanupModals();
        
        // Modal HTML oluştur
        const modalHtml = `
            <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ratingModalLabel">
                                <i class="fas fa-star text-warning me-2"></i>Merkezi Puanla
                            </h5>
                            <button type="button" class="btn-close" onclick="closeRatingModal()" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <h6 class="mb-3">Bu merkezi nasıl değerlendirirsiniz?</h6>
                                <div class="rating-stars my-3" id="modalRatingStars">
                                    <i class="fas fa-star rating-star text-muted me-1" data-rating="1" style="cursor: pointer; font-size: 2rem;"></i>
                                    <i class="fas fa-star rating-star text-muted me-1" data-rating="2" style="cursor: pointer; font-size: 2rem;"></i>
                                    <i class="fas fa-star rating-star text-muted me-1" data-rating="3" style="cursor: pointer; font-size: 2rem;"></i>
                                    <i class="fas fa-star rating-star text-muted me-1" data-rating="4" style="cursor: pointer; font-size: 2rem;"></i>
                                    <i class="fas fa-star rating-star text-muted me-1" data-rating="5" style="cursor: pointer; font-size: 2rem;"></i>
                                </div>
                                <small class="text-muted">Yıldızlara tıklayarak puanlayın</small>
                            </div>
                            <div class="mb-3">
                                <label for="ratingComment" class="form-label">
                                    <i class="fas fa-comment me-1"></i>Yorumunuz (isteğe bağlı)
                                </label>
                                <textarea class="form-control" id="ratingComment" rows="3" 
                                    placeholder="Merkezle ilgili deneyiminizi paylaşın..."></textarea>
                            </div>
                            <input type="hidden" id="selectedRating" value="0">
                            <input type="hidden" id="ratingMerkezId" value="${merkezId}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeRatingModal()">
                                <i class="fas fa-times me-1"></i>İptal
                            </button>
                            <button type="button" class="btn btn-primary" id="submitRating">
                                <i class="fas fa-paper-plane me-1"></i>Puanla
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // DOM'a ekle
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Event listener'ları ekle
        setupRatingModalEvents(merkezId);
        
        // Modal'ı göster - sadece Bootstrap kullan
        const modalElement = document.getElementById('ratingModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true
        });
        
        // Modal kapatma event listener ekle
        modalElement.addEventListener('hidden.bs.modal', cleanupModals);
        
        modal.show();
    }
    
    // Modal event listener'larını kurma
    function setupRatingModalEvents(merkezId) {
        // Star click events
        document.querySelectorAll('#modalRatingStars .rating-star').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                document.getElementById('selectedRating').value = rating;
                
                // Yıldızları güncelle
                document.querySelectorAll('#modalRatingStars .rating-star').forEach((s, index) => {
                    if (index < rating) {
                        s.classList.remove('text-muted');
                        s.classList.add('text-warning');
                    } else {
                        s.classList.remove('text-warning');
                        s.classList.add('text-muted');
                    }
                });
            });
        });
        
        // Submit button event
        document.getElementById('submitRating').addEventListener('click', function() {
            const rating = document.getElementById('selectedRating').value;
            const comment = document.getElementById('ratingComment').value;
            
            if (!rating || rating < 1) {
                showBootstrapAlert('Lütfen bir puan seçin!', 'warning');
                return;
            }
            
            submitRating(merkezId, rating, comment);
        });
    }
    
    // Modal'ı güvenle kapat
    function closeRatingModal() {
        const modalElement = document.getElementById('ratingModal');
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
        // Her durumda cleanup yap
        setTimeout(cleanupModals, 300);
    }
    
    // Tüm modal ve backdrop'ları temizle
    function cleanupModals() {
        // Mevcut rating modal'ı kaldır
        const existingModal = document.getElementById('ratingModal');
        if (existingModal) {
            const modal = bootstrap.Modal.getInstance(existingModal);
            if (modal) {
                modal.dispose();
            }
            existingModal.remove();
        }
        
        // Tüm backdrop'ları kaldır
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
            backdrop.remove();
        });
        
        // Body'den modal sınıflarını kaldır
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }
    
    // Rating submit fonksiyonu
    function submitRating(merkezId, rating, comment) {
        fetch('/api/ratings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.authToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                atik_merkezi_id: merkezId,
                rating: rating,
                comment: comment
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.average_rating !== undefined) {
                showBootstrapAlert('Puanınız başarıyla kaydedildi!', 'success');
                
                // Modal'ı güvenle kapat
                closeRatingModal();
                
                // Sayfayı yenile (rating'leri güncellemek için)
                setTimeout(() => location.reload(), 1000);
            } else {
                showBootstrapAlert('Hata: ' + (data.message || 'Bilinmeyen hata'), 'danger');
            }
        })
        .catch(error => {
            console.error('Rating submit error:', error);
            showBootstrapAlert('Bir hata oluştu!', 'danger');
        });
    }

    // Bootstrap alert helper
    function showBootstrapAlert(message, type = 'success') {
        // Remove any existing alert
        const existingAlert = document.getElementById('custom-bootstrap-alert');
        if (existingAlert) existingAlert.remove();

        // Create alert div
        const alertDiv = document.createElement('div');
        alertDiv.id = 'custom-bootstrap-alert';
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            <span>${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);
        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            if (alertDiv) {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 3000);
    }

    // Modal form submission fix - Button click approach
    document.addEventListener('DOMContentLoaded', function() {
        const filterSubmitBtn = document.getElementById('filterSubmitBtn');
        const filterForm = document.querySelector('#filtreModal form');
        const modal = document.querySelector('#filtreModal');
        
        if (filterSubmitBtn && filterForm) {
            filterSubmitBtn.addEventListener('click', function(e) {
                // Checked checkbox'ları topla
                const checkboxes = filterForm.querySelectorAll('input[name="filter[]"]:checked');
                const checkedFilters = Array.from(checkboxes).map(cb => cb.value);
                
                // Eğer hiç filtre seçilmemişse uyarı ver
                if (checkedFilters.length === 0) {
                    showBootstrapAlert('Lütfen en az bir filtre seçin!', 'warning');
                    return false;
                }
                
                // URL'i manuel olarak oluştur
                const url = new URL(window.location.origin + '/');
                checkedFilters.forEach(filter => {
                    url.searchParams.append('filter[]', filter);
                });
                
                // Modal'ı kapat
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
                
                // Direkt yönlendir
                window.location.href = url.toString();
            });
        } else {
            console.error('Filter button or form not found!');
        }
    });

    // Favorite button click events
    document.addEventListener('click', function(e) {
        const favoriteBtn = e.target.closest('.favorite-btn');
        if (favoriteBtn) {
            e.preventDefault();
            e.stopPropagation();

            const merkezId = favoriteBtn.dataset.merkezId;

            if (!window.userLoggedIn) {
                showBootstrapAlert('Favorilere eklemek için giriş yapmalısınız!', 'warning');
                return;
            }

            toggleFavorite(merkezId, favoriteBtn);
        }
    });

    // Favori ekle/çıkar fonksiyonu
    function toggleFavorite(merkezId, button) {
        fetch('/api/favorites/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.authToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                atik_merkezi_id: merkezId
            })
        })
        .then(response => response.json())
        .then(data => {
            const icon = button.querySelector('i');
            if (data.is_favorite) {
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-danger');
                showBootstrapAlert('Favorilere eklendi!', 'success');
            } else {
                icon.classList.remove('fas', 'text-danger');
                icon.classList.add('far');
                showBootstrapAlert('Favorilerden çıkarıldı!', 'danger');
            }
        })
        .catch(error => {
            console.error('Favori işlemi hatası:', error);
            showBootstrapAlert('Bir hata oluştu!', 'danger');
        });
    }
</script>

<!-- ES6 Modules Re-enabled - RatingModule disabled in code -->
<script type="module" src="{{ asset('js/atik-merkezleri.js') }}"></script>