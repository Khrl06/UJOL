        <footer class="main-footer mt-auto">
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
                <div>
                    &copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong>. Hak Cipta Dilindungi. Sistem Ujian Online SMA/Sederajat.
                </div>
                <div class="d-flex align-items-center gap-3 text-xs">
                    <span class="text-muted"><i class="bi bi-cpu me-1"></i>AI Proctoring Engine Ready</span>
                    <span class="text-muted">&bull;</span>
                    <span class="badge bg-light text-secondary border">v<?= APP_VERSION ?></span>
                </div>
            </div>
        </footer>
    </div> <!-- Close #content-wrapper -->
</div> <!-- Close #wrapper -->

<!-- Bootstrap 5.3.3 JS Bundle (Popper included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SmartExam Custom JS -->
<script src="<?= asset('js/app.js') ?>"></script>

<!-- SmartExam AI Chatbot Widget (Node.js) -->
<?php \App\Core\View::partial('chatbot_widget'); ?>

</body>
</html>
