<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=resource-width, initial-scale=1, shrink-to-fit=no">

    <title><?= $this->renderSection('title') ?> | Resource Usage Tracker</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        /* ナビバーの高さを考慮して main に余白を追加 */
        body {
            padding-top: 56px; /* navbar の高さ分 */
        }
    </style>

    <?= $this->renderSection('pageStyles') ?>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-secondary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand text-light" href="<?= site_url('/') ?>">Resource Usage Tracker</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (auth()->loggedIn()): ?>
                    <ul class="navbar-nav ms-auto">
                        <!-- リソース予約 -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                リソース予約
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= route_to('resource.index') ?>">予約状況確認</a></li>
                            </ul>
                        </li>

                        <!-- リソース管理 -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                リソース管理
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= route_to('resource.index') ?>">リソース一覧</a></li>
                                <li><a class="dropdown-item" href="<?= route_to('resource.create') ?>">リソース登録</a></li>
                            </ul>
                        </li>

                        <!-- ユーザー管理 -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                ユーザー管理
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= route_to('admin.users.index') ?>">ユーザ一覧</a></li>
                                <li><a class="dropdown-item" href="<?= route_to('admin.register') ?>">ユーザ登録</a></li>
                            </ul>
                        </li>

                        <!-- ユーザー情報 -->
                        <li class="nav-item dropdown ms-5">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-2"></i>
                                <?= esc(auth()->user()->fullname ?? auth()->user()->username) ?> さん
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <form id="logoutForm" action="<?= route_to('logout') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item text-danger">ログアウト</button>
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
        <?= $this->renderSection('main') ?>
    </main>

    <?= $this->renderSection('pageScripts') ?>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
