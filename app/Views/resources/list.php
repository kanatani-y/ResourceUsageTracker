<?= $this->extend('layouts/common') ?>

<?= $this->section('title') ?>リソース管理<?= $this->endSection() ?>

<?= $this->section('main') ?>
    <?php $authUser = auth()->user(); ?>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="mb-0"><i class="bi bi-server"></i> リソース一覧</h4>
            <?php if ($authUser->inGroup('admin')): ?>
                <a href="<?= site_url('resources/create') ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-lg"></i> 追加
                </a>
            <?php endif; ?>
        </div>

        <div class="table-responsive" style="max-height: 74vh; overflow-y: auto;">
            <table class="table table-bordered table-hover">
                <thead class="table-light text-center sticky-top" style="z-index: 100;">
                    <tr>
                        <th>ID</th>
                        <th>リソース名</th>
                        <th>ホスト名</th>
                        <th>IPアドレス</th>
                        <th>種類</th>
                        <th>状態</th>
                        <?php if ($authUser->inGroup('admin')): ?>
                            <th>操作</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resources as $resource) : ?>
                        <tr class="<?= $resource['deleted_at'] ? 'text-muted' : '' ?>">
                            <td><?= $resource['id'] ?></td>
                            <td class="text-decoration-none fw-bold">
                                <a href="<?= site_url('resources/show/' . $resource['id']) ?>" class="text-decoration-none">
                                    <?= esc($resource['name']) ?>
                                </a>
                            </td>
                            <td><?= esc($resource['hostname']) ?></td>
                            <td><?= esc($resource['ip_address']) ?></td>
                            <td>
                                <?php
                                $resourceIcons = [
                                    'PC'      => '<i class="bi bi-laptop"></i>',      // PC
                                    'Server'  => '<i class="bi bi-hdd-rack"></i>',   // サーバー
                                    'Network' => '<i class="bi bi-router"></i>',     // ネットワーク
                                    'Storage' => '<i class="bi bi-hdd"></i>',        // ストレージ
                                    'Other'   => '<i class="bi bi-question-circle"></i>', // その他
                                ];
                                echo $resourceIcons[$resource['type']] ?? '<i class="bi bi-question-circle"></i>';
                                ?>
                                <?= esc($resource['type']) ?>
                            </td>
                            <td>
                                <?php if ($resource['deleted_at']) : ?>
                                    <span class="badge bg-secondary">削除済</span>
                                <?php else : ?>
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
                                <?php endif; ?>
                            </td>
                            <?php if ($authUser->inGroup('admin')): ?>
                            <td class="d-flex gap-2">
                                <div>
                                    <?php if ($resource['deleted_at']) : ?>
                                        <!-- 復元リンク（モーダルで確認） -->
                                        <a href="#" class="btn btn-sm btn-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmModal"
                                            data-method="get"
                                            data-action="<?= site_url('resources/restore/' . $resource['id']) ?>"
                                            data-title="復元確認"
                                            data-message="対象を復元します。よろしいですか？">
                                            <i class="bi bi-arrow-counterclockwise"></i> 復元
                                        </a>

                                    <?php else : ?>
                                        <a href="<?= site_url('resources/edit/' . $resource['id']) ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil-square"></i> 編集
                                        </a>
                                        <?php if ($resource['status'] === 'retired') : ?>
                                            <form action="<?= site_url('resources/delete/' . $resource['id']) ?>" method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="button" class="btn btn-sm btn-danger <?= $resource['deleted_at'] ? 'disabled' : '' ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmModal"
                                                    data-action="<?= site_url('resources/delete/' . $resource['id']) ?>"
                                                    data-title="削除確認"
                                                    data-message="本当にこのリソースを削除しますか？">
                                                    <i class="bi bi-trash"></i> 削除
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>
