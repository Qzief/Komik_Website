<?php $errors = validation_errors(); ?>
<?php if ($errors !== []): ?>
    <ul class="error-list">
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php unset($_SESSION['_errors']); ?>
<?php endif; ?>
