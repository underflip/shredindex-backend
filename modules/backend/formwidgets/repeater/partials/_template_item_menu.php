<script type="text/template" data-item-menu-template>
    <?php if ($showDuplicate): ?>
        <li role="presentation">
            <a
                data-repeater-duplicate
                role="menuitem"
                href="javascript:;"
                tabindex="-1">
                <i class="icon-copy"></i>
                <?= __("Duplicate") ?>
            </a>
        </li>
        <li role="separator" class="divider"></li>
    <?php endif ?>
    <li role="presentation">
        <a
            data-repeater-expand
            role="menuitem"
            href="javascript:;"
            tabindex="-1">
            <i class="icon-expand"></i>
            <?= __("Expand") ?>
        </a>
    </li>
    <li role="presentation">
        <a
            data-repeater-collapse
            role="menuitem"
            href="javascript:;"
            tabindex="-1">
            <i class="icon-collapse"></i>
            <?= __("Collapse") ?>
        </a>
    </li>
    <?php if ($showReorder): ?>
        <li role="presentation">
            <a
                data-repeater-move-up
                role="menuitem"
                href="javascript:;"
                tabindex="-1">
                <i class="icon-long-arrow-up"></i>
                <?= __("Move Up") ?>
            </a>
        </li>
        <li role="presentation">
            <a
                data-repeater-move-down
                role="menuitem"
                href="javascript:;"
                tabindex="-1">
                <i class="icon-long-arrow-down"></i>
                <?= __("Move Down") ?>
            </a>
        </li>
    <?php endif ?>
    <li role="separator" class="divider"></li>
    <li role="presentation">
        <a
            data-repeater-remove
            role="menuitem"
            href="javascript:;"
            tabindex="-1">
            <i class="icon-delete"></i>
            <?= __("Remove") ?>
        </a>
    </li>
</script>
