<?php
include 'auth_check.php';
include 'config.php';

if (!isset($_SESSION['id'])) {
    die("Session ID tidak ditemukan. Silakan login ulang.");
}

$user_id = $_SESSION['id'];

// ✅ Persiapkan dan ambil hasil
$stmt = $conn->prepare("SELECT * FROM catatan WHERE user_id = ? ORDER BY tanggal DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$data_result = $stmt->get_result(); // ✅ Wajib

$saldo = 0;
$data = [];

while ($row = $data_result->fetch_assoc()) {
    $data[] = $row;
    $saldo += ($row['jenis'] == 'pemasukan') ? $row['jumlah'] : -$row['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Catatan Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4 text-center">📒 Catatan Keuangan Sederhana</h2>

    <div class="card mb-4 shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary">📒 Catatan Keuangan</h2>
    <div>
        <span class="me-3 text-muted">👤 <?= $_SESSION['username'] ?></span>
        <a href="dashboard.php" class="btn btn-outline-primary btn-sm">Dashboard</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>

    </div>
</div>

            <form method="POST" action="tambah.php" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="keterangan" class="form-control" placeholder="Keterangan" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="jumlah" class="form-control" placeholder="Jumlah (Rp)" required>
                </div>
                <div class="col-md-3">
                    <select name="jenis" class="form-select" required>
                        <option value="pemasukan">Pemasukan</option>
                        <option value="pengeluaran">Pengeluaran</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <div class="alert alert-info text-center fs-5">
        Saldo Saat Ini: <strong>Rp <?= number_format($saldo, 0, ',', '.') ?></strong>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($data) > 0): ?>
                        <?php foreach ($data as $d): ?>
                        <tr>
                            <td><?= $d['tanggal'] ?></td>
                            <td><?= htmlspecialchars($d['keterangan']) ?></td>
                            <td>
                                <span class="badge bg-<?= $d['jenis'] == 'pemasukan' ? 'success' : 'danger' ?>">
                                    <?= $d['jenis'] ?>
                                </span>
                            </td>
                            <td>Rp <?= number_format($d['jumlah'], 0, ',', '.') ?></td>
                            <td>
                                <a href="hapus.php?id=<?= $d['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                                   Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">Belum ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
