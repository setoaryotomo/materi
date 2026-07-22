# Rangkuman Lengkap Web Development
### HTML, PHP, MySQL, JavaScript, Laravel, CodeIgniter, dan Git

---

## 1. HTML (HyperText Markup Language)

### Penjelasan
HTML adalah bahasa markup (bukan bahasa pemrograman) yang digunakan untuk membangun **struktur** halaman web. Browser membaca file HTML lalu merendernya menjadi tampilan visual.

### Istilah Penting
- **Tag**: Kode pembungkus elemen, contoh `<p>`, `<div>`, `<h1>`.
- **Element**: Tag pembuka + isi + tag penutup, contoh `<p>Halo</p>`.
- **Attribute**: Informasi tambahan pada tag, contoh `class`, `id`, `src`, `href`.
- **DOM (Document Object Model)**: Representasi struktur HTML sebagai pohon objek yang bisa dimanipulasi JavaScript.
- **Semantic HTML**: Tag yang punya makna struktural, contoh `<header>`, `<footer>`, `<article>`, `<section>`, `<nav>`.
- **Self-closing tag**: Tag tanpa penutup, contoh `<img />`, `<input />`, `<br />`.

### Struktur Dasar
```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Judul Halaman</title>
</head>
<body>
    <h1>Selamat Datang</h1>
    <p>Ini paragraf.</p>
</body>
</html>
```

### Tag-tag Umum
| Tag | Fungsi |
|---|---|
| `<h1>`-`<h6>` | Heading/judul |
| `<p>` | Paragraf |
| `<a href="">` | Link |
| `<img src="">` | Gambar |
| `<ul>`/`<ol>`/`<li>` | List |
| `<table>`, `<tr>`, `<td>` | Tabel |
| `<form>`, `<input>`, `<button>` | Form input |
| `<div>`, `<span>` | Container umum |

---

## 2. PHP (Hypertext Preprocessor)

### Penjelasan
PHP adalah bahasa pemrograman **server-side**, artinya kode dijalankan di server, hasilnya (biasanya HTML) dikirim ke browser. PHP sering dipakai untuk membuat website dinamis yang terhubung ke database.

### Istilah Penting
- **Server-side scripting**: Kode dieksekusi di server sebelum dikirim ke client.
- **Variable**: Diawali `$`, contoh `$nama = "Seto";`.
- **Function**: Blok kode yang bisa dipanggil ulang.
- **Array & Associative Array**: Kumpulan data, `$arr = ["a","b"]` atau `$arr = ["nama"=>"Seto"]`.
- **Superglobals**: Variabel bawaan PHP seperti `$_GET`, `$_POST`, `$_SESSION`, `$_SERVER`, `$_FILES`.
- **Include/Require**: Memasukkan file PHP lain (`include`, `require`, `include_once`, `require_once`).
- **OOP di PHP**: `class`, `object`, `property`, `method`, `extends`, `interface`.

### Contoh Dasar
```php
<?php
$nama = "Seto";
echo "Halo, " . $nama;

function tambah($a, $b) {
    return $a + $b;
}
echo tambah(2, 3); // 5

// Koneksi database sederhana (mysqli)
$conn = new mysqli("localhost", "root", "", "db_test");
$result = $conn->query("SELECT * FROM users");
while ($row = $result->fetch_assoc()) {
    echo $row['nama'];
}
?>
```

### Menjalankan PHP
1. Install XAMPP/Laragon (sudah termasuk PHP, MySQL, Apache).
2. Simpan file `.php` di folder `htdocs` (XAMPP) atau `www` (Laragon).
3. Akses via browser: `http://localhost/namafile.php`.

---

## 3. MySQL

### Penjelasan
MySQL adalah **Relational Database Management System (RDBMS)** — software untuk menyimpan data dalam bentuk tabel (baris & kolom) dan diakses menggunakan bahasa SQL.

### Istilah Penting
- **Database**: Kumpulan tabel.
- **Table**: Kumpulan data terstruktur (baris = record, kolom = field).
- **Primary Key (PK)**: Kolom unik identitas tiap baris.
- **Foreign Key (FK)**: Kolom yang mereferensikan PK di tabel lain, untuk relasi antar tabel.
- **Index**: Struktur untuk mempercepat pencarian data.
- **Query**: Perintah SQL untuk mengambil/mengubah data.
- **JOIN**: Menggabungkan data dari beberapa tabel (`INNER JOIN`, `LEFT JOIN`, `RIGHT JOIN`).
- **Normalization**: Proses merancang tabel agar tidak redundan.

### Perintah SQL Dasar
```sql
-- Membuat database
CREATE DATABASE toko;
USE toko;

-- Membuat tabel
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CRUD
INSERT INTO products (name, price) VALUES ('Buku', 15000);
SELECT * FROM products;
UPDATE products SET price = 17000 WHERE id = 1;
DELETE FROM products WHERE id = 1;

-- JOIN contoh
SELECT orders.id, products.name
FROM orders
JOIN products ON orders.product_id = products.id;
```

---

## 4. JavaScript

### Penjelasan
JavaScript adalah bahasa pemrograman **client-side** (dijalankan di browser) yang membuat halaman web interaktif/dinamis (misal validasi form, animasi, update tampilan tanpa reload). Juga bisa dijalankan di server via Node.js.

### Istilah Penting
- **DOM Manipulation**: Mengubah elemen HTML lewat JS (`document.querySelector`, `.innerHTML`).
- **Event**: Aksi pengguna, contoh `click`, `submit`, `change`.
- **Event Listener**: Fungsi yang "mendengarkan" event.
- **Variable**: `let`, `const`, `var`.
- **Function & Arrow Function**: `function(){}` atau `() => {}`.
- **Asynchronous**: Kode yang berjalan tanpa memblokir eksekusi lain — `callback`, `Promise`, `async/await`.
- **AJAX / Fetch API**: Mengambil data dari server tanpa reload halaman.
- **JSON**: Format data pertukaran, mirip objek JS.

### Contoh Dasar
```javascript
// Variabel & fungsi
let nama = "Seto";
const sapa = (n) => `Halo, ${n}`;
console.log(sapa(nama));

// DOM manipulation
document.querySelector("#btn").addEventListener("click", () => {
    document.querySelector("#output").innerText = "Tombol diklik!";
});

// Fetch API (AJAX)
fetch("https://api.example.com/data")
    .then(res => res.json())
    .then(data => console.log(data))
    .catch(err => console.error(err));

// Async/Await
async function ambilData() {
    const res = await fetch("https://api.example.com/data");
    const data = await res.json();
    console.log(data);
}
```

---

## 5. Laravel

### Penjelasan
Laravel adalah **framework PHP** modern yang mengikuti pola **MVC**, dilengkapi tools bawaan seperti routing, ORM (Eloquent), migration, blade templating, sehingga development lebih cepat dan terstruktur dibanding PHP native.

### Istilah Penting
- **Route**: Menentukan URL mengarah ke fungsi/controller mana.
- **Controller**: Tempat logika aplikasi (menerima request, proses, kirim response).
- **Model**: Representasi tabel database (Eloquent ORM).
- **View**: Tampilan (Blade template, file `.blade.php`).
- **Migration**: Version control untuk struktur database (buat/ubah tabel via kode, bukan manual di phpMyAdmin).
- **Seeder**: Mengisi data awal/dummy ke database secara otomatis.
- **Eloquent ORM**: Cara mengakses database pakai objek PHP, bukan query SQL manual.
- **Middleware**: Filter request sebelum masuk ke controller (misal cek login).
- **Artisan**: Command Line Tool bawaan Laravel (`php artisan ...`).
- **Blade**: Templating engine Laravel (`{{ }}`, `@if`, `@foreach`).
- **.env**: File konfigurasi environment (database, app key, dll).

### Konsep MVC
```
User Request
   ↓
 Route (routes/web.php) --> menentukan controller mana yang dipanggil
   ↓
Controller --> mengambil/mengolah data lewat Model
   ↓
Model (Eloquent) --> berkomunikasi dengan Database
   ↓
Controller --> mengirim data ke View
   ↓
View (Blade) --> menampilkan hasil ke user (HTML)
```
- **Model**: data & logika bisnis (tabel database).
- **View**: tampilan yang dilihat user.
- **Controller**: penghubung/otak yang mengatur alur data antara Model dan View.

### Step by Step Install Laravel

**Prasyarat**: PHP >= 8.1, Composer, MySQL, terminal (CMD/Git Bash).

1. Install **Composer** dari https://getcomposer.org (cek dengan `composer -v`).
2. Install Laravel via Composer:
   ```bash
   composer global require laravel/installer
   ```
3. Buat project baru:
   ```bash
   laravel new nama-project
   # atau
   composer create-project laravel/laravel nama-project
   ```
4. Masuk folder project:
   ```bash
   cd nama-project
   ```
5. Jalankan server development:
   ```bash
   php artisan serve
   ```
6. Buka browser: `http://127.0.0.1:8000`

### Setting Project Laravel

1. Buka file `.env`, atur koneksi database:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database
   DB_USERNAME=root
   DB_PASSWORD=
   ```
2. Buat database `nama_database` di phpMyAdmin/MySQL dulu (kosong saja, Laravel yang isi struktur lewat migration).
3. Generate application key (biasanya otomatis saat instal, kalau belum):
   ```bash
   php artisan key:generate
   ```

### Step by Step Membuat CRUD di Laravel (contoh: data "Products")

1. **Buat Model + Migration + Controller sekaligus:**
   ```bash
   php artisan make:model Product -mcr
   ```
   (`-m` = migration, `-c` = controller, `-r` = resource controller dengan method CRUD lengkap)

2. **Edit file migration** di `database/migrations/xxxx_create_products_table.php`:
   ```php
   public function up()
   {
       Schema::create('products', function (Blueprint $table) {
           $table->id();
           $table->string('name');
           $table->decimal('price', 10, 2);
           $table->timestamps();
       });
   }
   ```

3. **Jalankan migration** (membuat tabel di database sungguhan):
   ```bash
   php artisan migrate
   ```

4. **Edit Model** `app/Models/Product.php`:
   ```php
   class Product extends Model
   {
       protected $fillable = ['name', 'price'];
   }
   ```

5. **Buat Route resource** di `routes/web.php`:
   ```php
   use App\Http\Controllers\ProductController;
   Route::resource('products', ProductController::class);
   ```
   (Ini otomatis membuat 7 route: index, create, store, show, edit, update, destroy)

6. **Isi Controller** `app/Http/Controllers/ProductController.php`:
   ```php
   use App\Models\Product;

   public function index() {
       $products = Product::all();
       return view('products.index', compact('products'));
   }

   public function create() {
       return view('products.create');
   }

   public function store(Request $request) {
       $request->validate(['name' => 'required', 'price' => 'required|numeric']);
       Product::create($request->all());
       return redirect()->route('products.index');
   }

   public function edit(Product $product) {
       return view('products.edit', compact('product'));
   }

   public function update(Request $request, Product $product) {
       $product->update($request->all());
       return redirect()->route('products.index');
   }

   public function destroy(Product $product) {
       $product->delete();
       return redirect()->route('products.index');
   }
   ```

7. **Buat View** di `resources/views/products/` — contoh `index.blade.php`:
   ```php
   @foreach ($products as $product)
       <p>{{ $product->name }} - {{ $product->price }}
       <a href="{{ route('products.edit', $product->id) }}">Edit</a>
       <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline">
           @csrf @method('DELETE')
           <button type="submit">Hapus</button>
       </form></p>
   @endforeach
   <a href="{{ route('products.create') }}">Tambah Produk</a>
   ```
   Buat juga `create.blade.php` dan `edit.blade.php` berisi form input dengan `@csrf`.

8. **Test** di browser: `http://127.0.0.1:8000/products`

### Migration Lanjutan
```bash
php artisan make:migration create_orders_table   # buat migration baru
php artisan migrate                              # jalankan migration
php artisan migrate:rollback                     # batalkan migration terakhir
php artisan migrate:refresh                      # rollback semua lalu migrate ulang
php artisan migrate:fresh                        # hapus semua tabel lalu migrate ulang
```

### Seeder
```bash
php artisan make:seeder ProductSeeder
```
```php
// database/seeders/ProductSeeder.php
public function run()
{
    Product::create(['name' => 'Buku', 'price' => 15000]);
    Product::create(['name' => 'Pensil', 'price' => 2000]);
}
```
Daftarkan di `DatabaseSeeder.php`:
```php
public function run()
{
    $this->call(ProductSeeder::class);
}
```
Jalankan:
```bash
php artisan db:seed
# atau sekaligus migrate + seed
php artisan migrate:fresh --seed
```

### Perintah Artisan Berguna Lainnya
```bash
php artisan make:controller NamaController
php artisan make:model NamaModel
php artisan make:migration nama_migration
php artisan route:list          # lihat semua route
php artisan tinker              # console interaktif untuk test kode
```

---

## 6. CodeIgniter

### Penjelasan
CodeIgniter (CI) adalah framework PHP yang **ringan dan cepat**, juga berbasis MVC, tapi lebih sederhana dan minim konfigurasi dibanding Laravel. Cocok untuk yang ingin belajar konsep MVC tanpa banyak "magic" seperti Eloquent.

### Istilah Penting
- **Controller**: Sama seperti Laravel, mengatur alur logika.
- **Model**: Berhubungan dengan database, biasanya pakai Query Builder CI, bukan ORM penuh.
- **View**: File tampilan (`.php` biasa, bukan Blade).
- **Query Builder**: Cara CI menulis query SQL secara terstruktur lewat PHP (`$this->db->get('products')`).
- **Autoload**: Konfigurasi library/helper yang otomatis dimuat di `app/Config/Autoload.php`.
- **Helper**: Kumpulan fungsi bantu (misal `url_helper`, `form_helper`).
- **Library**: Kelas bawaan CI untuk fungsi tertentu (session, email, upload, dll).
- **Routes**: Diatur di `app/Config/Routes.php`.

### Konsep MVC di CodeIgniter
Sama seperti Laravel:
```
Request → Routes → Controller → Model → Database
                        ↓
                      View → Response ke user
```
Bedanya CI lebih eksplisit — kita sering menulis manual query builder, sedangkan Laravel banyak "otomatis" lewat Eloquent.

### Step by Step Install CodeIgniter (CI4)

1. Prasyarat: PHP >= 8.1, Composer.
2. Buat project baru:
   ```bash
   composer create-project codeigniter4/appstarter nama-project
   ```
3. Masuk folder:
   ```bash
   cd nama-project
   ```
4. Jalankan server:
   ```bash
   php spark serve
   ```
5. Buka browser: `http://localhost:8080`

### Setting Project CodeIgniter

1. Buka file `.env` (copy dari `env` jika belum ada file `.env`), aktifkan environment:
   ```
   CI_ENVIRONMENT = development
   ```
2. Atur koneksi database di `.env`:
   ```
   database.default.hostname = localhost
   database.default.database = nama_database
   database.default.username = root
   database.default.password =
   database.default.DBDriver = MySQLi
   ```
3. Buat database `nama_database` di MySQL/phpMyAdmin.
4. Set base URL di `.env`:
   ```
   app.baseURL = 'http://localhost:8080/'
   ```

### Step by Step Membuat CRUD di CodeIgniter (contoh: "Products")

1. **Buat Migration**:
   ```bash
   php spark make:migration CreateProductsTable
   ```
   Edit file di `app/Database/Migrations/`:
   ```php
   public function up()
   {
       $this->forge->addField([
           'id'    => ['type' => 'INT', 'auto_increment' => true],
           'name'  => ['type' => 'VARCHAR', 'constraint' => 100],
           'price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
           'created_at' => ['type' => 'DATETIME', 'null' => true],
       ]);
       $this->forge->addKey('id', true);
       $this->forge->createTable('products');
   }

   public function down()
   {
       $this->forge->dropTable('products');
   }
   ```
2. **Jalankan migration**:
   ```bash
   php spark migrate
   ```
3. **Buat Model**:
   ```bash
   php spark make:model ProductModel
   ```
   ```php
   // app/Models/ProductModel.php
   class ProductModel extends Model
   {
       protected $table = 'products';
       protected $allowedFields = ['name', 'price'];
   }
   ```
4. **Buat Controller**:
   ```bash
   php spark make:controller Products
   ```
   ```php
   // app/Controllers/Products.php
   use App\Models\ProductModel;

   class Products extends BaseController
   {
       protected $productModel;

       public function __construct() {
           $this->productModel = new ProductModel();
       }

       public function index() {
           $data['products'] = $this->productModel->findAll();
           return view('products/index', $data);
       }

       public function create() {
           return view('products/create');
       }

       public function store() {
           $this->productModel->save([
               'name'  => $this->request->getPost('name'),
               'price' => $this->request->getPost('price'),
           ]);
           return redirect()->to('/products');
       }

       public function edit($id) {
           $data['product'] = $this->productModel->find($id);
           return view('products/edit', $data);
       }

       public function update($id) {
           $this->productModel->update($id, [
               'name'  => $this->request->getPost('name'),
               'price' => $this->request->getPost('price'),
           ]);
           return redirect()->to('/products');
       }

       public function delete($id) {
           $this->productModel->delete($id);
           return redirect()->to('/products');
       }
   }
   ```
5. **Daftarkan Route** di `app/Config/Routes.php`:
   ```php
   $routes->get('products', 'Products::index');
   $routes->get('products/create', 'Products::create');
   $routes->post('products/store', 'Products::store');
   $routes->get('products/edit/(:num)', 'Products::edit/$1');
   $routes->post('products/update/(:num)', 'Products::update/$1');
   $routes->get('products/delete/(:num)', 'Products::delete/$1');
   ```
6. **Buat View** di `app/Views/products/index.php`:
   ```php
   <?php foreach ($products as $p): ?>
       <p><?= $p['name'] ?> - <?= $p['price'] ?>
       <a href="/products/edit/<?= $p['id'] ?>">Edit</a>
       <a href="/products/delete/<?= $p['id'] ?>">Hapus</a></p>
   <?php endforeach ?>
   <a href="/products/create">Tambah Produk</a>
   ```
   Buat juga `create.php` dan `edit.php` dengan form biasa (`<form method="post" action="...">`).

7. **Test**: buka `http://localhost:8080/products`

### Perintah Spark Berguna
```bash
php spark serve                  # jalankan server
php spark make:controller Nama
php spark make:model NamaModel
php spark make:migration NamaMigration
php spark migrate                # jalankan migration
php spark migrate:rollback       # rollback migration
php spark db:seed NamaSeeder     # jalankan seeder
php spark routes                 # lihat semua route
```

### Perbandingan Singkat Laravel vs CodeIgniter
| Aspek | Laravel | CodeIgniter |
|---|---|---|
| Ukuran/Kecepatan | Lebih berat | Lebih ringan & cepat |
| ORM | Eloquent (otomatis, ekspresif) | Query Builder (lebih manual) |
| Templating | Blade | PHP native |
| Fitur bawaan | Sangat lengkap (auth, queue, dll) | Minimalis, tambah manual |
| Kurva belajar | Sedang-tinggi | Lebih mudah untuk pemula |

---

## 7. Git

### Penjelasan
Git adalah **sistem version control** untuk melacak perubahan kode, memungkinkan kolaborasi banyak developer, dan menyimpan riwayat versi project. GitHub/GitLab/Bitbucket adalah platform hosting untuk repository Git.

### Istilah Penting
- **Repository (repo)**: Folder project yang dilacak Git.
- **Commit**: Snapshot perubahan kode dengan pesan penjelasan.
- **Branch**: Cabang pengembangan terpisah dari kode utama.
- **Merge**: Menggabungkan perubahan dari satu branch ke branch lain.
- **Clone**: Menyalin repository dari server (misal GitHub) ke lokal.
- **Push**: Mengirim commit lokal ke repository remote.
- **Pull**: Mengambil & menggabungkan perubahan dari remote ke lokal.
- **Fork**: Menyalin repo orang lain ke akun sendiri.
- **Pull Request (PR) / Merge Request (MR)**: Permintaan menggabungkan perubahan ke repo utama.
- **Staging Area**: Area sementara sebelum commit (`git add`).
- **HEAD**: Penunjuk ke commit/branch aktif saat ini.
- **Conflict**: Terjadi saat dua perubahan bertabrakan di file yang sama.
- **.gitignore**: File berisi daftar file/folder yang tidak ikut dilacak Git.

### Alur Kerja Dasar
```
Working Directory → (git add) → Staging Area → (git commit) → Local Repo → (git push) → Remote Repo
```

### Perintah Git Dasar
```bash
# Setup awal
git config --global user.name "Nama Kamu"
git config --global user.email "email@kamu.com"

# Membuat repo baru
git init

# Cek status file
git status

# Menambahkan file ke staging
git add nama_file
git add .              # tambahkan semua file

# Commit perubahan
git commit -m "Pesan commit"

# Menghubungkan ke remote repo (GitHub)
git remote add origin https://github.com/username/repo.git

# Push ke remote
git push -u origin main

# Clone repo
git clone https://github.com/username/repo.git

# Pull perubahan terbaru
git pull origin main

# Membuat & pindah branch
git branch nama-branch
git checkout nama-branch
git checkout -b nama-branch   # buat + pindah sekaligus

# Merge branch
git checkout main
git merge nama-branch

# Melihat riwayat commit
git log

# Melihat perbedaan file
git diff

# Membatalkan perubahan sebelum commit
git checkout -- nama_file

# Undo commit terakhir (tetap simpan perubahan)
git reset --soft HEAD~1
```

### Contoh .gitignore untuk Project Laravel/CI
```
/vendor
/node_modules
.env
/public/storage
.DS_Store
```

### Alur Kerja Tim (Umum)
1. `git clone` repo project.
2. `git checkout -b fitur-baru` — buat branch untuk fitur/tugas sendiri.
3. Coding, lalu `git add .` dan `git commit -m "keterangan"`.
4. `git push origin fitur-baru` — kirim branch ke remote.
5. Buat **Pull Request** di GitHub untuk direview tim.
6. Setelah disetujui, di-**merge** ke branch `main`/`develop`.

---

## Ringkasan Alur Belajar yang Disarankan
1. **HTML & CSS** → struktur & tampilan.
2. **JavaScript** → interaktivitas di browser.
3. **PHP dasar** → logika server-side.
4. **MySQL** → penyimpanan data.
5. **Git** → mulai dipakai sejak awal ngoding, jangan ditunda.
6. **CodeIgniter** → belajar MVC dengan cara yang lebih "manual"/mudah dipahami.
7. **Laravel** → lanjut ke framework yang lebih powerful & banyak dipakai industri.
