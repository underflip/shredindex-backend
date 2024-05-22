<div class="modal-header">
    <h4 class="modal-title"><?= e(trans('system::lang.updates.changelog')) ?></h4>
    <button type="button" class="btn-close" data-dismiss="popup"></button>
</div>
<div class="modal-body">

    <?php if ($this->fatalError): ?>
        <p class="flash-message static error"><?= e($fatalError) ?></p>
    <?php else: ?>
        <div class="control-updatelist">
            <div class="control-scrollbar" style="height:400px" data-control="scrollbar">
                <div class="update-item">
                    <?php if (!empty($changelog)): ?>
                        <dl>
                            <?php foreach ($changelog as $version => $comments): ?>
                                <?php foreach ($comments as $index => $comment): ?>
                                    <dt><?= !$index ? e($version): '' ?></dt>
                                    <dd><?= e($comment) ?></dd>
                                <?php endforeach ?>
                            <?php endforeach ?>
                        </dl>
                    <?php else: ?>
                        <p class="m-3"><?= e(trans('system::lang.updates.details_changelog_missing')) ?></p>
                    <?php endif ?>
                </div>
            </div>
        </div>
    <?php endif ?>
</div>

<div class="modal-footer">
    <button
        type="button"
        class="btn btn-secondary"
        data-dismiss="popup">
        <?= __("Close") ?>
    </button>
</div>
