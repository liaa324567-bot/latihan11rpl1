<?php
require_once 'edit_mengajar.php';
require_once '../navbar.php';
?>

<main class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h1 class="h4 mb-0">Edit Mengajar</h1>
                </div>
                <div class="card-body">
                    <?php if ($pesanError !== '') { ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($pesanError, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php } ?>

                    <form method="post" action="edit_mengajar.php">
                        <input type="hidden" name="mapel_id_lama" value="<?= (int) $mapelLamaId ?>">
                        <input type="hidden" name="kelas_lama" value="<?= htmlspecialchars($kelasLama, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3">
                            <label for="kelas" class="form-label">Kelas</label>
                            <select class="form-select" id="kelas" name="kelas" required>
                                <?php while ($barisKelas = mysqli_fetch_assoc($queryKelas)) { ?>
                                    <option value="<?= htmlspecialchars($barisKelas['kelas'], ENT_QUOTES, 'UTF-8') ?>" <?= ($kelas ?? '') === $barisKelas['kelas'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($barisKelas['kelas'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="mapel_id" class="form-label">Mata Pelajaran</label>
                            <select class="form-select" id="mapel_id" name="mapel_id" required>
                                <?php while ($mapel = mysqli_fetch_assoc($queryMapel)) { ?>
                                    <option value="<?= (int) $mapel['id'] ?>" <?= (int) ($mapelId ?? 0) === (int) $mapel['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mapel['nama_mapel'], ENT_QUOTES, 'UTF-8') ?><?= $mapel['nama_guru'] ? ' - ' . htmlspecialchars($mapel['nama_guru'], ENT_QUOTES, 'UTF-8') : '' ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="daftar_mengajar.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../footer.php'; ?>