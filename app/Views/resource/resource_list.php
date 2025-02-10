<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?>リソース管理<?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="mb-0">リソース管理</h4>
            <a href="<?= route_to('resource.create') ?>" class="btn btn-sm btn-success">
                <i class="bi bi-plus-lg"></i> 追加
            </a>
        </div>

        <?php if (session('message')) : ?>
            <div class="alert alert-success"><?= session('message') ?></div>
        <?php endif ?>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>リソース名</th>
                    <th>種類</th>
                    <th>状態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resources as $resource) : ?>
                    <tr>
                        <td><?= $resource['id'] ?></td>
                        <td><?= esc($resource['name']) ?></td>
                        <td><?= esc($resource['type']) ?></td>
                        <td>
                            <?php
                            switch ($resource['status']) {
                                case 'available':
                                    echo '<span class="badge bg-success">利用可能</span>';
                                    break;
                                case 'in_use':
                                    echo '<span class="badge bg-warning">使用中</span>';
                                    break;
                                case 'maintenance':
                                    echo '<span class="badge bg-info">メンテナンス中</span>';
                                    break;
                                case 'retired':
                                    echo '<span class="badge bg-secondary">廃止</span>';
                                    break;
                            }
                            ?>
                        </td>
                        <td class="d-flex gap-2">
                            <a href="<?= route_to('resource.edit', $resource['id']) ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil-square"></i> 編集
                            </a>
                            <form action="<?= route_to('resource.delete', $resource['id']) ?>" method="post" onsubmit="return confirm('本当に削除しますか？');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> 削除
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?= $this->endSection() ?>
