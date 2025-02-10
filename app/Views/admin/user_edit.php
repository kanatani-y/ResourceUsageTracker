<?= $this->extend('layouts/layout') ?>

<?= $this->section('title') ?>ユーザー編集<?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="container d-flex justify-content-center p-2">
        <div class="card col-12 col-md-5 shadow-sm">
            <div class="card-body">
                <h4 class="card-title mb-2">ユーザー編集</h4>

                <?php if (session('error') !== null) : ?>
                    <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
                <?php elseif (session('errors') !== null) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?php if (is_array(session('errors'))) : ?>
                            <?php foreach (session('errors') as $error) : ?>
                                <?= $error ?><br>
                            <?php endforeach ?>
                        <?php else : ?>
                            <?= session('errors') ?>
                        <?php endif ?>
                    </div>
                <?php endif ?>

                <form action="<?= route_to('admin.users.update', $user->id) ?>" method="post">
                    <?= csrf_field() ?>

                    <!-- Username -->
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="floatingUsernameInput" name="username" inputmode="text" autocomplete="username" placeholder="ユーザー名" value="<?= old('username', $user->username) ?>" required>
                        <label for="floatingUsernameInput">ユーザー名</label>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="floatingfullnameInput" name="fullname" inputmode="text" autocomplete="fullname" placeholder="氏名" value="<?= old('fullname', $user->fullname) ?>" required>
                        <label for="floatingfullnameInput">氏名</label>
                    </div>

                    <!-- Password -->
                    <div class="form-floating mb-2">
                        <input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text" autocomplete="new-password" placeholder="新しいパスワード">
                        <label for="floatingPasswordInput">新しいパスワード（変更する場合のみ）</label>
                    </div>

                    <!-- Role Selection -->
                    <div class="form-floating mb-4">
                        <select class="form-control" id="floatingRoleSelect" name="role" <?= ($user->username === 'admin') ? 'disabled' : '' ?> required>
                            <option value="user" <?= $user->inGroup('user') ? 'selected' : '' ?>>一般ユーザー</option>
                            <option value="admin" <?= $user->inGroup('admin') ? 'selected' : '' ?>>管理者</option>
                            <option value="guest" <?= $user->inGroup('guest') ? 'selected' : '' ?>>ゲスト</option>
                        </select>
                        <label for="floatingRoleSelect">役割を選択</label>
                    </div>

                    <!-- Active Status Selection -->
                    <div class="mb-4">
                        <label class="form-label">アカウント状態</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="active" id="activeYes" value="1" <?= $user->active ? 'checked' : '' ?> <?= ($user->username === 'admin') ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="activeYes">
                                有効
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="active" id="activeNo" value="0" <?= $user->active ? '' : 'checked' ?> <?= ($user->username === 'admin') ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="activeNo">
                                無効
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between col-12 col-md-8 mx-auto m-3">
                        <a href="<?= site_url('admin/users') ?>" class="btn btn-light" onclick="return confirm('戻りますがよろしいですか？')">戻る</a>
                        <button type="submit" class="btn btn-primary">更新</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
