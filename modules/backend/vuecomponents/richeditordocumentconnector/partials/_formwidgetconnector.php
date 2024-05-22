<backend-component-document
    container-css-class="fill-container"
    :built-in-mode="true"
    :full-screen="fullScreen"
    ref="document"
>
    <template v-slot:toolbar v-if="!hasExternalToolbar">
        <backend-component-document-toolbar
            :elements="toolbarElements"
            :disabled="readOnly"
            @command="onToolbarCommand"
            ref="toolbar"
        ></backend-component-document-toolbar>
    </template>

    <template v-slot:content>
        <div class="flex-layout-column fill-container" ref="contentContainer">
            <div class="flex-layout-item stretch editor-panel relative">
                <backend-component-richeditor-document-connector
                    :allow-resizing="showMargins"
                    :toolbar-container="toolbarExtensionPointProxy"
                    :external-toolbar-app-state="externalToolbarAppState"
                    :use-media-manager="useMediaManager"
                    :built-in-mode="true"
                    unique-key="html-editor-form-widget"
                    container-css-class="fill-container"
                >
                    <backend-component-richeditor
                        v-model="value"
                        :read-only="options.readOnly"
                        :use-line-breaks="options.useLineBreaks"
                        :full-page="fullPage"
                        :editor-options="editorOptions"
                        :toolbar-buttons="toolbarButtons"
                        ref="richeditor"
                        @blur="onBlur"
                        @focus="onFocus"
                    >
                    </backend-component-richeditor>
                </backend-component-richeditor-document-connector>
            </div>
        </div>
    </template>
</backend-component-document>
