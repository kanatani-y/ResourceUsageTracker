<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?>
ユーザー一覧
<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<!-- 必要に応じて追加のCSSをここに記述 -->
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="mb-0">ユーザー一覧</h4>
        <a href="<?= route_to('admin.register') ?>" class="btn btn-sm btn-success float-end">
            <i class="bi bi-plus-lg"></i> 追加
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped mt-2">
        <thead>
            <tr>
                <th>ID</th>
                <th>ユーザー名</th>
                <th>氏名</th>
                <th>種別</th>
                <th>ステータス</th>
                <th>最終アクティブ日時</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= esc($user->id) ?></td>
                    <td><?= esc($user->username) ?></td>
                    <td><?= esc($user->fullname ?? '-') ?></td>
                    <td>
                    <?php 
                    $groups = $user->getGroups() ?? [];
                    $groupLabel = '一般';

                    if (in_array('admin', $groups, true)) {
                        $groupLabel = '管理者';
                    } elseif (in_array('guest', $groups, true)) {
                        $groupLabel = 'ゲスト';
                    }
                    
                    echo esc($groupLabel);
                    ?>
                    </td>
                    <td><?= $user->active ? '有効' : '無効' ?></td>
                    <td><?= $user->last_active ? $user->last_active->toLocalizedString('yyyy-MM-dd HH:mm:ss') : '未アクティブ' ?></td>
                    <td class="text-nowrap" style="width: 150px;">
                        <div class="btn-group" role="group">
                            
                            <?php if ($user->username !== 'admin' && $user->id !== auth()->user()->id): ?>
                                <a href="<?= site_url('admin/users/edit/' . $user->id) ?>" class="btn btn-sm btn-primary">編集</a>
                                <a href="<?= site_url('admin/users/delete/' . $user->id) ?>" class="btn btn-sm btn-danger" onclick="return confirm('本当に削除しますか？')">削除</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<!-- 必要に応じて追加のJavaScriptをここに記述 -->
<?= $this->endSection() ?>
