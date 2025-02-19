<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?>予約スケジュール（<?= esc($selectedDate) ?>）<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <h3 class="mb-3">予約スケジュール（<?= esc($selectedDate) ?>）</h3>

    <!-- 日付選択 -->
    <form method="get" class="mb-3 d-flex align-items-center">
        <label for="date" class="form-label me-2">日付選択:</label>
        <input type="date" name="date" id="date" class="form-control w-auto me-2" value="<?= esc($selectedDate) ?>">
        <button type="submit" class="btn btn-primary">表示</button>
    </form>

    <!-- テーブルをスクロール可能にする -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover text-center align-middle">
            <thead class="table-light sticky-top">
                <tr>
                    <th class="bg-primary text-white">リソース</th>
                    <th class="bg-primary text-white">アカウント</th>
                    <?php for ($hour = 9; $hour <= 18; $hour++): ?>
                        <th class="bg-primary text-white"><?= $hour ?>:00</th>
                    <?php endfor; ?>
                </tr>
            </thead>
            
            <tbody>
            <?php 
            $currentUserId = auth()->user()->id;
            foreach ($resources as $resource): 
                $resourceAccounts = $accounts[$resource['id']] ?? [['id' => 0, 'username' => 'なし']];
                foreach ($resourceAccounts as $account):
            ?>
                <tr>
                    <td class="fw-bold"><?= esc($resource['name']) ?></td>
                    <td><?= esc($account['username']) ?></td>
                    <?php
                    // 9:00～18:00 のセルを生成（10個）
                    $hourlySlots = array_fill(9, 10, '<td class="empty-slot"></td>');

                    foreach ($reservations as $res) {
                        if ((int)$res['resource_id'] === (int)$resource['id'] && 
                            ((int)$res['account_id'] === (int)$account['id'] || ($res['account_id'] == -1 && $account['id'] == 0))) {
                    
                            $startHour = (int) date('H', strtotime($res['start_time']));
                            $endHour = (int) date('H', strtotime($res['end_time']));
                            $colspan = max(1, $endHour - $startHour);

                            $isOwnReservation = ($res['user_id'] == $currentUserId);
                            $class = $isOwnReservation ? 'bg-warning text-dark rounded shadow-sm' : 'bg-success text-white rounded shadow-sm';

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

                            $hourlySlots[$startHour] = "<td colspan='$colspan' class='$class reservation-cell text-center fw-bold'>
                                    <a href='#' class='reservation-link text-black fw-bold' data-details='$modalData'>$userName</a>
                                </td>";

                            // 予約済みの時間帯のセルを削除
                            for ($i = $startHour + 1; $i < $endHour; $i++) {
                                unset($hourlySlots[$i]);
                            }
                        }
                    }
                    // 予定のないセルに `data-*` 属性を適切に設定
                    for ($hour = 9; $hour <= 18; $hour++) {
                        if (isset($hourlySlots[$hour]) && strpos($hourlySlots[$hour], 'reservation-cell') === false) {
                            $hourlySlots[$hour] = "<td class='empty-slot' data-resource-id='" . esc($resource['id']) . "' 
                                                                    data-account-id='" . esc($account['id']) . "' 
                                                                    data-time='" . esc($hour) . ":00'></td>";
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
    document.querySelectorAll('.empty-slot').forEach(slot => {
        slot.addEventListener('click', function () {
            const resourceId = this.dataset.resourceId;
            const accountId = this.dataset.accountId;
            let time = this.dataset.time.replace(':', '-'); // `:` を `-` に置き換える

            // デバッグ用ログ
            console.log("resource_id:", resourceId, "account_id:", accountId, "time:", time);

            // 値が正しく取得できているかチェック
            if (!resourceId || !accountId || !time) {
                alert("リソースID、アカウントID、または時間の取得に失敗しました。");
                return;
            }

            // 予約登録ページに遷移
            window.location.href = `/reservation/create/${resourceId}/${accountId}/${time}`;
        });
    });

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

/* 予定のないセル */
.empty-slot {
    cursor: pointer;
    position: relative;
    background-color: #ffffff;
    transition: background-color 0.2s;
}

/* ホバー時に `+` アイコンを表示 */
.empty-slot:hover::before {
    content: "+";
    font-size: 20px;
    font-weight: bold;
    color: #007bff;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
</style>

<?= $this->endSection() ?>
