<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kullanıcı Panelim') }}
        </h2>
    </x-slot>

    <!-- Meta tags -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS için -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* Kullanıcı paneli için uyumlu stiller */
        .dashboard-card {
            background-color: #2c2f33;
            border: 1px solid #404448;
            border-radius: 12px;
            transition: all 0.3s ease;
            color: #ffffff;
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            border-color: #556b7d;
        }
        
        .dashboard-card-header {
            background: linear-gradient(135deg, #495057 0%, #3d4248 100%);
            border-bottom: 1px solid #404448;
            border-radius: 11px 11px 0 0;
            padding: 1rem 1.25rem;
        }
        
        .dashboard-item {
            background-color: #34373b;
            border: 1px solid #404448;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .dashboard-item:hover {
            background-color: #3d4248;
            border-color: #556b7d;
            transform: translateX(5px);
        }
        
        .dashboard-item:last-child {
            margin-bottom: 0;
        }
        
        .dashboard-section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 0;
        }
        
        .dashboard-content {
            max-height: 300px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #495057 #2c2f33;
        }
        
        .dashboard-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .dashboard-content::-webkit-scrollbar-track {
            background: #2c2f33;
            border-radius: 3px;
        }
        
        .dashboard-content::-webkit-scrollbar-thumb {
            background: #495057;
            border-radius: 3px;
        }
        
        .dashboard-content::-webkit-scrollbar-thumb:hover {
            background: #556b7d;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #9ca3af;
        }
        
        .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .merkez-name {
            font-weight: 600;
            color: #e9ecef;
            margin-bottom: 0.25rem;
        }
        
        .comment-text {
            color: #ced4da;
            font-style: italic;
            margin-bottom: 0.25rem;
        }
        
        .rating-stars {
            color: #ffc107;
            margin-bottom: 0.25rem;
        }
        
        .item-date {
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        .stats-badge {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- İstatistik Kartları -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card-header text-center">
                            <i class="fas fa-comment text-info"></i>
                            <h6 class="dashboard-section-title justify-content-center">
                                Toplam Yorumlarım
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="stats-badge">{{ $ratings->where('comment', '!=', '')->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card-header text-center">
                            <i class="fas fa-star text-warning"></i>
                            <h6 class="dashboard-section-title justify-content-center">
                                Toplam Puanlarım
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="stats-badge">{{ $ratings->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card-header text-center">
                            <i class="fas fa-heart text-danger"></i>
                            <h6 class="dashboard-section-title justify-content-center">
                                Favori Merkezlerim
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="stats-badge">{{ $favoriMerkezler->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ana İçerik Kartları -->
            <div class="row">
                <!-- Yorumlarım -->
                <div class="col-lg-4 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-section-title">
                                <i class="fas fa-comment text-info"></i>
                                {{ __("Son Yorumlarım") }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="dashboard-content p-3">
                                @forelse($ratings->where('comment', '!=', '')->take(5) as $rating)
                                    <div class="dashboard-item" data-rating-id="{{ $rating->id }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="merkez-name">
                                                    <i class="fas fa-leaf text-success me-1"></i>
                                                    {{ $rating->atikMerkezi->title ?? 'Merkez' }}
                                                </div>
                                                <div class="comment-text">
                                                    "{{ Str::limit($rating->comment, 80) }}"
                                                </div>
                                                <div class="rating-stars">
                                                    {{ str_repeat('⭐', $rating->rating) }}
                                                </div>
                                                <div class="item-date">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $rating->created_at->format('d.m.Y H:i') }}
                                                </div>
                                            </div>
                                            <div class="ms-2">
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm delete-rating-btn" 
                                                        data-rating-id="{{ $rating->id }}"
                                                        title="Yorumu Sil">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-state">
                                        <i class="fas fa-comment-slash"></i>
                                        <p>Henüz yorum yapmadınız.</p>
                                        <small>Atık merkezlerine yorum yaparak deneyimlerinizi paylaşın.</small>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Puanlarım -->
                <div class="col-lg-4 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-section-title">
                                <i class="fas fa-star text-warning"></i>
                                {{ __("Son Puanlarım") }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="dashboard-content p-3">
                                @forelse($ratings->take(8) as $rating)
                                    <div class="dashboard-item">
                                        <div class="merkez-name">
                                            <i class="fas fa-leaf text-success me-1"></i>
                                            {{ $rating->atikMerkezi->title ?? 'Merkez' }}
                                        </div>
                                        <div class="rating-stars">
                                            {{ str_repeat('⭐', $rating->rating) }}
                                            <span class="text-muted ms-1">({{ $rating->rating }}/5)</span>
                                        </div>
                                        <div class="item-date">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $rating->created_at->format('d.m.Y H:i') }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-state">
                                        <i class="fas fa-star-half-alt"></i>
                                        <p>Henüz puan vermediniz.</p>
                                        <small>Atık merkezlerini puanlayarak hizmet kalitesini değerlendirin.</small>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Favori Atık Merkezlerim -->
                <div class="col-lg-4 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-section-title">
                                <i class="fas fa-heart text-danger"></i>
                                {{ __("Favori Atık Merkezlerim") }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="dashboard-content p-3">
                                @forelse($favoriMerkezler as $favorite)
                                    <div class="dashboard-item">
                                        <div class="merkez-name">
                                            <i class="fas fa-heart text-danger me-1"></i>
                                            {{ $favorite->atikMerkezi->title ?? 'Merkez' }}
                                        </div>
                                        @if($favorite->atikMerkezi->adres)
                                            <div class="comment-text">
                                                <i class="fas fa-leaf text-success me-1"></i>
                                                {{ Str::limit($favorite->atikMerkezi->adres, 60) }}
                                            </div>
                                        @endif
                                        <div class="item-date">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $favorite->created_at->format('d.m.Y H:i') }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-state">
                                        <i class="fas fa-heart-broken"></i>
                                        <p>Henüz favori atık merkezi eklememişsiniz.</p>
                                        <small>Beğendiğiniz merkezleri favorilerinize ekleyin.</small>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Yorum Silme Onay Modal'ı -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: #2c2f33; border: 1px solid #404448;">
                <div class="modal-header" style="border-bottom: 1px solid #404448;">
                    <h5 class="modal-title text-white" id="deleteConfirmModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Yorumu Sil
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-white">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="fas fa-trash-alt text-danger" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <p class="mb-2"><strong>Bu yorumu silmek istediğinizden emin misiniz?</strong></p>
                            <p class="text-warning mb-0">
                                <small>
                                    <i class="fas fa-info-circle me-1 text-warning"></i>
                                    Bu işlem geri alınamaz ve yorumunuz kalıcı olarak silinecektir.
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #404448;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        İptal
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash-alt me-1"></i>
                        Evet, Sil
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.title = "Kullanıcı Panelim - Atık Merkezleri";

                 // CSRF token setup for AJAX requests
         const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                 // Global değişkenler - silinecek yorum bilgileri
         let currentDeleteButton = null;
         let currentRatingId = null;
         let currentRatingItem = null;

         // Yorum silme işlemi
         document.addEventListener('DOMContentLoaded', function() {
             const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
             
             // Silme butonlarına event listener ekle
             document.querySelectorAll('.delete-rating-btn').forEach(button => {
                 button.addEventListener('click', function() {
                     currentDeleteButton = this;
                     currentRatingId = this.dataset.ratingId;
                     currentRatingItem = this.closest('.dashboard-item');
                     
                     // Modal'ı göster
                     deleteModal.show();
                 });
             });

             // Modal'daki "Evet, Sil" butonuna event listener
             document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                 if (!currentRatingId || !currentDeleteButton || !currentRatingItem) return;
                 
                 // Modal'ı kapat
                 deleteModal.hide();
                 
                 // Butonu devre dışı bırak ve loading göster
                 currentDeleteButton.disabled = true;
                 currentDeleteButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                 
                 // AJAX ile silme isteği gönder
                 const formData = new FormData();
                 formData.append('rating_id', currentRatingId);
                 
                 fetch('{{ route("api.ratings.delete") }}', {
                     method: 'DELETE',
                     headers: {
                         'X-CSRF-TOKEN': csrfToken,
                         'Accept': 'application/json'
                     },
                     body: formData
                 })
                 .then(response => response.json())
                 .then(data => {
                     if (data.success) {
                         // Başarılı silme animasyonu
                         currentRatingItem.style.transition = 'opacity 0.3s ease';
                         currentRatingItem.style.opacity = '0';
                         
                         setTimeout(() => {
                             currentRatingItem.remove();
                             
                             // Eğer hiç yorum kalmadıysa empty state göster
                             const remainingComments = document.querySelectorAll('.dashboard-content .dashboard-item').length;
                             if (remainingComments === 0) {
                                 const dashboardContent = document.querySelector('.dashboard-content');
                                 dashboardContent.innerHTML = `
                                     <div class="empty-state">
                                         <i class="fas fa-comment-slash"></i>
                                         <p>Henüz yorum yapmadınız.</p>
                                         <small>Atık merkezlerine yorum yaparak deneyimlerinizi paylaşın.</small>
                                     </div>
                                 `;
                             }
                         }, 300);
                         
                         // Başarı mesajı göster
                         showNotification('success', data.message);
                         
                         // İstatistikleri güncelle (1.5 saniye sonra)
                         setTimeout(() => {
                             location.reload();
                         }, 1500);
                     } else {
                         showNotification('error', data.message || 'Yorum silinirken bir hata oluştu');
                         
                         // Butonu tekrar aktif hale getir
                         currentDeleteButton.disabled = false;
                         currentDeleteButton.innerHTML = '<i class="fas fa-trash-alt"></i>';
                     }
                 })
                 .catch(error => {
                     showNotification('error', 'Yorum silinirken bir hata oluştu');
                     
                     // Butonu tekrar aktif hale getir
                     currentDeleteButton.disabled = false;
                     currentDeleteButton.innerHTML = '<i class="fas fa-trash-alt"></i>';
                 });
                 
                 // Global değişkenleri temizle
                 currentDeleteButton = null;
                 currentRatingId = null;
                 currentRatingItem = null;
             });
         });

        // Bildirim gösterme fonksiyonu
        function showNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
            
            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.style.minWidth = '300px';

            const icon = document.createElement('i');
            icon.className = `fas ${iconClass} me-2`;
            notification.appendChild(icon);

            const text = document.createTextNode(message);
            notification.appendChild(text);

            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'btn-close';
            closeButton.setAttribute('data-bs-dismiss', 'alert');
            notification.appendChild(closeButton);

            document.body.appendChild(notification);
            
            // 5 saniye sonra otomatik kapat
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(notification);
                bsAlert.close();
            }, 5000);
        }
    </script>
</x-app-layout>
