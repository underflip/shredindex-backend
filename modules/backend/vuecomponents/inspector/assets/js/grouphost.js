/*
 * Vue Inspector group host implementation
 */
oc.Modules.register('backend.component.inspector.grouphost', function () {
    Vue.component('backend-component-inspector-grouphost', {
        props: {
            controls: {
                type: Array,
                required: true
            },
            obj: {
                type: Object,
                required: true
            },
            parentObj: {
                type: Object,
                required: false
            },
            splitterData: {
                type: Object,
                required: true
            },
            inspectorUniqueId: {
                type: String,
                required: true
            },
            depth: {
                type: Number,
                required: true
            },
            panelUpdateData: {
                type: Object,
                required: true
            },
            layoutUpdateData: {
                type: Object
            },
            inspectorPreferences: {
                type: Object
            }
        },
        data: function () {
            return {
            };
        },
        computed: {
            groupedControls: function computeGroupedUntabbedControls() {
                return $.oc.vueComponentHelpers.inspector.utils.groupControls(this.controls);
            }
        },
        methods: {
        },
        template: '#backend_vuecomponents_inspector_grouphost'
    });
});