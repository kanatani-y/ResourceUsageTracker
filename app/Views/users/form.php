<?= $this->extend('layouts/common') ?>

<?= $this->section('title') ?><?= isset($user) ? 'ユーザー編集' : 'ユーザー登録' ?><?= $this->endSection() ?>

<?= $this->section('main') ?>
<?php $loggedInUser = auth()->user(); ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi <?= isset($user) ? 'bi-pencil-square' : 'bi-person-plus' ?>"></i>
                        <?= isset($user) ? 'ユーザー編集' : 'ユーザー登録' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (session('errors')) : ?>
                        <div class="alert alert-danger">
                            <?php foreach (session('errors') as $error) : ?>
                                <div><?= esc($error) ?></div>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>

                    <form action="<?= isset($user) ? site_url('admin/users/update/' . $user->id) : site_url('admin/users/register') ?>" method="post" id="userForm">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">ユーザー名</label>
                                <input type="text" class="form-control" id="username" name="username"
                                    value="<?= old('username', $user->username ?? '') ?>" required maxlength="20" style="ime-mode:disabled;" inputmode="latin"
                                    pattern="^[a-zA-Z0-9._@-]+$" title="ユーザー名は半角英数字、ドット、アンダースコア、ハイフン、@ のみ利用可能">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fullname" class="form-label">氏名</label>
                                <input type="text" class="form-control" id="fullname" name="fullname"
                                    value="<?= old('fullname', $user->fullname ?? '') ?>" required
                                    maxlength="20" pattern="^[^\s ]+[ ][^\s ]+$"
                                    title="姓と名の間に半角スペースを1つ入れてください">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">メールアドレス</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= old('email', $user->email ?? '') ?>" required maxlength="255">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">パスワード <?= isset($user) ? '(変更する場合のみ)' : '' ?></label>
                                <input type="password" class="form-control" id="password" name="password"
                                    minlength="4" <?= isset($user) ? '' : 'required' ?> onkeyup="validatePassword()">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">パスワード（確認）</label>
                                <input type="password" class="form-control" id="password_confirm"
                                    name="password_confirm" onkeyup="validatePassword()">
                                <div id="password-error" class="text-danger mt-1" style="display: none;">
                                    パスワード（確認）が一致しません。
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- 役割（Role） -->
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">役割</label>
                                <select class="form-select" id="role" name="role" 
                                    <?= (isset($user) && $user->inGroup('admin')) || (isset($user) && $user->id == $loggedInUser->id) ? 'disabled' : '' ?> required>
                                    <option value="user" <?= isset($user) && $user->inGroup('user') ? 'selected' : '' ?>>一般ユーザー</option>
                                    <option value="admin" <?= isset($user) && $user->inGroup('admin') ? 'selected' : '' ?>>管理者</option>
                                </select>
                                <?php if ((isset($user) && $user->inGroup('admin')) || (isset($user) && $user->id == $loggedInUser->id)) : ?>
                                    <input type="hidden" name="role" value="<?= $user->inGroup('admin') ? 'admin' : '' ?>">
                                <?php endif; ?>
                            </div>

                            <!-- 状態 -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ユーザ状態</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="active" id="activeYes" value="1" 
                                        <?= isset($user) && $user->active ? 'checked' : '' ?>
                                        <?= isset($user) && $user->id == $loggedInUser->id ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="activeYes">有効</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="active" id="activeNo" value="0" 
                                        <?= isset($user) && !$user->active ? 'checked' : '' ?>
                                        <?= isset($user) && $user->id == $loggedInUser->id ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="activeNo">無効</label>
                                </div>
                                <?php if (isset($user) && $user->id == $loggedInUser->id) : ?>
                                    <input type="hidden" name="active" value="<?= $user->active ?>">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= site_url('admin/users') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 戻る
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> <?= isset($user) ? '更新' : '登録' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function validatePassword() {
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("password_confirm").value;
        const errorDiv = document.getElementById("password-error");

        if (password !== confirmPassword && confirmPassword !== "") {
            errorDiv.style.display = "block";
        } else {
            errorDiv.style.display = "none";
        }
    }

    document.getElementById("userForm").addEventListener("submit", function(event) {
        if (document.getElementById("password-error").style.display === "block") {
            event.preventDefault();
            alert("パスワード（確認）が一致していません。");
        }
    });
</script>

<?= $this->endSection() ?>
