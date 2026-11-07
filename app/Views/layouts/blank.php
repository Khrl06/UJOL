<?php
use App\Core\View;
// Muat Header HTML
View::partial('header', ['pageTitle' => $pageTitle ?? APP_NAME]);
?>

<div class="print-container p-4 bg-white text-dark">
    <?= $content ?? '' ?>
</div>

<style>
@media print {
    body {
        background: #fff !important;
        color: #000 !important;
        font-size: 11pt;
    }
    .no-print {
        display: none !important;
    }
    .print-container {
        padding: 0 !important;
        margin: 0 !important;
    }
    table {
        page-break-inside: auto;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    thead {
        display: table-header-group;
    }
}
</style>

<!-- Bootstrap 5.3.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
