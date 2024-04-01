<div
    id="<?= $this->relationGetId() ?>"
    data-control="relation-controller"
    data-request-data="_relation_field: '<?= $relationField ?>', _relation_extra_config: '<?= e(json_encode($relationExtraConfig)) ?>'"
    class="relation-behavior relation-view-<?= $relationViewMode ?>"
    <?php if ($externalToolbarAppState): ?>data-external-toolbar-app-state="<?= e($externalToolbarAppState)?>"<?php endif ?>
>

    <?php if ($toolbar = $this->relationRenderToolbar()): ?>
        <!-- Relation Toolbar -->
        <div id="<?= $this->relationGetId('toolbar') ?>" class="relation-toolbar">
            <?= $toolbar ?>
        </div>
    <?php endif ?>

    <!-- Relation View -->
    <div id="<?= $this->relationGetId('view') ?>" class="relation-manager">
        <?= $this->relationRenderView() ?>
    </div>

</div>
