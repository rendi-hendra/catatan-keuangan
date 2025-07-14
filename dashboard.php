<?php   
include 'auth_check.php';
include 'config.php';

$user_id = $_SESSION['id'];
$username = $_SESSION['username'];

// ✅ Hitung total pemasukan
$stmt = $conn->prepare("SELECT SUM(jumlah) AS total FROM catatan WHERE user_id = ? AND jenis = 'pemasukan'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pemasukan = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// ✅ Hitung total pengeluaran
$stmt = $conn->prepare("SELECT SUM(jumlah) AS total FROM catatan WHERE user_id = ? AND jenis = 'pengeluaran'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pengeluaran = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// ✅ Hitung saldo
$saldo = $pemasukan - $pengeluaran;

// ✅ Hitung total transaksi (PERBAIKAN DI SINI)
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM catatan WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transaksi_result = $stmt->get_result();
$transaksi = $transaksi_result->fetch_assoc()['total'] ?? 0;

// ✅ Query grafik transaksi
$stmt = $conn->prepare("SELECT tanggal, jenis, jumlah FROM catatan WHERE user_id = ? ORDER BY tanggal ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$data_pemasukan = [];
$data_pengeluaran = [];

while ($row = $result->fetch_assoc()) {
    $tanggal = date('d M Y', strtotime($row['tanggal']));
    $labels[] = $tanggal;

    if (!isset($data_pemasukan[$tanggal])) $data_pemasukan[$tanggal] = 0;
    if (!isset($data_pengeluaran[$tanggal])) $data_pengeluaran[$tanggal] = 0;

    if ($row['jenis'] === 'pemasukan') {
        $data_pemasukan[$tanggal] += $row['jumlah'];
    } else {
        $data_pengeluaran[$tanggal] += $row['jumlah'];
    }
}

$labels = array_unique($labels);
$final_pemasukan = [];
$final_pengeluaran = [];

foreach ($labels as $tgl) {
    $final_pemasukan[] = $data_pemasukan[$tgl] ?? 0;
    $final_pengeluaran[] = $data_pengeluaran[$tgl] ?? 0;
}


?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Ringkasan Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .card-icon {
            font-size: 2rem;
            margin-right: 10px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">📊 Dashboard Keuangan</h2>
        <div>
            <span class="me-3 text-secondary">👤 <?= htmlspecialchars($username) ?></span>
            <a href="index.php" class="btn btn-outline-primary btn-sm me-2"><i class="bi bi-journal-text"></i> Catatan</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-white rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-cash-stack text-success card-icon"></i>
                    <div>
                        <h6 class="text-muted">Total Pemasukan</h6>
                        <h4 class="text-success">Rp <?= number_format($pemasukan, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-white rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-cart-dash text-danger card-icon"></i>
                    <div>
                        <h6 class="text-muted">Total Pengeluaran</h6>
                        <h4 class="text-danger">Rp <?= number_format($pengeluaran, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-white rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-wallet2 text-info card-icon"></i>
                    <div>
                        <h6 class="text-muted">Saldo Saat Ini</h6>
                        <h4 class="text-info">Rp <?= number_format($saldo, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-5 shadow-sm border-0 bg-white rounded-4">
        <div class="card-body d-flex align-items-center">
            <i class="bi bi-receipt text-primary card-icon"></i>
            <div>
                <h6 class="text-muted mb-1">Jumlah Transaksi</h6>
                <h4><?= $transaksi ?> transaksi</h4>
            </div>
        </div>
    </div>
    <div class="card mt-4 shadow-sm border-0 bg-white rounded-4">
    <div class="card-body">
        <h5 class="mb-4">📈 Grafik Transaksi Harian</h5>
        <canvas id="lineChart" height="100"></canvas>
    </div>
</div>
</div>


<script>
    const ctxLine = document.getElementById('lineChart').getContext('2d');
    const lineChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: <?= json_encode($final_pemasukan) ?>,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.2)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Pengeluaran',
                    data: <?= json_encode($final_pengeluaran) ?>,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.2)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script>


</body>
</html>
