<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>
ユーザー一覧
<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<!-- 必要に応じて追加のCSSをここに記述 -->
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container mt-5">
    <h1 class="mb-4">ユーザー一覧</h1>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>ユーザー名</th>
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
                    <td>
                        <?php
                        $group = match (true) {
                            in_array('admin', $user->getGroups(), true) => '管理者',
                            in_array('guest', $user->getGroups(), true) => 'ゲスト',
                            default => '一般',
                        };
                        ?>
                    </td>
                    <td><?= $user->active ? '有効' : '無効' ?></td>
                    <td><?= $user->last_active ? $user->last_active->toLocalizedString('yyyy-MM-dd HH:mm:ss') : '未アクティブ' ?></td>
                    <td>
                        <a href="<?= site_url('admin/users/edit/' . $user->id) ?>" class="btn btn-sm btn-primary">編集</a>
                        <a href="<?= site_url('admin/users/delete/' . $user->id) ?>" class="btn btn-sm btn-danger" onclick="return confirm('本当に削除しますか？')">削除</a>
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
