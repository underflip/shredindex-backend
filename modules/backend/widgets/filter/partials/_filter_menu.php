<div
    class="filter-group filter-setup dropdown">
    <a href="javascript:;"
        data-toggle="dropdown"
        title="<?= __("Filter Setup") ?>">
        <span><i class="ph ph-funnel-simple"></i></span>
    </a>
    <ul class="dropdown-menu" role="menu">
        <li role="presentation">
            <a
                data-filter-clear
                role="menuitem"
                href="javascript:;"
                data-request="<?= $this->getEventHandler('onFilterClearAll') ?>"
                data-stripe-load-indicator
                tabindex="-1">
                <i class="icon-eraser"></i>
                <?= __("Clear Filters") ?>
            </a>
        </li>
    </ul>
</div>
