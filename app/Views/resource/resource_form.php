<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?><?= isset($resource) ? 'リソース編集' : '新規リソース登録' ?><?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="container d-flex justify-content-center p-2">
        <div class="card col-12 col-md-6 shadow-sm">
            <div class="card-body">
                <h4 class="card-title mb-4"><?= isset($resource) ? 'リソース編集' : '新規リソース登録' ?></h4>

                <form action="<?= isset($resource) ? route_to('resource.update', $resource['id']) : route_to('resource.store') ?>" method="post">
                    <?= csrf_field() ?>

                    <!-- リソース名 -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" value="<?= old('name', $resource['name'] ?? '') ?>" required>
                        <label for="name">リソース名</label>
                    </div>

                    <!-- 種類 -->
                    <div class="form-floating mb-3">
                        <select class="form-select" id="type" name="type" required>
                            <option value="PC" <?= old('type', $resource['type'] ?? '') === 'PC' ? 'selected' : '' ?>>PC</option>
                            <option value="Server" <?= old('type', $resource['type'] ?? '') === 'Server' ? 'selected' : '' ?>>Server</option>
                            <option value="Network" <?= old('type', $resource['type'] ?? '') === 'Network' ? 'selected' : '' ?>>Network</option>
                            <option value="Storage" <?= old('type', $resource['type'] ?? '') === 'Storage' ? 'selected' : '' ?>>Storage</option>
                            <option value="Other" <?= old('type', $resource['type'] ?? 'Other') === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                        <label for="type">種類</label>
                    </div>

                    <!-- OS -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="os" name="os" value="<?= old('os', $resource['os'] ?? '') ?>">
                        <label for="os">OS</label>
                    </div>

                    <!-- IPアドレス -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="ip_address" name="ip_address" value="<?= old('ip_address', $resource['ip_address'] ?? '') ?>">
                        <label for="ip_address">IPアドレス</label>
                    </div>

                    <!-- MACアドレス -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="mac_address" name="mac_address" value="<?= old('mac_address', $resource['mac_address'] ?? '') ?>">
                        <label for="mac_address">MACアドレス</label>
                    </div>

                    <!-- CPU -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="cpu" name="cpu" value="<?= old('cpu', $resource['cpu'] ?? '') ?>">
                        <label for="cpu">CPU</label>
                    </div>

                    <!-- メモリ -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="memory" name="memory" value="<?= old('memory', $resource['memory'] ?? '') ?>">
                        <label for="memory">メモリ</label>
                    </div>

                    <!-- ストレージ -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="storage" name="storage" value="<?= old('storage', $resource['storage'] ?? '') ?>">
                        <label for="storage">ストレージ</label>
                    </div>

                    <!-- ステータス -->
                    <div class="form-floating mb-3">
                        <select class="form-select" id="status" name="status" required>
                            <option value="available" <?= old('status', $resource['status'] ?? '') === 'available' ? 'selected' : '' ?>>利用可能</option>
                            <option value="in_use" <?= old('status', $resource['status'] ?? '') === 'in_use' ? 'selected' : '' ?>>使用中</option>
                            <option value="maintenance" <?= old('status', $resource['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>メンテナンス中</option>
                            <option value="retired" <?= old('status', $resource['status'] ?? '') === 'retired' ? 'selected' : '' ?>>廃止</option>
                        </select>
                        <label for="status">ステータス</label>
                    </div>

                    <!-- 設置場所 -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="location" name="location" value="<?= old('location', $resource['location'] ?? '') ?>">
                        <label for="location">設置場所</label>
                    </div>

                    <!-- 説明 -->
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="description" name="description" style="height: 100px"><?= old('description', $resource['description'] ?? '') ?></textarea>
                        <label for="description">説明</label>
                    </div>

                    <!-- ボタン -->
                    <div class="d-flex justify-content-between col-12 mx-auto mt-3">
                        <a href="<?= site_url('resource') ?>" class="btn btn-light">戻る</a>
                        <button type="submit" class="btn btn-primary"><?= isset($resource) ? '更新' : '登録' ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
