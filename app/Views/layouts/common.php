<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->renderSection('title') ?> | Resource Usage Tracker</title>

    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/favicon.ico') ?>">

    <!-- Bootstrap CSS -->
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/icons/bootstrap-icons.css') ?>">

    <style>
        body {
            padding-top: 56px;
        }
    </style>

    <?= $this->renderSection('pageStyles') ?>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-secondary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand text-light" href="<?= site_url('/') ?>">
                <i class="bi bi-hdd-stack"></i> Resource Usage Tracker
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (auth()->loggedIn()): ?>
                    <?php $authUser = auth()->user(); ?>
                    <ul class="navbar-nav ms-auto">
                        <!-- リソース予約 -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-calendar-check"></i> 予約
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= route_to('reservations.schedule') ?>">
                                        <i class="bi bi-list-check"></i> 予約スケジュール
                                    </a>
                                </li>
                                <li>
                                    <?php if (!$authUser->inGroup('guest')): ?> 
                                        <a class="dropdown-item" href="<?= route_to('reservations.create') ?>">
                                            <i class="bi bi-calendar-plus"></i> 予約登録
                                        </a>
                                    <?php endif; ?>
                                </li>

                            </ul>
                        </li>

                        <!-- リソース管理 (ゲスト以外) -->
                        <?php if (!$authUser->inGroup('guest')): ?> 
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-hdd-network"></i> リソース管理
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= route_to('resources.index') ?>">
                                        <i class="bi bi-server"></i> リソース一覧
                                    </a>
                                </li>
                                <?php if ($authUser->inGroup('admin')): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= route_to('resources.create') ?>">
                                        <i class="bi bi-plus-square"></i> リソース登録
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if ($authUser->inGroup('admin')): ?>
                        <!-- アカウント管理 (管理者のみ) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-key"></i> アカウント管理
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= route_to('accounts.index') ?>">
                                        <i class="bi bi-list-ul"></i> アカウント一覧
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- ユーザー管理 (管理者のみ) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-people"></i> ユーザー管理
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= route_to('admin.users.index') ?>">
                                        <i class="bi bi-list-ul"></i> ユーザ一覧
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= route_to('admin.users.register') ?>">
                                        <i class="bi bi-person-plus"></i> ユーザ登録
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <!-- 設定 -->
                        <li class="nav-item dropdown ms-5">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-2"></i>
                                <?= esc($authUser->fullname ?? $authUser->username) ?> さん
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (!$authUser->inGroup('guest')): ?> 
                                    <li>
                                        <a class="dropdown-item" href="<?= route_to('profiles.settings', $authUser->id) ?>">
                                            <i class="bi bi-gear"></i> 設定
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <form id="logoutForm" action="<?= route_to('logout') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right"></i> ログアウト
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main role="main" class="container mt-3">
    <?php if (session()->getFlashdata('message')) : ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('message')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="alert alert-danger">
            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                <?= esc($error) ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

        <?= $this->renderSection('main') ?>
    </main>

    <?= $this->renderSection('pageScripts') ?>

    <!-- Bootstrap JavaScript -->
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>

</body>
</html>
