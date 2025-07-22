<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kullanıcı Panelim') }}
        </h2>
    </x-slot>

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
                                    <div class="dashboard-item">
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.title = "Kullanıcı Panelim - Atık Merkezleri";
    </script>
</x-app-layout>
