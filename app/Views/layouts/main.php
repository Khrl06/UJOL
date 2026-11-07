<?php
use App\Core\View;

// Muat Header HTML
View::partial('header', ['pageTitle' => $pageTitle ?? APP_NAME]);
?>

<div id="wrapper">
    <!-- Sidebar Navigasi -->
    <?php View::partial('sidebar'); ?>

    <div id="content-wrapper">
        <!-- Top Navbar -->
        <?php View::partial('navbar'); ?>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="container-fluid p-0">
                <!-- Flash Messages -->
                <?php View::partial('flash'); ?>

                <!-- View Content -->
                <?= $content ?? '' ?>
            </div>
        </main>

        <!-- Footer -->
<?php View::partial('footer'); ?>
