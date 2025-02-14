<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?>アカウント管理<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="mb-0">
            <?= !empty($resource) ? esc($resource['name']) . ' のアカウント一覧' : '全アカウント一覧' ?>
        </h4>
        <a href="<?= isset($resource) ? route_to('account.create', $resource['id']) : route_to('account.create_no_resource') ?>" class="btn btn-sm btn-success">
            <i class="bi bi-plus-lg"></i> 追加
        </a>
    </div>
    
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>リソース</th>
                <th>ユーザー名</th>
                <th>接続方式</th>
                <th>ポート</th>
                <th>備考</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $account) : ?>
                <tr class="<?= $account['deleted_at'] ? 'text-muted' : '' ?>">
                    <td>
                        <a href="<?= route_to('resource.show', $account['resource_id']) ?>" class="text-decoration-none">
                            <i class="bi bi-server"></i> <?= esc($account['resource_name'] ?? '不明') ?>
                        </a>
                    </td>
                    <td><?= esc($account['username']) ?></td>
                    <td><?= esc($account['connection_type']) ?></td>
                    <td><?= $account['port'] == -1 ? '-' : esc($account['port']) ?></td>
                    <td><?= esc($account['description'] ?? '-') ?></td>
                    <td>
                        <form action="<?= route_to('account.delete', $account['id']) ?>" method="post" onsubmit="return confirm('本当に削除しますか？');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i> 削除
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
