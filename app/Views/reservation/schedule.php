<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?>予約スケジュール（<?= esc($selectedDate) ?>）<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <h3 class="mb-3">予約スケジュール（<?= esc($selectedDate) ?>）</h3>

    <!-- 日付選択 -->
    <form method="get" class="mb-3">
        <label for="date" class="form-label">日付選択:</label>
        <input type="date" name="date" id="date" class="form-control d-inline-block w-auto" value="<?= esc($selectedDate) ?>">
        <button type="submit" class="btn btn-primary">表示</button>
    </form>

    <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
            <tr>
                <th>リソース</th>
                <th>アカウント</th>
                <?php for ($hour = 9; $hour <= 18; $hour++): ?>
                    <th><?= $hour ?>:00</th>
                <?php endfor; ?>
            </tr>
        </thead>
        
        <tbody>
            <?php 
            $currentUserId = auth()->user()->id; // 現在のログインユーザーの ID
            foreach ($resources as $resource): 
                $resourceAccounts = isset($accounts[$resource['id']]) ? $accounts[$resource['id']] : [['id' => 0, 'username' => 'なし']];
                foreach ($resourceAccounts as $account):
            ?>
                <tr>
                    <td><?= esc($resource['name']) ?></td>
                    <td><?= esc($account['username']) ?></td>
                    <?php
                    $hourlySlots = array_fill(9, 10, '<td></td>'); // 9:00～18:00の枠を作成
                    
                    foreach ($reservations as $res) {
                        if ((int)$res['resource_id'] === (int)$resource['id'] && (int)$res['account_id'] === (int)$account['id']) {
                            $startHour = (int) date('H', strtotime($res['start_time']));
                            $endHour = (int) date('H', strtotime($res['end_time']));
                            $colspan = max(1, $endHour - $startHour);

                            // 予約者が現在のログインユーザーかどうかで色を変更
                            $isOwnReservation = ($res['user_id'] == $currentUserId);
                            $class = $isOwnReservation ? 'table-warning' : 'table-success';

                            // 予約詳細をモーダルで表示するリンク
                            $userName = esc($res['user_name'] ?? '未設定');

                            $modalData = htmlspecialchars(json_encode([
                                'id' => $res['id'],
                                'user_name' => $userName,
                                'resource' => esc($res['resource_name']),
                                'account' => esc($res['account_name']),
                                'start_time' => esc($res['start_time']),
                                'end_time' => esc($res['end_time']),
                                'purpose' => esc($res['purpose']),
                                'isOwn' => $isOwnReservation
                            ]), ENT_QUOTES, 'UTF-8');

                            $hourlySlots[$startHour] = "<td colspan='$colspan' class='$class text-white text-center'>
                                    <a href='#' class='reservation-link text-white fw-bold' data-details='$modalData'>$userName</a>
                                </td>";

                            // 予約済み時間帯のセルをスキップ
                            for ($i = $startHour + 1; $i < $endHour; $i++) {
                                unset($hourlySlots[$i]);
                            }
                        }
                    }
                    ?>
                    <?= implode('', $hourlySlots) ?>
                </tr>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- 予約詳細モーダル -->
<div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="reservationModalLabel"><i class="bi bi-calendar-check"></i> 予約詳細</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr><th class="bg-light">予約者</th><td id="modalUser"></td></tr>
                    <tr><th class="bg-light">リソース</th><td id="modalResource"></td></tr>
                    <tr><th class="bg-light">アカウント</th><td id="modalAccount"></td></tr>
                    <tr><th class="bg-light">開始時間</th><td id="modalStartTime"></td></tr>
                    <tr><th class="bg-light">終了時間</th><td id="modalEndTime"></td></tr>
                    <tr><th class="bg-light">使用目的</th><td id="modalPurpose"></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <!-- 編集ボタン（現在のユーザーのみ表示） -->
                <a href="#" id="editReservationBtn" class="btn btn-warning d-none">
                    <i class="bi bi-pencil-square"></i> 編集
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.reservation-link').forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            let details = JSON.parse(this.getAttribute('data-details'));

            // モーダルにデータをセット
            document.getElementById('modalUser').textContent = details.user_name;
            document.getElementById('modalResource').textContent = details.resource;
            document.getElementById('modalAccount').textContent = details.account;
            document.getElementById('modalStartTime').textContent = details.start_time;
            document.getElementById('modalEndTime').textContent = details.end_time;
            document.getElementById('modalPurpose').textContent = details.purpose;

            // 編集ボタンの表示/非表示
            let editBtn = document.getElementById('editReservationBtn');
            if (details.isOwn) {
                editBtn.classList.remove('d-none');
                editBtn.href = "<?= site_url('reservation/edit') ?>/" + details.id;
            } else {
                editBtn.classList.add('d-none');
            }

            // モーダルを表示
            let reservationModal = new bootstrap.Modal(document.getElementById('reservationModal'));
            reservationModal.show();
        });
    });
});
</script>

<style>
    .table-success {
        background-color: #28a745 !important;
        color: white;
        font-weight: bold;
    }

    .table-warning {
        background-color: #ffc107 !important;
        color: black;
        font-weight: bold;
    }
</style>

<?= $this->endSection() ?>
