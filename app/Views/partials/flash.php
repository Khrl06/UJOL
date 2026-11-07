<?php
use App\Helpers\SessionHelper;
$allFlashes = SessionHelper::getFlash();
?>
<?php if (!empty($allFlashes)): ?>
    <div class="flash-container mb-4">
        <?php foreach ($allFlashes as $type => $messages): ?>
            <?php 
                $icon = match($type) {
                    'success' => 'bi-check-circle-fill',
                    'danger'  => 'bi-exclamation-triangle-fill',
                    'warning' => 'bi-exclamation-circle-fill',
                    'info'    => 'bi-info-circle-fill',
                    default   => 'bi-bell-fill'
                };
            ?>
            <?php foreach ($messages as $msg): ?>
                <div class="alert alert-<?= e($type) ?> alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm rounded-3 py-2 px-3 mb-2" role="alert">
                    <i class="bi <?= $icon ?> fs-5"></i>
                    <div class="flex-grow-1 text-sm"><?= $msg ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
