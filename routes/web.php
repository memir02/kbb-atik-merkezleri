<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AtikMerkeziController;
use App\Http\Controllers\RatingController;
use Illuminate\Support\Facades\Route;

// Ana route'lar

Route::get('/', [AtikMerkeziController::class, 'index'])->name('atik-merkezleri.index');
Route::get('/konuma-gore', [AtikMerkeziController::class, 'konumaGore'])->name('atik-merkezleri.konuma-gore');

// Test route for debugging
Route::get('/test-filter', function() {
    $filter = request('filter');
    return response()->json([
        'has_filter' => request()->has('filter'),
        'filter_value' => $filter,
        'all_params' => request()->all(),
        'query_string' => request()->getQueryString(),
        'url' => request()->fullUrl()
    ]);
});

// Basit HTML test sayfası
Route::get('/debug-form', function() {
    return '<html><body>
        <h2>Filter Test</h2>
        <form method="GET" action="/">
            <label><input type="checkbox" name="filter[]" value="tekstil"> Tekstil</label><br>
            <label><input type="checkbox" name="filter[]" value="cam"> Cam</label><br>
            <button type="submit">Gönder</button>
        </form>
        <hr>
        <h3>Manual Test Links:</h3>
        <a href="/?filter[]=tekstil">Test Link 1</a><br>
        <a href="/?filter%5B%5D=tekstil">Test Link 2</a><br>
        <a href="/test-filter?filter[]=tekstil">Test API 1</a><br>
        <a href="/test-filter?filter%5B%5D=tekstil">Test API 2</a>
        <hr>
        <h3>JavaScript Auto Test:</h3>
        <button onclick="testFilter1()">Auto Test 1</button>
        <button onclick="testFilter2()">Auto Test 2</button>
        <script>
        function testFilter1() {
            console.log("Redirecting to: /?filter[]=tekstil");
            window.location.href = "/?filter[]=tekstil";
        }
        function testFilter2() {
            console.log("Redirecting to: /?filter%5B%5D=tekstil");
            window.location.href = "/?filter%5B%5D=tekstil";
        }
        </script>
    </body></html>';
});

Route::get('/dashboard', function () {
    $user = request()->user();
    $ratings = $user->ratings()->with('atikMerkezi')->get();
    return view('dashboard', compact('ratings'));
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rating API Routes (moved from api.php for session-based auth)
    Route::post('/api/ratings', [RatingController::class, 'submitRating'])->name('api.ratings.submit');
    Route::get('/api/ratings/{atikMerkezi}/user-rating', [RatingController::class, 'getUserRating'])->name('api.ratings.user-rating');
});

require __DIR__.'/auth.php';
