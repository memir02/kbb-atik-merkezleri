class RatingModule {
    constructor() {
        this.isInitialized = false;
        this.userLoggedIn = this.checkUserAuthentication();
        this.init();
    }

    checkUserAuthentication() {
        // Check if user is logged in (can be set from blade template)
        return window.userLoggedIn || false;
    }

    init() {
        if (this.isInitialized) return;
        
        // DOM hazır olana kadar bekle
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.setupModule();
            });
        } else {
            this.setupModule();
        }
    }

    setupModule() {
        this.bindEvents();
        this.initializeRatingDisplays();
        this.loadUserFavorites();
        this.isInitialized = true;
        

        
        console.log('RatingModule initialized');
    }

    /**
     * Initialize all rating displays on page load
     */
    initializeRatingDisplays() {
        // Find all rating widgets and set their star colors
        const ratingWidgets = document.querySelectorAll('.rating-widget');
        
        ratingWidgets.forEach(widget => {
            const averageSpan = widget.querySelector('.average-rating');
            const totalSpan = widget.querySelector('.total-ratings');
            const starsContainer = widget.querySelector('.stars-display');
            
            if (averageSpan && totalSpan && starsContainer) {
                const average = parseFloat(averageSpan.textContent) || 0;
                const total = parseInt(totalSpan.textContent) || 0;
                
                // Update star colors based on average rating
                this.updateStarColors(starsContainer, average);
            }
        });
    }

    /**
     * Update star colors based on rating
     */
    updateStarColors(starsContainer, rating) {
        const stars = starsContainer.querySelectorAll('.star-icon');
        const fullStars = Math.floor(rating);
        const hasHalfStar = (rating % 1) >= 0.5;
        
        stars.forEach((star, index) => {
            // Reset all classes
            star.className = 'fas fa-star star-icon';
            star.style.cursor = 'pointer';
            
            if (index < fullStars) {
                // Full star - yellow
                star.style.color = '#ffc107';
            } else if (index === fullStars && hasHalfStar) {
                // Half star - yellow
                star.className = 'fas fa-star-half-alt star-icon';
                star.style.color = '#ffc107';
            } else {
                // Empty star - gray
                star.className = 'far fa-star star-icon';
                star.style.color = '#ddd';
            }
        });
    }

    bindEvents() {
        // Rating button click events
        document.addEventListener('click', (e) => {
            const rateBtn = e.target && e.target.closest ? e.target.closest('.rate-btn') : null;
            if (rateBtn) {
                e.preventDefault();
                e.stopPropagation();
                const merkezId = rateBtn.dataset.merkezId;
                this.showRatingModal(merkezId);
            }
        });

        // Favorite button click events
        document.addEventListener('click', (e) => {
            const favoriteBtn = e.target && e.target.closest ? e.target.closest('.favorite-btn') : null;
            if (favoriteBtn) {
                e.preventDefault();
                e.stopPropagation();
                const merkezId = favoriteBtn.dataset.merkezId;
                this.toggleFavorite(merkezId, favoriteBtn);
            }
        });

        // Rating modal events
        document.addEventListener('click', (e) => {
            const submitBtn = e.target && e.target.closest ? e.target.closest('#submitRating') : null;
            if (submitBtn) {
                e.preventDefault();
                this.submitRating();
            }
        });

        // Star rating events
        document.addEventListener('mouseover', (e) => {
            const star = e.target && e.target.closest ? e.target.closest('.rating-star') : null;
            if (star) {
                this.highlightStars(star);
            }
        });

        document.addEventListener('click', (e) => {
            const star = e.target && e.target.closest ? e.target.closest('.rating-star') : null;
            if (star) {
                this.selectRating(star);
            }
        });

        // Reset stars on mouse leave
        document.addEventListener('mouseleave', (e) => {
            const starsContainer = e.target && e.target.closest ? e.target.closest('.rating-stars') : null;
            if (starsContainer) {
                this.resetStarHighlight();
            }
        });
    }

    async showRatingModal(merkezId) {
        if (!this.userLoggedIn) {
            this.showToast('Puanlama yapmak için giriş yapmalısınız!', 'warning');
            return;
        }

        try {
            // Get current rating if exists
            const response = await fetch(`/api/ratings/${merkezId}/user-rating`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin' // Include session cookies
            });

            let currentRating = null;
            if (response.ok) {
                const data = await response.json();
                currentRating = data.rating;
            }

            this.createRatingModal(merkezId, currentRating);
        } catch (error) {
            console.error('Error fetching current rating:', error);
            this.createRatingModal(merkezId, null);
        }
    }

    createRatingModal(merkezId, currentRating = null) {
        // Remove existing modal if any
        const existingModal = document.getElementById('ratingModal');
        if (existingModal) {
            existingModal.remove();
        }

        const currentRatingValue = currentRating?.rating || 0;
        const currentComment = currentRating?.comment || '';

        const modalHtml = `
            <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ratingModalLabel">
                                <i class="fas fa-star text-warning me-2"></i>Merkezi Puanla
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <h6 class="mb-3">Bu merkezi nasıl değerlendirirsiniz?</h6>
                                <div class="rating-stars my-3" data-merkez-id="${merkezId}">
                                    ${this.generateInteractiveStars()}
                                </div>
                                <small class="text-muted">Yıldızlara tıklayarak puanlayın</small>
                            </div>
                            <div class="mb-3">
                                <label for="ratingComment" class="form-label">
                                    <i class="fas fa-comment me-1"></i>Yorumunuz (isteğe bağlı)
                                </label>
                                <textarea class="form-control" id="ratingComment" rows="3" 
                                    placeholder="Merkezle ilgili deneyiminizi paylaşın...">${currentComment}</textarea>
                            </div>
                            <input type="hidden" id="selectedRating" value="${currentRatingValue}">
                            <input type="hidden" id="ratingMerkezId" value="${merkezId}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>İptal
                            </button>
                            <button type="button" class="btn btn-primary" id="submitRating">
                                <i class="fas fa-paper-plane me-1"></i>
                                ${currentRating ? 'Güncelle' : 'Puanla'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Initialize stars with current rating
        if (currentRatingValue > 0) {
            setTimeout(() => {
                this.setModalStarRating(currentRatingValue);
            }, 100);
        }
        
        // Show modal
        const modalElement = document.getElementById('ratingModal');
        console.log('Modal element:', modalElement);
        console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
        
        if (modalElement && typeof bootstrap !== 'undefined') {
            console.log('Creating bootstrap modal...');
            
            try {
                const modal = new bootstrap.Modal(modalElement);
                console.log('Bootstrap Modal instance created:', modal);
                
                // Force show with timeout
                setTimeout(() => {
                    console.log('Attempting to show modal...');
                    modal.show();
                    console.log('Modal.show() called');
                    
                    // Check if modal is actually visible
                    setTimeout(() => {
                        const isVisible = modalElement.classList.contains('show');
                        const computedStyle = window.getComputedStyle(modalElement);
                        console.log('Modal visibility check:', {
                            hasShowClass: isVisible,
                            display: computedStyle.display,
                            visibility: computedStyle.visibility,
                            opacity: computedStyle.opacity
                        });
                        
                        if (!isVisible) {
                            console.log('⚠️ Modal not visible, trying manual show...');
                            modalElement.classList.add('show');
                            modalElement.style.display = 'block';
                            document.body.classList.add('modal-open');
                        }
                    }, 500);
                }, 100);
                
            } catch (error) {
                console.error('Error creating/showing modal:', error);
            }
        } else {
            console.error('Bootstrap not available or modal element not found:', {
                modalElement,
                bootstrap: typeof bootstrap
            });
        }

        // Clean up on modal close
        document.getElementById('ratingModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });
    }

    /**
     * Generate interactive stars for rating modal
     */
    generateInteractiveStars() {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<i class="fas fa-star rating-star interactive-star text-muted me-1" 
                        data-rating="${i}" 
                        role="button"
                        aria-label="${i} yıldız ver"
                        tabindex="0"
                        style="cursor: pointer; font-size: 2rem;"></i>`;
        }
        return stars;
    }

    /**
     * Set star rating in modal
     */
    setModalStarRating(rating) {
        const stars = document.querySelectorAll('#ratingModal .rating-star');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-muted');
                star.classList.add('text-warning');
            } else {
                star.classList.remove('text-warning');
                star.classList.add('text-muted');
            }
        });
        document.getElementById('selectedRating').value = rating;
    }

    highlightStars(targetStar) {
        const rating = parseInt(targetStar.dataset.rating);
        const container = targetStar.closest('.rating-stars');
        const stars = container.querySelectorAll('.rating-star');
        
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-muted');
                star.classList.add('text-warning');
            } else {
                star.classList.remove('text-warning');
                star.classList.add('text-muted');
            }
        });
    }

    selectRating(targetStar) {
        const rating = parseInt(targetStar.dataset.rating);
        const selectedRatingInput = document.getElementById('selectedRating');
        if (selectedRatingInput) {
            selectedRatingInput.value = rating;
        }
        
        // Keep stars highlighted
        this.highlightStars(targetStar);
    }

    resetStarHighlight() {
        const selectedRatingInput = document.getElementById('selectedRating');
        const selectedRating = selectedRatingInput ? parseInt(selectedRatingInput.value || 0) : 0;
        
        const container = document.querySelector('#ratingModal .rating-stars');
        if (!container) return;
        
        const stars = container.querySelectorAll('.rating-star');
        stars.forEach((star, index) => {
            if (index < selectedRating) {
                star.classList.remove('text-muted');
                star.classList.add('text-warning');
            } else {
                star.classList.remove('text-warning');
                star.classList.add('text-muted');
            }
        });
    }

    async submitRating() {
        const merkezId = document.getElementById('ratingMerkezId').value;
        const rating = document.getElementById('selectedRating').value;
        const comment = document.getElementById('ratingComment').value;

        if (!rating || rating < 1) {
            this.showToast('Lütfen bir puan seçin!', 'warning');
            return;
        }

        try {
            const response = await fetch('/api/ratings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin', // Include session cookies
                body: JSON.stringify({
                    atik_merkezi_id: merkezId,
                    rating: rating,
                    comment: comment
                })
            });

            // Check if response is actually JSON
            const contentType = response.headers.get('content-type');
            
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                console.error('Server returned non-JSON response:', textResponse);
                this.showToast('Server hatası: JSON bekleniyor ama HTML geldi', 'error');
                return;
            }

            const data = await response.json();

            if (response.ok) {
                this.showToast('Puanınız kaydedildi!', 'success');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('ratingModal'));
                modal.hide();
                
                // Update rating display
                this.updateRatingDisplay(merkezId, data.average_rating, data.total_ratings);
                
            } else {
                this.showToast(data.message || 'Bir hata oluştu!', 'error');
            }
        } catch (error) {
            console.error('Error submitting rating:', error);
            this.showToast('Bir hata oluştu!', 'error');
        }
    }

    async toggleFavorite(merkezId, button) {
        if (!this.userLoggedIn) {
            this.showToast('Favorilere eklemek için giriş yapmalısınız!', 'warning');
            return;
        }

        try {
            const response = await fetch('/api/favorites/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': `Bearer ${window.authToken || ''}`
                },
                body: JSON.stringify({
                    atik_merkezi_id: merkezId
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Update button state
                const icon = button.querySelector('i');
                if (data.is_favorite) {
                    icon.classList.remove('far');
                    icon.classList.add('fas', 'text-danger');
                    this.showToast('Favorilere eklendi!', 'success');
                } else {
                    icon.classList.remove('fas', 'text-danger');
                    icon.classList.add('far');
                    this.showToast('Favorilerden çıkarıldı!', 'info');
                }
            } else {
                this.showToast(data.message || 'Bir hata oluştu!', 'error');
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
            this.showToast('Bir hata oluştu!', 'error');
        }
    }

    generateStarRating(average, interactive = false, totalRatings = 0) {
        let stars = '';
        const fullStars = Math.floor(average);
        const hasHalfStar = average % 1 >= 0.5;
        
        for (let i = 1; i <= 5; i++) {
            let starClass = 'rating-star';
            let starIcon = 'far fa-star';
            
            if (i <= fullStars) {
                starIcon = 'fas fa-star text-warning';
            } else if (i === fullStars + 1 && hasHalfStar) {
                starIcon = 'fas fa-star-half-alt text-warning';
            }
            
            if (interactive) {
                starClass += ' interactive-star';
                starIcon = 'far fa-star text-muted';
                stars += `<i class="${starIcon} ${starClass}" data-rating="${i}" style="cursor: pointer; font-size: 1.5rem;"></i>`;
            } else {
                stars += `<i class="${starIcon}" style="font-size: 0.9rem;"></i>`;
            }
        }
        
        if (!interactive && totalRatings > 0) {
            stars += ` <small class="text-muted ms-1">(${average.toFixed(1)} - ${totalRatings} değerlendirme)</small>`;
        }
        
        return stars;
    }

    updateRatingDisplay(merkezId, averageRating, totalRatings) {
        // Update all rating displays for this center
        const ratingWidgets = document.querySelectorAll(`[data-merkez-id="${merkezId}"] .rating-widget`);
        
        ratingWidgets.forEach(widget => {
            // Update average rating text
            const averageSpan = widget.querySelector('.average-rating');
            if (averageSpan) {
                averageSpan.textContent = averageRating.toFixed(1);
            }
            
            // Update total ratings text
            const totalSpan = widget.querySelector('.total-ratings');
            if (totalSpan) {
                totalSpan.textContent = totalRatings;
            }
            
            // Update star colors
            const starsContainer = widget.querySelector('.stars-display');
            if (starsContainer) {
                this.updateStarColors(starsContainer, averageRating);
            }
        });
    }

    showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toastId = 'toast-' + Date.now();
        const toastClass = type === 'success' ? 'text-bg-success' : 
                          type === 'error' ? 'text-bg-danger' : 
                          type === 'warning' ? 'text-bg-warning' : 'text-bg-info';

        const toastHtml = `
            <div id="${toastId}" class="toast ${toastClass}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement);
        toast.show();

        // Remove toast after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }

    // Load user favorites on page load
    async loadUserFavorites() {
        // TODO: Implement favorites API endpoint
        // Şu anda favorites API endpoint'i olmadığı için bu fonksiyon disable edildi
        return;
        
        if (!this.userLoggedIn) return;

        try {
            const response = await fetch('/api/favorites', {
                headers: {
                    'Authorization': `Bearer ${window.authToken || ''}`,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const favorites = await response.json();
                favorites.forEach(favorite => {
                    const favoriteBtn = document.querySelector(`[data-merkez-id="${favorite.atik_merkezi_id}"] .favorite-btn`);
                    if (favoriteBtn) {
                        const icon = favoriteBtn.querySelector('i');
                        icon.classList.remove('far');
                        icon.classList.add('fas', 'text-danger');
                    }
                });
            }
        } catch (error) {
            console.error('Error loading favorites:', error);
        }
    }
}

// Export for use in other modules
window.RatingModule = RatingModule;

export { RatingModule }; 