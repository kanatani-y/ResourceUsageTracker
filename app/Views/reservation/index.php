<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?>予約状況<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <h4 class="mb-3"><i class="bi bi-calendar-check"></i> 予約状況</h4>

    <!-- 日付選択 -->
    <form method="get" action="<?= route_to('reservation.by_date', $selectedDate ?? date('Y-m-d')) ?>" class="mb-3">
        <div class="row">
            <div class="col-auto">
                <label for="reservation_date" class="form-label">日付選択:</label>
                <input type="date" id="reservation_date" name="date" class="form-control" value="<?= $selectedDate ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary mt-4">表示</button>
            </div>
        </div>
    </form>

    <!-- 予約一覧 -->
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>リソース</th>
                <th>利用者</th>
                <th>開始時刻</th>
                <th>終了時刻</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reservations)): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">この日の予約はありません。</td>
                </tr>
            <?php else: ?>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?= esc($reservation['resource_name']) ?></td>
                        <td><?= esc($reservation['user_name']) ?></td>
                        <td><?= date('H:i', strtotime($reservation['start_time'])) ?></td>
                        <td><?= date('H:i', strtotime($reservation['end_time'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="d-flex justify-content-between mt-3">
        <a href="<?= route_to('reservation.create') ?>" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> 新規予約
        </a>
    </div>
</div>

<script>
    document.getElementById('reservation_date').addEventListener('change', function() {
        const selectedDate = this.value;
        if (selectedDate) {
            window.location.href = "<?= route_to('reservation.by_date', 'PLACEHOLDER') ?>".replace('PLACEHOLDER', encodeURIComponent(selectedDate));
        }
    });
</script>
<?= $this->endSection() ?>
