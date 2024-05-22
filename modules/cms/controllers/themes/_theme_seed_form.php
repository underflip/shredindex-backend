<?= Form::ajax('onSeed', [
    'id' => 'themeSeedForm',
    'data-popup-load-indicator' => true,
]) ?>

    <input type="hidden" name="theme" value="<?= $themeDir ?>" />
    <input type="hidden" name="mode" value="import" />

    <div class="modal-header">
        <h4 class="modal-title"><?= __('Seed Theme') ?></h4>
        <button type="button" class="btn-close" data-dismiss="popup"></button>
    </div>

    <?php if (!$this->fatalError): ?>

        <div class="modal-body">
            <?= $widget->render() ?>
        </div>
        <div class="modal-footer">
            <button
                type="submit"
                class="btn btn-success">
                <?= __('Seed Theme') ?>
            </button>

            <button
                type="button"
                class="btn btn-secondary"
                data-dismiss="popup">
                <?= __("Cancel") ?>
            </button>
        </div>

    <?php else: ?>

        <div class="modal-body">
            <p class="flash-message static error"><?= e(__($this->fatalError)) ?></p>
        </div>
        <div class="modal-footer">
            <button
                type="button"
                class="btn btn-default"
                data-dismiss="popup">
                <?= __("Close") ?>
            </button>
        </div>

    <?php endif ?>

    <script>
        setTimeout(
            function(){ $('#themeSeedForm input.form-control:first').focus() },
            310
        )
    </script>

<?= Form::close() ?>
