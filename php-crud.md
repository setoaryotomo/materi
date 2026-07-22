# Tutorial Lengkap: PHP Native + MySQL — CRUD, Register, Login, Logout, Upload Gambar, Role Admin & User

Studi kasus: **Sistem Manajemen Produk**. Ada 2 role: **admin** (bisa kelola semua produk & lihat semua user) dan **user** (hanya bisa kelola produk miliknya sendiri). Semua dibuat pakai PHP native (tanpa framework) + MySQLi (prepared statements) + PDO opsional.

---

## 1. Persiapan

1. Install **XAMPP/Laragon** (Apache + MySQL + PHP 8+).
2. Aktifkan Apache & MySQL.
3. Buat folder project di `htdocs` (XAMPP) atau `www` (Laragon), misal: `sistem-produk`.

### Struktur Folder

```
sistem-produk/
│
├── config/
│   └── db.php
├── includes/
│   ├── auth.php
│   ├── header.php
│   └── footer.php
├── uploads/              <- folder upload gambar (chmod 755)
├── admin/
│   ├── dashboard.php
│   ├── users.php
│   ├── produk.php
│   ├── produk_create.php
│   ├── produk_edit.php
│   └── produk_delete.php
├── user/
│   ├── dashboard.php
│   ├── produk_create.php
│   ├── produk_edit.php
│   └── produk_delete.php
├── assets/
│   └── style.css
├── index.php
├── register.php
├── login.php
├── logout.php
└── proses_register.php / proses_login.php (opsional, bisa digabung)
```

---

## 2. Buat Database

Buka **phpMyAdmin**, buat database baru `db_sistem`, lalu jalankan SQL berikut:

```sql
CREATE DATABASE IF NOT EXISTS db_sistem;
USE db_sistem;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(12,2) NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Buat 1 akun admin default (password: admin123)
INSERT INTO users (nama, username, email, password, role)
VALUES ('Administrator', 'admin', 'admin@mail.com',
'$2y$10$92IXUNpkjO0rOQ5by4mQrOgWEc/PoBQtBFN/RQyR8UkYQxKtY3Ime', 'admin');
```

> Password hash di atas adalah hasil `password_hash('admin123', PASSWORD_DEFAULT)`. Kalau ingin generate sendiri, jalankan file PHP kecil: `<?php echo password_hash('admin123', PASSWORD_DEFAULT);`

---

## 3. Koneksi Database — `config/db.php`

```php
<?php
$host = 'localhost';
$dbname = 'db_sistem';
$user = 'root';
$pass = '';

$koneksi = new mysqli($host, $user, $pass, $dbname);

if ($koneksi->connect_error) {
    die('Koneksi database gagal: ' . $koneksi->connect_error);
}

$koneksi->set_charset('utf8mb4');
```

---

## 4. Helper Autentikasi — `includes/auth.php`

File ini dipanggil di setiap halaman yang butuh login, untuk cek session & role.

```php
<?php
session_start();

function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /sistem-produk/login.php');
        exit;
    }
}

function cekAdmin() {
    cekLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /sistem-produk/user/dashboard.php');
        exit;
    }
}

function cekUser() {
    cekLogin();
    if ($_SESSION['role'] !== 'user') {
        header('Location: /sistem-produk/admin/dashboard.php');
        exit;
    }
}
```

Sesuaikan path `/sistem-produk/` dengan nama folder project Anda.

---

## 5. Register — `register.php`

```php
<?php
require 'config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($password !== $konfirmasi) {
        $error = 'Password dan konfirmasi tidak sama.';
    } else {
        // Cek username/email sudah dipakai atau belum
        $stmt = $koneksi->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Username atau email sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $koneksi->prepare(
                'INSERT INTO users (nama, username, email, password, role) VALUES (?, ?, ?, ?, "user")'
            );
            $stmt2->bind_param('ssss', $nama, $username, $email, $hash);

            if ($stmt2->execute()) {
                header('Location: login.php?sukses=1');
                exit;
            } else {
                $error = 'Terjadi kesalahan saat menyimpan data.';
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Daftar Akun Baru</h2>
        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="nama" placeholder="Nama Lengkap" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="konfirmasi" placeholder="Konfirmasi Password" required>
            <button type="submit">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
</body>
</html>
```

**Poin penting:**
- Gunakan `password_hash()`, JANGAN pernah simpan password polos.
- Gunakan **prepared statement** (`bind_param`) untuk mencegah SQL Injection.

---

## 6. Login — `login.php`

```php
<?php
session_start();
require 'config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $koneksi->prepare('SELECT id, nama, username, password, role FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();
        if (password_verify($password, $data['password'])) {
            $_SESSION['user_id'] = $data['id'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role'] = $data['role'];

            if ($data['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: user/dashboard.php');
            }
            exit;
        } else {
            $error = 'Password salah.';
        }
    } else {
        $error = 'Username tidak ditemukan.';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <?php if (isset($_GET['sukses'])): ?>
            <p style="color:green;">Registrasi berhasil, silakan login.</p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
</body>
</html>
```

---

## 7. Logout — `logout.php`

```php
<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
```

---

## 8. Redirect Awal — `index.php`

```php
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['role'] === 'admin') {
    header('Location: admin/dashboard.php');
} else {
    header('Location: user/dashboard.php');
}
exit;
```

---

## 9. CRUD Produk — Bagian USER

### 9.1 Dashboard User (Read) — `user/dashboard.php`

```php
<?php
require '../includes/auth.php';
cekUser();
require '../config/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $koneksi->prepare('SELECT * FROM produk WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$produk = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard User</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Halo, <?= htmlspecialchars($_SESSION['nama']) ?> (User)</h2>
    <a href="../logout.php">Logout</a> |
    <a href="produk_create.php">+ Tambah Produk</a>

    <table border="1" cellpadding="8" style="width:100%; margin-top:15px;">
        <tr>
            <th>Gambar</th><th>Nama Produk</th><th>Harga</th><th>Deskripsi</th><th>Aksi</th>
        </tr>
        <?php while ($row = $produk->fetch_assoc()): ?>
        <tr>
            <td>
                <?php if ($row['gambar']): ?>
                    <img src="../uploads/<?= htmlspecialchars($row['gambar']) ?>" width="80">
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['nama_produk']) ?></td>
            <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($row['deskripsi']) ?></td>
            <td>
                <a href="produk_edit.php?id=<?= $row['id'] ?>">Edit</a> |
                <a href="produk_delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
```

### 9.2 Tambah Produk + Upload Gambar (Create) — `user/produk_create.php`

```php
<?php
require '../includes/auth.php';
cekUser();
require '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga'];
    $user_id = $_SESSION['user_id'];
    $namaFileGambar = null;

    // Proses upload gambar (opsional)
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];
        $namaAsli = $_FILES['gambar']['name'];
        $ekstensi = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));
        $ukuran = $_FILES['gambar']['size'];

        if (!in_array($ekstensi, $ekstensiValid)) {
            $error = 'Format gambar harus jpg, jpeg, png, atau webp.';
        } elseif ($ukuran > 2 * 1024 * 1024) { // maks 2MB
            $error = 'Ukuran gambar maksimal 2MB.';
        } else {
            $namaFileGambar = uniqid('produk_') . '.' . $ekstensi;
            $tujuan = '../uploads/' . $namaFileGambar;
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $tujuan)) {
                $error = 'Gagal mengupload gambar.';
            }
        }
    }

    if (!$error) {
        $stmt = $koneksi->prepare(
            'INSERT INTO produk (user_id, nama_produk, deskripsi, harga, gambar) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('issds', $user_id, $nama_produk, $deskripsi, $harga, $namaFileGambar);
        if ($stmt->execute()) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Gagal menyimpan produk.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Tambah Produk</h2>
    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="nama_produk" placeholder="Nama Produk" required><br><br>
        <textarea name="deskripsi" placeholder="Deskripsi"></textarea><br><br>
        <input type="number" step="0.01" name="harga" placeholder="Harga" required><br><br>
        <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp"><br><br>
        <button type="submit">Simpan</button>
        <a href="dashboard.php">Batal</a>
    </form>
</body>
</html>
```

> **Penting:** `enctype="multipart/form-data"` wajib ada di form supaya file ikut terkirim.

### 9.3 Edit Produk (Update) — `user/produk_edit.php`

```php
<?php
require '../includes/auth.php';
cekUser();
require '../config/db.php';

$id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil data produk, pastikan milik user yang login
$stmt = $koneksi->prepare('SELECT * FROM produk WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();

if (!$produk) {
    die('Produk tidak ditemukan atau bukan milik Anda.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga'];
    $namaFileGambar = $produk['gambar']; // default: gambar lama tetap dipakai

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));

        if (in_array($ekstensi, $ekstensiValid)) {
            // Hapus gambar lama jika ada
            if ($produk['gambar'] && file_exists('../uploads/' . $produk['gambar'])) {
                unlink('../uploads/' . $produk['gambar']);
            }
            $namaFileGambar = uniqid('produk_') . '.' . $ekstensi;
            move_uploaded_file($_FILES['gambar']['tmp_name'], '../uploads/' . $namaFileGambar);
        } else {
            $error = 'Format gambar tidak didukung.';
        }
    }

    if (!$error) {
        $stmt2 = $koneksi->prepare(
            'UPDATE produk SET nama_produk=?, deskripsi=?, harga=?, gambar=? WHERE id=? AND user_id=?'
        );
        $stmt2->bind_param('ssdsii', $nama_produk, $deskripsi, $harga, $namaFileGambar, $id, $user_id);
        $stmt2->execute();
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Edit Produk</h2>
    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="nama_produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required><br><br>
        <textarea name="deskripsi"><?= htmlspecialchars($produk['deskripsi']) ?></textarea><br><br>
        <input type="number" step="0.01" name="harga" value="<?= $produk['harga'] ?>" required><br><br>
        <?php if ($produk['gambar']): ?>
            <img src="../uploads/<?= htmlspecialchars($produk['gambar']) ?>" width="100"><br>
        <?php endif; ?>
        <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp"><br><br>
        <button type="submit">Update</button>
        <a href="dashboard.php">Batal</a>
    </form>
</body>
</html>
```

### 9.4 Hapus Produk (Delete) — `user/produk_delete.php`

```php
<?php
require '../includes/auth.php';
cekUser();
require '../config/db.php';

$id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $koneksi->prepare('SELECT gambar FROM produk WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();

if ($produk) {
    if ($produk['gambar'] && file_exists('../uploads/' . $produk['gambar'])) {
        unlink('../uploads/' . $produk['gambar']);
    }
    $stmt2 = $koneksi->prepare('DELETE FROM produk WHERE id = ? AND user_id = ?');
    $stmt2->bind_param('ii', $id, $user_id);
    $stmt2->execute();
}

header('Location: dashboard.php');
exit;
```

---

## 10. Bagian ADMIN

Admin bisa: lihat semua produk dari semua user, CRUD semua produk, dan lihat daftar semua user.

### 10.1 Dashboard Admin — `admin/dashboard.php`

```php
<?php
require '../includes/auth.php';
cekAdmin();
require '../config/db.php';

// Join ke tabel users supaya tahu produk itu milik siapa
$sql = "SELECT produk.*, users.nama AS nama_user
        FROM produk
        JOIN users ON produk.user_id = users.id
        ORDER BY produk.created_at DESC";
$produk = $koneksi->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Halo, <?= htmlspecialchars($_SESSION['nama']) ?> (Admin)</h2>
    <a href="../logout.php">Logout</a> |
    <a href="users.php">Kelola User</a> |
    <a href="produk_create.php">+ Tambah Produk</a>

    <table border="1" cellpadding="8" style="width:100%; margin-top:15px;">
        <tr>
            <th>Gambar</th><th>Nama Produk</th><th>Pemilik</th><th>Harga</th><th>Aksi</th>
        </tr>
        <?php while ($row = $produk->fetch_assoc()): ?>
        <tr>
            <td>
                <?php if ($row['gambar']): ?>
                    <img src="../uploads/<?= htmlspecialchars($row['gambar']) ?>" width="80">
                <?php else: ?>-<?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['nama_produk']) ?></td>
            <td><?= htmlspecialchars($row['nama_user']) ?></td>
            <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
            <td>
                <a href="produk_edit.php?id=<?= $row['id'] ?>">Edit</a> |
                <a href="produk_delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
```

### 10.2 Kelola User — `admin/users.php`

```php
<?php
require '../includes/auth.php';
cekAdmin();
require '../config/db.php';

// Ubah role (misal promosikan user jadi admin, atau sebaliknya)
if (isset($_GET['ubah_role'])) {
    $id = (int) $_GET['ubah_role'];
    $roleBaru = $_GET['role'] === 'admin' ? 'admin' : 'user';
    // Cegah admin mengubah role dirinya sendiri agar tidak terkunci
    if ($id !== $_SESSION['user_id']) {
        $stmt = $koneksi->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->bind_param('si', $roleBaru, $id);
        $stmt->execute();
    }
    header('Location: users.php');
    exit;
}

// Hapus user
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    if ($id !== $_SESSION['user_id']) {
        $stmt = $koneksi->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    header('Location: users.php');
    exit;
}

$users = $koneksi->query('SELECT * FROM users ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Kelola User</h2>
    <a href="dashboard.php">Kembali</a>

    <table border="1" cellpadding="8" style="width:100%; margin-top:15px;">
        <tr><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
        <?php while ($row = $users->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td>
                <?php if ($row['id'] !== $_SESSION['user_id']): ?>
                    <?php if ($row['role'] === 'user'): ?>
                        <a href="?ubah_role=<?= $row['id'] ?>&role=admin">Jadikan Admin</a>
                    <?php else: ?>
                        <a href="?ubah_role=<?= $row['id'] ?>&role=user">Jadikan User</a>
                    <?php endif; ?>
                    |
                    <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                <?php else: ?>
                    (akun Anda)
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
```

### 10.3 CRUD Produk Admin

File `admin/produk_create.php`, `admin/produk_edit.php`, `admin/produk_delete.php` **strukturnya sama** dengan versi user (poin 9.2–9.4), bedanya:

- Panggil `cekAdmin()` bukan `cekUser()`.
- Query **tidak difilter** `WHERE user_id = ?` — admin boleh edit/hapus produk siapa saja:

```php
// Contoh query admin (produk_edit.php) — tanpa filter user_id
$stmt = $koneksi->prepare('SELECT * FROM produk WHERE id = ?');
$stmt->bind_param('i', $id);
```

```php
// Saat update, admin bisa tetap pilih user_id pemilik produk lewat dropdown jika perlu
$stmt2 = $koneksi->prepare('UPDATE produk SET nama_produk=?, deskripsi=?, harga=?, gambar=? WHERE id=?');
$stmt2->bind_param('ssdsi', $nama_produk, $deskripsi, $harga, $namaFileGambar, $id);
```

Untuk `admin/produk_create.php`, tambahkan dropdown pemilik produk:

```php
<select name="user_id" required>
    <?php
    $listUser = $koneksi->query('SELECT id, nama FROM users');
    while ($u = $listUser->fetch_assoc()):
    ?>
        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nama']) ?></option>
    <?php endwhile; ?>
</select>
```

---

## 11. CSS Sederhana — `assets/style.css`

```css
body {
    font-family: Arial, sans-serif;
    margin: 40px;
    background: #f4f6f8;
}
.form-container {
    max-width: 400px;
    margin: 60px auto;
    padding: 30px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
input, textarea, select {
    width: 100%;
    padding: 10px;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}
button {
    background: #2d6cdf;
    color: #fff;
    padding: 10px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
table {
    background: #fff;
    border-collapse: collapse;
}
th {
    background: #2d6cdf;
    color: #fff;
}
```

---

## 12. Keamanan Tambahan (Wajib Dipahami)

1. **SQL Injection** → selalu pakai *prepared statement* (`bind_param`), jangan pernah menggabung variabel langsung ke query SQL.
2. **XSS** → selalu bungkus output dengan `htmlspecialchars()` saat menampilkan data ke HTML.
3. **Password** → selalu `password_hash()` saat simpan, `password_verify()` saat cek login. Jangan pernah `md5()` atau plain text.
4. **Upload File** → validasi ekstensi & ukuran file, dan sebaiknya cek juga tipe MIME asli file (`finfo_file`), bukan cuma dari nama file, supaya orang tidak bisa upload file `.php` menyamar jadi `.jpg`.
5. **Folder `uploads/`** → tambahkan file `uploads/.htaccess` berisi berikut, supaya file di folder itu tidak bisa dieksekusi sebagai PHP walau ada yang berhasil upload script jahat:
   ```
   php_flag engine off
   ```
6. **Session Hijacking** → gunakan `session_regenerate_id(true)` setelah login berhasil.
7. **CSRF** → untuk form-form penting (hapus, update), idealnya tambahkan CSRF token tersembunyi yang divalidasi saat submit.
8. **Otorisasi per baris data** → seperti contoh di atas, query `user` selalu difilter `WHERE user_id = ?` supaya user A tidak bisa edit/hapus produk milik user B lewat manipulasi URL.

---

## 13. Alur Testing

1. Jalankan `http://localhost/sistem-produk/register.php` → daftar akun baru (otomatis role `user`).
2. Login dengan akun tersebut → masuk ke `user/dashboard.php`.
3. Tambah produk + upload gambar → cek muncul di tabel & folder `uploads/`.
4. Edit & hapus produk → pastikan gambar lama ikut terhapus saat delete/replace.
5. Logout, lalu login sebagai `admin` (username: `admin`, password: `admin123`).
6. Cek `admin/dashboard.php` → semua produk dari semua user harus terlihat.
7. Cek `admin/users.php` → coba ubah role user jadi admin, dan sebaliknya.
8. Coba akses `admin/dashboard.php` saat login sebagai `user` → harus otomatis dilempar ke `user/dashboard.php` (test fungsi `cekAdmin()`).

---

Dengan struktur ini, Anda sudah punya fondasi lengkap: autentikasi (register/login/logout), otorisasi berbasis role (admin/user), CRUD data, dan upload gambar — semua native PHP + MySQL tanpa framework, sekaligus menerapkan praktik keamanan dasar yang penting untuk dipahami sebelum lanjut ke framework seperti Laravel atau CodeIgniter 4.


Kemungkinan besar penyebabnya: di tutorial sebelumnya saya **tidak memberikan kode lengkap** untuk `admin/produk_create.php` dan `admin/produk_edit.php` — saya hanya kasih cuplikan/hint ("strukturnya sama seperti versi user, tinggal hilangkan filter `user_id`"). Jadi kemungkinan filenya belum lengkap/belum dibuat, sehingga saat diklik halamannya kosong/putih atau error yang tidak tertampil (karena `display_errors` mati di XAMPP secara default).

Coba cek dulu satu hal cepat: buka `php.ini` atau tambahkan baris ini di paling atas `admin/produk_create.php` dan `admin/produk_edit.php` untuk memunculkan error tersembunyi:

```php
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Tapi supaya pasti beres, ini kode **lengkap** untuk kedua file tersebut:

### `admin/produk_create.php`

```php
<?php
require '../includes/auth.php';
cekAdmin();
require '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $deskripsi   = trim($_POST['deskripsi']);
    $harga       = $_POST['harga'];
    $user_id     = (int) $_POST['user_id'];
    $namaFileGambar = null;

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $ukuran = $_FILES['gambar']['size'];

        if (!in_array($ekstensi, $ekstensiValid)) {
            $error = 'Format gambar harus jpg, jpeg, png, atau webp.';
        } elseif ($ukuran > 2 * 1024 * 1024) {
            $error = 'Ukuran gambar maksimal 2MB.';
        } else {
            $namaFileGambar = uniqid('produk_') . '.' . $ekstensi;
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], '../uploads/' . $namaFileGambar)) {
                $error = 'Gagal mengupload gambar.';
            }
        }
    }

    if (!$error) {
        $stmt = $koneksi->prepare(
            'INSERT INTO produk (user_id, nama_produk, deskripsi, harga, gambar) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('issds', $user_id, $nama_produk, $deskripsi, $harga, $namaFileGambar);
        if ($stmt->execute()) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Gagal menyimpan produk: ' . $stmt->error;
        }
    }
}

$listUser = $koneksi->query('SELECT id, nama FROM users ORDER BY nama');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk (Admin)</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Tambah Produk (Admin)</h2>
    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Pemilik Produk</label>
        <select name="user_id" required>
            <?php while ($u = $listUser->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nama']) ?></option>
            <?php endwhile; ?>
        </select>

        <input type="text" name="nama_produk" placeholder="Nama Produk" required>
        <textarea name="deskripsi" placeholder="Deskripsi"></textarea>
        <input type="number" step="0.01" name="harga" placeholder="Harga" required>
        <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp">
        <button type="submit">Simpan</button>
        <a href="dashboard.php">Batal</a>
    </form>
</body>
</html>
```

### `admin/produk_edit.php`

```php
<?php
require '../includes/auth.php';
cekAdmin();
require '../config/db.php';

$id = (int) $_GET['id'];

$stmt = $koneksi->prepare('SELECT * FROM produk WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();

if (!$produk) {
    die('Produk tidak ditemukan.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $deskripsi   = trim($_POST['deskripsi']);
    $harga       = $_POST['harga'];
    $user_id     = (int) $_POST['user_id'];
    $namaFileGambar = $produk['gambar'];

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));

        if (in_array($ekstensi, $ekstensiValid)) {
            if ($produk['gambar'] && file_exists('../uploads/' . $produk['gambar'])) {
                unlink('../uploads/' . $produk['gambar']);
            }
            $namaFileGambar = uniqid('produk_') . '.' . $ekstensi;
            move_uploaded_file($_FILES['gambar']['tmp_name'], '../uploads/' . $namaFileGambar);
        } else {
            $error = 'Format gambar tidak didukung.';
        }
    }

    if (!$error) {
        $stmt2 = $koneksi->prepare(
            'UPDATE produk SET user_id=?, nama_produk=?, deskripsi=?, harga=?, gambar=? WHERE id=?'
        );
        $stmt2->bind_param('issdsi', $user_id, $nama_produk, $deskripsi, $harga, $namaFileGambar, $id);
        $stmt2->execute();
        header('Location: dashboard.php');
        exit;
    }
}

$listUser = $koneksi->query('SELECT id, nama FROM users ORDER BY nama');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk (Admin)</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Edit Produk (Admin)</h2>
    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Pemilik Produk</label>
        <select name="user_id" required>
            <?php while ($u = $listUser->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>" <?= $u['id'] == $produk['user_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['nama']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <input type="text" name="nama_produk" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
        <textarea name="deskripsi"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
        <input type="number" step="0.01" name="harga" value="<?= $produk['harga'] ?>" required>
        <?php if ($produk['gambar']): ?>
            <img src="../uploads/<?= htmlspecialchars($produk['gambar']) ?>" width="100"><br>
        <?php endif; ?>
        <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp">
        <button type="submit">Update</button>
        <a href="dashboard.php">Batal</a>
    </form>
</body>
</html>
```

**Kalau setelah pakai kode di atas masih tetap "tidak terjadi apa-apa"** (halaman putih, tidak error, tidak pindah), tolong cek dan kasih tahu saya:
1. Apakah file `admin/produk_create.php` dan `admin/produk_edit.php` memang sudah ada di folder itu?
2. Coba buka langsung via URL `localhost/sistem-produk/admin/produk_create.php` — apa yang muncul (kosong / error / form tampil)?
3. Aktifkan `ini_set('display_errors', 1);` di baris atas file, lalu screenshot errornya kalau ada.
