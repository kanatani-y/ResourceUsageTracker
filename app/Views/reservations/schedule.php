<?= $this->extend('layouts/common') ?>

<?= $this->section('title') ?>予約スケジュール（<?= esc($selectedDate) ?>）<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="mb-0"><i class="bi bi-list-check"></i> 予約スケジュール</h4>
        <!-- 日付選択 -->
        <form method="get" class="d-flex align-items-center">
            <label for="date" class="form-label me-2 mt-2">日付選択:</label>
            <input type="date" name="date" id="date" class="form-control w-auto me-2" value="<?= esc($selectedDate) ?>">
        </form>
    </div>

    <!-- テーブル -->
    <div class="table-responsive" style="max-height: 74vh; overflow-y: auto;">
        <table class="table table-bordered table-hover text-center align-middle">
            <thead class="table-light sticky-top" style="z-index: 100;">
                <tr>
                    <th colspan="2">リソース</th>
                    <th colspan="2">アカウント</th>
                    <?php for ($hour = 9; $hour <= 17; $hour++): ?>
                        <th class="bg-primary text-white"><?= $hour ?>:00</th>
                    <?php endfor; ?>
                </tr>
            </thead>
            
            <tbody>
            <?php 
            $currentUserId = auth()->user()->id;
            $isAdmin = auth()->user()->inGroup('admin');
            $isGuest = auth()->user()->inGroup('guest');
            
            foreach ($resources as $resource):
                $resourceId = $resource['id'];
                $resourceAccounts = $accounts[$resourceId] ?? [['id' => 0, 'account_name' => 'なし', 'active' => 1]];
                $firstRow = true;

                foreach ($resourceAccounts as $account):
            ?>
                <tr>
                    <?php if ($firstRow): ?>
                        <?php
                        $resourceIcons = [
                            'PC'      => '<i class="bi bi-laptop"></i>',      // PC
                            'Server'  => '<i class="bi bi-hdd-rack"></i>',   // サーバー
                            'Network' => '<i class="bi bi-router"></i>',     // ネットワーク
                            'Storage' => '<i class="bi bi-hdd"></i>',        // ストレージ
                            'Other'   => '<i class="bi bi-question-circle"></i>', // その他
                        ];
                        ?>

                        <!-- リソース名 -->
                        <td class="fw-bold text-start" rowspan="<?= count($resourceAccounts) ?>">
                            <?php if (!$isGuest): ?>
                                <a href="<?= site_url('resources/show/' . $resource['id']) ?>" class="text-decoration-none">
                                    <?= $resourceIcons[$resource['type']] ?? '<i class="bi bi-question-circle"></i>' ?> 
                                    <?= esc($resource['name']) ?>
                                </a>
                            <?php else: ?>
                                <?= $resourceIcons[$resource['type']] ?? '<i class="bi bi-question-circle"></i>' ?> 
                                <?= esc($resource['name']) ?>
                            <?php endif; ?>
                        </td>

                        <!-- リソース状態 -->
                        <td class="text-start" rowspan="<?= count($resourceAccounts) ?>">
                            <?php
                                switch ($resource['status']) {
                                    case 'available':
                                        echo '<span class="badge bg-info">利用可能</span>';
                                        break;
                                    case 'restricted':
                                        echo '<span class="badge bg-danger">利用禁止</span>';
                                        break;
                                    case 'retired':
                                        echo '<span class="badge bg-secondary">廃止</span>';
                                        break;
                                }
                            ?>
                        </td>
                    <?php endif; ?>
                    <td>
                        <?php
                        $connectionLabels = [
                            'SSH' => '<i class="bi bi-terminal"></i> ',
                            'RDP' => '<i class="bi bi-windows"></i> ',
                            'VNC' => '<i class="bi bi-display"></i> ',
                            'OTH' => '<i class="bi bi-question-circle"></i> '
                        ];
                        echo ($connectionLabels[$account['connection_type']] ?? '') . esc($account['account_name']);
                        ?>
                    </td>

                    <!-- アカウント状態 -->
                    <td class="text-start">
                        <?php if ($account['account_name'] === 'なし'): ?>
                            <span>-</span>
                        <?php elseif ($account['active'] == 1): ?>
                            <span class="badge bg-info">有効</span>
                        <?php elseif ($account['active'] == 0): ?>
                            <span class="badge bg-danger">無効</span>
                        <?php else: ?>
                            <span>-</span>
                        <?php endif; ?>
                    </td>

                    <?php
                    // **9:00～17:00 のセルを生成**
                    $hourlySlots = array_fill(9, 9, '<td class="empty-slot"></td>');

                    foreach ($reservations as $res) {
                        if ((int)$res['resource_id'] === (int)$resource['id'] && 
                            ((int)$res['account_id'] === (int)$account['id'] || ($res['account_id'] == -1 && $account['id'] == 0))) {
                            
                            $startHour = (int) date('H', strtotime($res['start_time']));
                            $endHour = (int) date('H', strtotime($res['end_time']));
                            $colspan = max(1, $endHour - $startHour);

                            $isOwnReservation = ($res['user_id'] == $currentUserId);
                            $class = $isOwnReservation ? 'bg-warning rounded shadow-sm' : 'bg-success rounded shadow-sm';

                            $userName = esc($res['user_name'] ?? '未設定');

                            $modalData = htmlspecialchars(json_encode([
                                'id' => $res['id'],
                                'resource_id' => $res['resource_id'],
                                'user_name' => $userName,
                                'resource' => esc($res['resource_name']),
                                'resource_type' => esc($res['resource_type']), 
                                'account' => esc($res['account_name']),
                                'start_time' => esc($res['start_time']),
                                'end_time' => esc($res['end_time']),
                                'purpose' => esc($res['purpose']),
                                'isOwn' => $isOwnReservation
                            ]), ENT_QUOTES, 'UTF-8');

                            $hourlySlots[$startHour] = "<td colspan='$colspan' class='$class reservation-cell text-center fw-bold'>
                                    <a href='#' class='reservation-link text-white fw-bold text-decoration-none' data-details='$modalData'>$userName</a>
                                </td>";

                            for ($i = $startHour + 1; $i < $endHour; $i++) {
                                unset($hourlySlots[$i]);
                            }
                        }
                    }

                    for ($hour = 9; $hour <= 17; $hour++) {
                        if (isset($hourlySlots[$hour]) && strpos($hourlySlots[$hour], 'reservation-cell') === false) {

                            if ($isGuest) {
                                $hourlySlots[$hour] = "<td></td>";
                            } elseif ($account['active'] == 1 && $resource['status'] === 'available') {
                                $hourlySlots[$hour] = "<td class='empty-slot' data-resource-id='" . esc($resource['id']) . "' 
                                                                        data-account-id='" . esc($account['id']) . "' 
                                                                        data-reservation-date='" . esc($selectedDate) . "' 
                                                                        data-time='" . esc($hour) . ":00'></td>";
                            } else {
                                $hourlySlots[$hour] = "<td class='text-muted'>×</td>";
                            }
                        }
                    }
                    ?>
                    <?= implode('', $hourlySlots) ?>
                </tr>
            <?php 
                $firstRow = false; 
                endforeach;
            endforeach;
            ?>
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
                    <tr>
                        <?php
                        $resourceIcons = [
                            'PC'      => '<i class="bi bi-laptop"></i>',      // PC
                            'Server'  => '<i class="bi bi-hdd-rack"></i>',   // サーバー
                            'Network' => '<i class="bi bi-router"></i>',     // ネットワーク
                            'Storage' => '<i class="bi bi-hdd"></i>',        // ストレージ
                            'Other'   => '<i class="bi bi-question-circle"></i>', // その他
                        ];
                        ?>

                        <!-- リソース名 -->
                        <th class="bg-light">リソース</th>
                        <td id="modalResource">
                            <?php if (!$isGuest): ?>
                                <a href="#" id="modalResourceLink" class="text-decoration-none fw-bold">
                                    <?= $resourceIcons[$resource['type']] ?? '<i class="bi bi-question-circle"></i>' ?> 
                                    <span id="modalResourceName"></span>
                                </a>
                            <?php else: ?>
                                <?= $resourceIcons[$resource['type']] ?? '<i class="bi bi-question-circle"></i>' ?> <span id="modalResourceName"></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr><th class="bg-light">アカウント</th><td id="modalAccount"></td></tr>
                    <tr><th class="bg-light">開始時間</th><td id="modalStartTime"></td></tr>
                    <tr><th class="bg-light">終了時間</th><td id="modalEndTime"></td></tr>
                    <tr><th class="bg-light">使用目的</th><td id="modalPurpose"></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <!-- 編集ボタン（現在のユーザーのみ表示） -->
                <a href="#" id="editReservationBtn" class="btn btn-primary d-none">
                    <i class="bi bi-pencil-square"></i> 編集
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const dateInput = document.getElementById('date');

    function fetchSchedule() {
        const selectedDate = dateInput.value;
        if (!selectedDate) return;

        // ページをリロードせずにスケジュールを更新
        window.location.href = `?date=${selectedDate}`;
    }

    dateInput.addEventListener('change', fetchSchedule);

    document.querySelectorAll('.empty-slot').forEach(slot => {
        slot.addEventListener('click', function () {
            const resourceId = this.dataset.resourceId;
            const accountId = this.dataset.accountId;
            const reservationDate = this.dataset.reservationDate;
            let time = this.dataset.time.replace(':', '-'); // `:` を `-` に置き換える

            // デバッグ用ログ
            console.log("resource_id:", resourceId, "account_id:", accountId, "reservationDate:", reservationDate, "time:", time);

            // 値が正しく取得できているかチェック
            if (!resourceId || !accountId || !reservationDate || !time) {
                alert("リソースID、アカウントID、予約日、または時間の取得に失敗しました。");
                return;
            }

            // site_url() を JavaScript に埋め込む
            const siteUrl = "<?= site_url('reservations/create') ?>";
            window.location.href = `${siteUrl}/${resourceId}/${accountId}/${reservationDate}/${time}`;
        });
    });

    document.querySelectorAll('.reservation-link').forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            let details = JSON.parse(this.getAttribute('data-details'));

            // モーダルの要素取得
            let modalUser = document.getElementById('modalUser');
            let modalAccount = document.getElementById('modalAccount');
            let modalStartTime = document.getElementById('modalStartTime');
            let modalEndTime = document.getElementById('modalEndTime');
            let modalPurpose = document.getElementById('modalPurpose');
            let modalResource = document.getElementById('modalResource');
            let modalResourceLink = document.getElementById('modalResourceLink');

            // 要素が取得できない場合は処理を中止
            if (!modalUser || !modalAccount || !modalStartTime || !modalEndTime || !modalPurpose || !modalResource) {
                console.error("モーダルの要素が見つかりません。");
                return;
            }

            // モーダルにデータをセット
            modalUser.textContent = details.user_name;
            modalAccount.textContent = details.account;
            modalStartTime.textContent = details.start_time;
            modalEndTime.textContent = details.end_time;
            modalPurpose.textContent = details.purpose;

            // リソースのアイコンを設定
            let resourceIcons = {
                'PC': '<i class="bi bi-laptop"></i>',
                'Server': '<i class="bi bi-hdd-rack"></i>',
                'Network': '<i class="bi bi-router"></i>',
                'Storage': '<i class="bi bi-hdd"></i>',
                'Other': '<i class="bi bi-question-circle"></i>'
            };

            let resourceIcon = resourceIcons[details.resource_type] || '<i class="bi bi-question-circle"></i>';
            modalResource.innerHTML = `${resourceIcon} ${details.resource}`;

            // リンクの設定（ゲストユーザー以外のみ）
            if (modalResourceLink) {
                modalResourceLink.href = "<?= site_url('resources/show') ?>/" + details.resource_id;
            }

            // 編集ボタンの表示/非表示
            let editBtn = document.getElementById('editReservationBtn');
            let isAdmin = <?= json_encode(auth()->user()->inGroup('admin')) ?>;
            if (editBtn) {
                if (isAdmin || details.isOwn) {
                    editBtn.classList.remove('d-none');
                    editBtn.href = "<?= site_url('reservations/edit') ?>/" + details.id;
                } else {
                    editBtn.classList.add('d-none');
                }
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

.table-danger {
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

.disabled-link {
    pointer-events: none;
    color: gray;
    text-decoration: none;
}
</style>

<?= $this->endSection() ?>
