<?php
    $context = BackendMenu::getContext();
    $contextSidenav = BackendMenu::getContextSidenavPartial($context->owner, $context->mainMenuCode);
?>
<?php if (!$contextSidenav): ?>
    <?php
        $sideMenuItems = BackendMenu::listMainMenuSubItems();
    ?>
    <?php if ($sideMenuItems): ?>
        <div class="layout-sidenav-container">
            <div class="layout-sidenav-spacer">
                <nav
                    id="layout-sidenav"
                    class="layout-sidenav"
                    data-active-class="active"
                    data-control="sidenav">
                    <ul class="mainmenu-items">
                        <?= $this->makeLayoutPartial('submenu_items', [
                            'sideMenuItems' => $sideMenuItems,
                            'mainMenuItemActive' => true,
                            'mainMenuItemCode' => $context->mainMenuCode,
                            'noSvgEffects' => true,
                            'context' => $context
                        ]) ?>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif ?>
<?php else: ?>
    <?= $this->makePartial($contextSidenav) ?>
<?php endif ?>
