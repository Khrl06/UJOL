<?php
use App\Core\View;

// Muat Header HTML
View::partial('header', ['pageTitle' => $pageTitle ?? ('Masuk - ' . APP_NAME)]);
?>

<div class="auth-body">
    <div class="auth-card">
        <!-- Flash Alert Notification -->
        <?php View::partial('flash'); ?>

        <!-- Auth Content -->
        <?= $content ?? '' ?>
    </div>
</div>

<!-- Bootstrap 5.3.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SmartExam Custom JS -->
<script src="<?= asset('js/app.js') ?>"></script>

<!-- SmartExam AI Chatbot Widget (Node.js) -->
<?php View::partial('chatbot_widget'); ?>

</body>
</html>
