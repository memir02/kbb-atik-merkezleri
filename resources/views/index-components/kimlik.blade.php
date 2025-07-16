<!-- User Authentication Section -->
<div class="auth-section py-2" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
    <div class="container">
        <div class="d-flex justify-content-end">
            @guest
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-sign-in-alt me-1"></i> Giriş Yap
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus me-1"></i> Kayıt Ol
                    </a>
                </div>
            @else
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Hoş geldiniz, {{ Auth::user()->name }}</span>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-tachometer-alt me-1"></i> Panelim
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i> Çıkış
                        </button>
                    </form>
                </div>
            @endguest
        </div>
    </div>
</div> 