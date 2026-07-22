# Tutorial Lengkap: Laravel CRUD + Auth (Register/Login/Logout) + Upload Gambar + Role Admin/User

Berikut panduan step by step dari nol sampai jadi, menggunakan Laravel dengan sistem autentikasi manual (biar kamu paham alurnya, bukan cuma pakai Breeze/Jetstream instan).

## 1. Install Laravel & Setup Awal

```bash
composer create-project laravel/laravel crud-app
cd crud-app
```

Konfigurasi database di file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crud_app
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `crud_app` di MySQL/phpMyAdmin, lalu jalankan:
```bash
php artisan storage:link
```
(ini penting untuk upload gambar bisa diakses publik)

## 2. Modifikasi Tabel Users (Tambah Role)

Buka `database/migrations/xxxx_create_users_table.php`, tambahkan kolom role:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->enum('role', ['admin', 'user'])->default('user'); // tambahan
    $table->rememberToken();
    $table->timestamps();
});
```

## 3. Buat Model & Migration untuk Produk (Contoh CRUD)

```bash
php artisan make:model Produk -m
```

Edit migration `create_produks_table.php`:
```php
public function up(): void
{
    Schema::create('produks', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->text('deskripsi');
        $table->integer('harga');
        $table->string('gambar')->nullable();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}
```

Jalankan migrasi:
```bash
php artisan migrate
```

Edit `app/Models/Produk.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'deskripsi', 'harga', 'gambar', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

## 4. Buat AuthController (Register, Login, Logout)

```bash
php artisan make:controller AuthController
```

Isi `app/Http/Controllers/AuthController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', // default role
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil, silakan login.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            }
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
```

## 5. Buat Middleware untuk Cek Role Admin

```bash
php artisan make:middleware AdminMiddleware
```

Isi `app/Http/Middleware/AdminMiddleware.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Hanya admin yang bisa mengakses halaman ini.');
    }
}
```

Daftarkan middleware di `bootstrap/app.php` (Laravel 11) atau `app/Http/Kernel.php` (Laravel 10 ke bawah).

**Laravel 11 (`bootstrap/app.php`):**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);
})
```

**Laravel 10 (`app/Http/Kernel.php`):**
```php
protected $middlewareAliases = [
    // ...
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
];
```

## 6. Buat ProdukController (CRUD + Upload Gambar)

```bash
php artisan make:controller ProdukController --resource
```

Isi `app/Http/Controllers/ProdukController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index()
    {
        // Admin lihat semua, user lihat punya sendiri
        if (auth()->user()->role === 'admin') {
            $produks = Produk::latest()->paginate(10);
        } else {
            $produks = Produk::where('user_id', auth()->id())->latest()->paginate(10);
        }

        return view('produk.index', compact('produks'));
    }

    public function create()
    {
        return view('produk.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only('nama', 'deskripsi', 'harga');
        $data['user_id'] = auth()->id();

        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('produk', 'public');
            $data['gambar'] = $path;
        }

        Produk::create($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        $this->authorizeAccess($produk);
        return view('produk.edit', compact('produk'));
    }

    public function update(Request $request, Produk $produk)
    {
        $this->authorizeAccess($produk);

        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only('nama', 'deskripsi', 'harga');

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama kalau ada
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        $produk->update($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy(Produk $produk)
    {
        $this->authorizeAccess($produk);

        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }

        $produk->delete();

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function authorizeAccess(Produk $produk)
    {
        if (auth()->user()->role !== 'admin' && $produk->user_id !== auth()->id()) {
            abort(403, 'Anda tidak punya akses ke produk ini.');
        }
    }
}
```

## 7. Setup Routes

Edit `routes/web.php`:

```php
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

// Halaman awal redirect ke login
Route::get('/', fn () => redirect('/login'));

// Guest routes (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Auth routes (sudah login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // CRUD produk (user & admin bisa akses, tapi dibatasi di controller)
    Route::resource('produk', ProdukController::class);
});

// Khusus admin
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');
});
```

## 8. Buat Views

### Layout dasar `resources/views/layouts/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Aplikasi')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow p-4 flex justify-between">
        <div class="font-bold">Laravel CRUD App</div>
        <div>
            @auth
                <span class="mr-4">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                <a href="{{ route('produk.index') }}" class="mr-4">Produk</a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="mr-4">Admin</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600">Logout</button>
                </form>
            @endauth
        </div>
    </nav>

    <div class="container mx-auto p-6">
        @if(session('success'))
            <div class="bg-green-200 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
```

### Register `resources/views/auth/register.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <form method="POST" action="{{ route('register') }}" class="bg-white p-8 rounded shadow w-96">
        @csrf
        <h2 class="text-xl font-bold mb-4">Register</h2>

        @if ($errors->any())
            <div class="bg-red-200 p-2 mb-3 rounded text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <input type="text" name="name" placeholder="Nama" value="{{ old('name') }}" class="w-full border p-2 mb-3 rounded" required>
        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" class="w-full border p-2 mb-3 rounded" required>
        <input type="password" name="password" placeholder="Password" class="w-full border p-2 mb-3 rounded" required>
        <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" class="w-full border p-2 mb-3 rounded" required>

        <button type="submit" class="bg-blue-600 text-white w-full p-2 rounded">Daftar</button>
        <p class="mt-3 text-sm">Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600">Login</a></p>
    </form>
</body>
</html>
```

### Login `resources/views/auth/login.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <form method="POST" action="{{ route('login') }}" class="bg-white p-8 rounded shadow w-96">
        @csrf
        <h2 class="text-xl font-bold mb-4">Login</h2>

        @if ($errors->any())
            <div class="bg-red-200 p-2 mb-3 rounded text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" class="w-full border p-2 mb-3 rounded" required>
        <input type="password" name="password" placeholder="Password" class="w-full border p-2 mb-3 rounded" required>

        <button type="submit" class="bg-blue-600 text-white w-full p-2 rounded">Login</button>
        <p class="mt-3 text-sm">Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600">Register</a></p>
    </form>
</body>
</html>
```

### Dashboard sederhana `resources/views/dashboard.blade.php`

```blade
@extends('layouts.app')
@section('content')
    <h1 class="text-2xl font-bold">Selamat datang, {{ auth()->user()->name }}!</h1>
    <p>Role kamu: {{ auth()->user()->role }}</p>
@endsection
```

### Admin dashboard `resources/views/admin/dashboard.blade.php`

```blade
@extends('layouts.app')
@section('content')
    <h1 class="text-2xl font-bold">Dashboard Admin</h1>
    <p>Halaman ini hanya bisa diakses oleh admin.</p>
@endsection
```

### List produk `resources/views/produk/index.blade.php`

```blade
@extends('layouts.app')
@section('content')
    <div class="flex justify-between mb-4">
        <h1 class="text-2xl font-bold">Daftar Produk</h1>
        <a href="{{ route('produk.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">+ Tambah Produk</a>
    </div>

    <table class="w-full bg-white shadow rounded">
        <thead>
            <tr class="border-b">
                <th class="p-2 text-left">Gambar</th>
                <th class="p-2 text-left">Nama</th>
                <th class="p-2 text-left">Harga</th>
                <th class="p-2 text-left">Pemilik</th>
                <th class="p-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($produks as $produk)
                <tr class="border-b">
                    <td class="p-2">
                        @if($produk->gambar)
                            <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-16 h-16 object-cover rounded">
                        @else
                            <span class="text-gray-400">Tidak ada</span>
                        @endif
                    </td>
                    <td class="p-2">{{ $produk->nama }}</td>
                    <td class="p-2">Rp {{ number_format($produk->harga, 0, ',', '.') }}</td>
                    <td class="p-2">{{ $produk->user->name }}</td>
                    <td class="p-2">
                        <a href="{{ route('produk.edit', $produk) }}" class="text-blue-600 mr-2">Edit</a>
                        <form action="{{ route('produk.destroy', $produk) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-4 text-center text-gray-400">Belum ada produk.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $produks->links() }}</div>
@endsection
```

### Form tambah produk `resources/views/produk/create.blade.php`

```blade
@extends('layouts.app')
@section('content')
    <h1 class="text-2xl font-bold mb-4">Tambah Produk</h1>

    <form method="POST" action="{{ route('produk.store') }}" enctype="multipart/form-data" class="bg-white p-6 rounded shadow max-w-lg">
        @csrf
        <label class="block mb-1">Nama Produk</label>
        <input type="text" name="nama" class="w-full border p-2 mb-3 rounded" value="{{ old('nama') }}">

        <label class="block mb-1">Deskripsi</label>
        <textarea name="deskripsi" class="w-full border p-2 mb-3 rounded">{{ old('deskripsi') }}</textarea>

        <label class="block mb-1">Harga</label>
        <input type="number" name="harga" class="w-full border p-2 mb-3 rounded" value="{{ old('harga') }}">

        <label class="block mb-1">Gambar</label>
        <input type="file" name="gambar" class="w-full border p-2 mb-3 rounded">

        @if ($errors->any())
            <div class="bg-red-200 p-2 mb-3 rounded text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
    </form>
@endsection
```

### Form edit produk `resources/views/produk/edit.blade.php`

```blade
@extends('layouts.app')
@section('content')
    <h1 class="text-2xl font-bold mb-4">Edit Produk</h1>

    <form method="POST" action="{{ route('produk.update', $produk) }}" enctype="multipart/form-data" class="bg-white p-6 rounded shadow max-w-lg">
        @csrf
        @method('PUT')

        <label class="block mb-1">Nama Produk</label>
        <input type="text" name="nama" class="w-full border p-2 mb-3 rounded" value="{{ old('nama', $produk->nama) }}">

        <label class="block mb-1">Deskripsi</label>
        <textarea name="deskripsi" class="w-full border p-2 mb-3 rounded">{{ old('deskripsi', $produk->deskripsi) }}</textarea>

        <label class="block mb-1">Harga</label>
        <input type="number" name="harga" class="w-full border p-2 mb-3 rounded" value="{{ old('harga', $produk->harga) }}">

        @if($produk->gambar)
            <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-24 h-24 object-cover rounded mb-2">
        @endif

        <label class="block mb-1">Ganti Gambar (opsional)</label>
        <input type="file" name="gambar" class="w-full border p-2 mb-3 rounded">

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
    </form>
@endsection
```

## 9. Seeder untuk Akun Admin (Opsional tapi Direkomendasikan)

```bash
php artisan make:seeder AdminSeeder
```

Isi `database/seeders/AdminSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
    }
}
```

Jalankan:
```bash
php artisan db:seed --class=AdminSeeder
```

## 10. Jalankan Aplikasi

```bash
php artisan serve
```

Akses di `http://127.0.0.1:8000`. Login sebagai admin (`admin@admin.com` / `admin123`) untuk lihat dashboard admin, atau register akun baru sebagai user biasa untuk uji CRUD produk.

---

**Catatan penting:**
- Field `role` harus di-cast otomatis sebagai enum, jangan lupa validasi di controller kalau kamu tambah fitur ubah role user oleh admin.
- Untuk produksi, tambahkan validasi lebih ketat, rate limiting login, dan email verification kalau perlu.
- Kalau mau lebih cepat, Laravel Breeze (`composer require laravel/breeze`) sudah menyediakan scaffolding register/login/logout siap pakai — tinggal kamu tambah kolom role dan middleware admin di atasnya.

Kalau kamu mau, aku bisa lanjutkan dengan versi yang pakai **Laravel Breeze** biar lebih ringkas, atau tambahkan fitur **manajemen user oleh admin** (ubah role, hapus user, dll).
