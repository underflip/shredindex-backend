<?php namespace Nocio\Headstart\Controllers;

use Nocio\Headstart\Classes\Asset;
use Nocio\Headstart\Classes\Templates;
use Nocio\Headstart\Models\Settings;
use Url;
use Lang;
use Flash;
use Cache;
use Config;
use Request;
use Input;
use Exception;
use BackendMenu;
use Nocio\Headstart\Widgets\GraphList;
use Nocio\Headstart\Widgets\ComponentList;
use Nocio\Headstart\Widgets\Documentation;
use Nocio\Headstart\FormWidgets\AssetList;
use Nocio\Headstart\Classes\Graph;
use Nocio\Headstart\Classes\GraphqlClient;
use Cms\Classes\CmsCompoundObject;
use Backend\Classes\Controller;
use System\Helpers\DateTime;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use October\Rain\Router\Router as RainRouter;
use ApplicationException;


class Schema extends Controller
{
    use \Backend\Traits\InspectableContainer;

    /**
     * @var SchemaSourceProvider
     */
    protected $source;

    /**
     * @var Cms\Classes\Theme
     */
    protected $theme;

    /**
     * @var array Permissions required to view this page.
     */
    public $requiredPermissions = [
        'headstart.manage_schema',
    ];

    public function __construct(SchemaSourceProvider $source)
    {
        parent::__construct();

        $this->source = $source;
        $this->theme = $source->template;

        BackendMenu::setContext('Nocio.Headstart', 'api', 'schema');

        try {
            new GraphList($this, 'pageList', function () use ($source) {
                return Graph::listInTheme($source->template, true);
            });
            new ComponentList($this, 'componentList');
            new Documentation($this, 'documentation');
            new AssetList($this, 'assetList');
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }
    }

    /**
     * Index page action
     * @return void
     */
    public function index()
    {
        // ensure that schema path exists
        @mkdir(Settings::getSchemaDirectory(), 0777, true);

        // inherit CMS functionality
        $this->addJs('/modules/cms/assets/js/october.cmspage.js', 'core');
        $this->addJs('/modules/cms/assets/js/october.dragcomponents.js', 'core');
        $this->addJs('/modules/cms/assets/js/october.tokenexpander.js', 'core');
        $this->addCss('/modules/cms/assets/css/october.components.css', 'core');
        $this->addJs('/modules/backend/formwidgets/codeeditor/assets/js/build-min.js', 'core');

        // Color overrides
        $this->addCss('/plugins/nocio/headstart/assets/css/headstart.css');

        // GraphiQL
        $route_uri = !empty(Settings::get('route_uri', '')) ? Settings::get('route_uri', 'graphql') : 'graphql';
        $fetchUrl = parse_url(url($route_uri))['path'];
        $this->addJs('/plugins/nocio/headstart/assets/js/graphiql.js?fetchUrl=' . $fetchUrl);

        $this->bodyClass = 'compact-container';
        $this->pageTitle = 'cms::lang.cms.menu_label';
        $this->pageTitleTemplate = '%s '.trans($this->pageTitle);

        if (Request::ajax() && Request::input('formWidgetAlias')) {
            $this->bindFormWidgetToController();
        }
    }

    /**
     * Opens an existing template from the index page
     * @return array
     */
    public function index_onOpenTemplate($type = null, $path = null)
    {
        $path = ($path) ? $path : Request::input('path');
        $type = ($type) ? $type : Request::input('type');

        $template = $this->loadTemplate($type, $path);
        $widget = $this->makeTemplateFormWidget($type, $template);

        $this->vars['templatePath'] = $path;
        $this->vars['lastModified'] = DateTime::makeCarbon($template->mtime);

        if ($type === 'page') {
            // todo: what to do here?
            $router = new RainRouter;
            $this->vars['pageUrl'] = $router->urlFromPattern($template->url);
        }

        return [
            'tabTitle' => $this->getTabTitle($type, $template),
            'tab'      => $this->makePartial('form_page', [
                'form'          => $widget,
                'templateType'  => $type,
                'templateTheme' => $this->theme->getDirName(),
                'templateMtime' => $template->mtime
            ])
        ];
    }

    public function onCreateFromTemplate() {
        $templates = new Templates();
        $filename = $templates->download(Input::get('code'));
        return $this->index_onOpenTemplate('page', $filename);
    }

    public function onRefreshGraphTemplates() {
        $templates = new Templates();
        $this->vars['graph_templates'] = $templates->get();
        return [
            'graphTemplates' => $this->makePartial('graph_templates')
        ];
    }

    /**
     * Saves the template currently open
     * @return array
     */
    public function onSave()
    {
        $type = Request::input('templateType');
        $templatePath = trim(Request::input('templatePath'));
        $template = $templatePath ? $this->loadTemplate($type, $templatePath) : $this->createTemplate($type);
        $formWidget = $this->makeTemplateFormWidget($type, $template);

        $saveData = $formWidget->getSaveData();
        $postData = post();
        $templateData = [];

        $settings = array_get($saveData, 'settings', []) + Request::input('settings', []);
        $settings = $this->upgradeSettings($settings);

        if ($settings) {
            $templateData['settings'] = $settings;
        }

        $fields = ['markup', 'code', 'fileName', 'content'];

        foreach ($fields as $field) {
            if (array_key_exists($field, $saveData)) {
                $templateData[$field] = $saveData[$field];
            }
            elseif (array_key_exists($field, $postData)) {
                $templateData[$field] = $postData[$field];
            }
        }

        if (!empty($templateData['markup']) && Config::get('cms.convertLineEndings', false) === true) {
            $templateData['markup'] = $this->convertLineEndings($templateData['markup']);
        }

        if (!empty($templateData['code']) && Config::get('cms.convertLineEndings', false) === true) {
            $templateData['code'] = $this->convertLineEndings($templateData['code']);
        }

        if (
            !Request::input('templateForceSave') && $template->mtime
            && Request::input('templateMtime') != $template->mtime
        ) {
            throw new ApplicationException('mtime-mismatch');
        }

        $template->attributes = [];
        $template->fill($templateData);
        $template->save();

        /**
         * @event cms.template.save
         * Fires after a CMS template (page|partial|layout|content|asset) has been saved.
         *
         * Example usage:
         *
         *     Event::listen('cms.template.save', function ((\Cms\Controllers\Index) $controller, (mixed) $templateObject, (string) $type) {
         *         \Log::info("A $type has been saved");
         *     });
         *
         * Or
         *
         *     $CmsIndexController->bindEvent('template.save', function ((mixed) $templateObject, (string) $type) {
         *         \Log::info("A $type has been saved");
         *     });
         *
         */
        $this->fireSystemEvent('headstart.template.save', [$template, $type]);

        Flash::success(Lang::get('cms::lang.template.saved'));

        $result = [
            'templatePath'  => $template->fileName,
            'templateMtime' => $template->mtime,
            'tabTitle'      => $this->getTabTitle($type, $template)
        ];

        if ($type === 'page') {
            $this->source->clearCache();
            CmsCompoundObject::clearCache($this->theme);
            Cache::forget(config('lighthouse.cache.key'));
        }

        return $result;
    }

    /**
     * Displays a form that suggests the template has been edited elsewhere
     * @return string
     */
    public function onOpenConcurrencyResolveForm()
    {
        return $this->makePartial('concurrency_resolve_form');
    }

    /**
     * Create a new template
     * @return array
     */
    public function onCreateTemplate()
    {

        $type = Request::input('type');
        $template = $this->createTemplate($type);

        $widget = $this->makeTemplateFormWidget($type, $template);

        $this->vars['templatePath'] = '';

        return [
            'tabTitle' => $this->getTabTitle($type, $template),
            'tab'      => $this->makePartial('form_page', [
                'form'          => $widget,
                'templateType'  => $type,
                'templateTheme' => $this->theme->getDirName(),
                'templateMtime' => null
            ])
        ];
    }

    /**
     * Deletes multiple templates at the same time
     * @return array
     */
    public function onDeleteTemplates()
    {
        $type = Request::input('type');
        $templates = Request::input('template');
        $error = null;
        $deleted = [];

        try {
            foreach ($templates as $path => $selected) {
                if ($selected) {
                    $this->loadTemplate($type, $path)->delete();
                    $deleted[] = $path;
                }
            }
        }
        catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        /**
         * @event cms.template.delete
         * Fires after a CMS template (page|partial|layout|content|asset) has been deleted.
         *
         * Example usage:
         *
         *     Event::listen('cms.template.delete', function ((\Cms\Controllers\Index) $controller, (string) $type) {
         *         \Log::info("A $type has been deleted");
         *     });
         *
         * Or
         *
         *     $CmsIndexController->bindEvent('template.delete', function ((string) $type) {
         *         \Log::info("A $type has been deleted");
         *     });
         *
         */
        $this->fireSystemEvent('headstart.template.delete', [$type]);

        return [
            'deleted' => $deleted,
            'error'   => $error,
            'theme'   => Request::input('theme')
        ];
    }

    /**
     * Deletes a template
     * @return void
     */
    public function onDelete()
    {
        $type = Request::input('templateType');

        $this->loadTemplate($type, trim(Request::input('templatePath')))->delete();

        /*
         * Extensibility - documented above
         */
        $this->fireSystemEvent('headstart.template.delete', [$type]);
    }

    /**
     * Returns list of available templates
     * @return array
     */
    public function onGetTemplateList()
    {
        $page = Graph::inTheme($this->theme);
        return [
            'layouts' => $page->getLayoutOptions()
        ];
    }

    /**
     * Remembers an open or closed state for a supplied token, for example, component folders.
     * @return array
     */
    public function onExpandMarkupToken()
    {
        if (!$alias = post('tokenName')) {
            throw new ApplicationException(trans('cms::lang.component.no_records'));
        }

        // Can only expand components at this stage
        if ((!$type = post('tokenType')) && $type !== 'component') {
            return;
        }

        if (!($names = (array) post('component_names')) || !($aliases = (array) post('component_aliases'))) {
            throw new ApplicationException(trans('cms::lang.component.not_found', ['name' => $alias]));
        }

        if (($index = array_get(array_flip($aliases), $alias, false)) === false) {
            throw new ApplicationException(trans('cms::lang.component.not_found', ['name' => $alias]));
        }

        if (!$componentName = array_get($names, $index)) {
            throw new ApplicationException(trans('cms::lang.component.not_found', ['name' => $alias]));
        }

        return $this->source->getComponentSchemaString($componentName, $alias);
    }

    //
    // Methods for the internal use
    //

    /**
     * Reolves a template type to its class name
     * @param string $type
     * @return string
     */
    protected function resolveTypeClassName($type)
    {
        $types = [
            'graph' => Graph::class,
            'asset'=> Asset::class,
            // we keep page as legacy type so that we can reuse the CMS javascript
            'page' => Graph::class
        ];

        if (!array_key_exists($type, $types)) {
            throw new ApplicationException(trans('cms::lang.template.invalid_type'));
        }

        return $types[$type];
    }

    /**
     * Returns an existing template of a given type
     * @param string $type
     * @param string $path
     * @return mixed
     */
    protected function loadTemplate($type, $path)
    {
        $class = $this->resolveTypeClassName($type);

        if (!($template = call_user_func([$class, 'load'], $this->theme, $path))) {
            throw new ApplicationException(trans('cms::lang.template.not_found'));
        }

        /**
         * @event cms.template.processSettingsAfterLoad
         * Fires immediately after a CMS template (page|partial|layout|content|asset) has been loaded and provides an opportunity to interact with it.
         *
         * Example usage:
         *
         *     Event::listen('cms.template.processSettingsAfterLoad', function ((\Cms\Controllers\Index) $controller, (mixed) $templateObject) {
         *         // Make some modifications to the $template object
         *     });
         *
         * Or
         *
         *     $CmsIndexController->bindEvent('template.processSettingsAfterLoad', function ((mixed) $templateObject) {
         *         // Make some modifications to the $template object
         *     });
         *
         */
        $this->fireSystemEvent('headstart.template.processSettingsAfterLoad', [$template]);

        return $template;
    }

    /**
     * Creates a new template of a given type
     * @param string $type
     * @return mixed
     */
    protected function createTemplate($type)
    {
        $class = $this->resolveTypeClassName($type);

        if (!($template = $class::inTheme($this->theme))) {
            throw new ApplicationException(trans('cms::lang.template.not_found'));
        }

        return $template;
    }

    /**
     * Returns the text for a template tab
     * @param string $type
     * @param string $template
     * @return string
     */
    protected function getTabTitle($type, $template)
    {
        if ($type === 'page') {
            $result = $template->title ?: $template->getFileName();
            if (!$result) {
                $result = trans('cms::lang.page.new');
            }

            return $result;
        }

        if ($type === 'partial' || $type === 'layout' || $type === 'content' || $type === 'asset') {
            $result = in_array($type, ['asset', 'content']) ? $template->getFileName() : $template->getBaseFileName();
            if (!$result) {
                $result = trans('cms::lang.'.$type.'.new');
            }

            return $result;
        }

        return $template->getFileName();
    }

    /**
     * Returns a form widget for a specified template type.
     * @param string $type
     * @param string $template
     * @param string $alias
     * @return Backend\Widgets\Form
     */
    protected function makeTemplateFormWidget($type, $template, $alias = null)
    {
        $formConfigs = [
            'graph'    => '~/plugins/nocio/headstart/controllers/schema/fields.yaml',
            'page'    => '~/plugins/nocio/headstart/controllers/schema/fields.yaml',
            'asset'   => '~/modules/cms/classes/asset/fields.yaml'
        ];

        if (!array_key_exists($type, $formConfigs)) {
            throw new ApplicationException(trans('cms::lang.template.not_found'));
        }

        $widgetConfig = $this->makeConfig($formConfigs[$type]);
        $widgetConfig->model = $template;
        $widgetConfig->alias = $alias ?: 'form'.studly_case($type).md5($template->getFileName()).uniqid();

        return $this->makeWidget('Backend\Widgets\Form', $widgetConfig);
    }

    /**
     * Processes the component settings so they are ready to be saved
     * @param array $settings
     * @return array
     */
    protected function upgradeSettings($settings)
    {
        /*
         * Handle component usage
         */
        $componentProperties = post('component_properties');
        $componentNames = post('component_names');
        $componentAliases = post('component_aliases');

        if ($componentProperties !== null) {
            if ($componentNames === null || $componentAliases === null) {
                throw new ApplicationException(trans('cms::lang.component.invalid_request'));
            }

            $count = count($componentProperties);
            if (count($componentNames) != $count || count($componentAliases) != $count) {
                throw new ApplicationException(trans('cms::lang.component.invalid_request'));
            }

            for ($index = 0; $index < $count; $index++) {
                $componentName = $componentNames[$index];
                $componentAlias = $componentAliases[$index];

                $section = $componentName;
                if ($componentAlias != $componentName) {
                    $section .= ' '.$componentAlias;
                }

                $properties = json_decode($componentProperties[$index], true);
                unset($properties['oc.alias'], $properties['inspectorProperty'], $properties['inspectorClassName']);
                $settings[$section] = $properties;
            }
        }

        /*
         * Handle view bag
         */
        $viewBag = post('viewBag');
        if ($viewBag !== null) {
            $settings['viewBag'] = $viewBag;
        }

        /**
         * @event cms.template.processSettingsBeforeSave
         * Fires before a CMS template (page|partial|layout|content|asset) is saved and provides an opportunity to interact with the settings data. `$dataHolder` = {settings: array()}
         *
         * Example usage:
         *
         *     Event::listen('cms.template.processSettingsBeforeSave', function ((\Cms\Controllers\Index) $controller, (object) $dataHolder) {
         *         // Make some modifications to the $dataHolder object
         *     });
         *
         * Or
         *
         *     $CmsIndexController->bindEvent('template.processSettingsBeforeSave', function ((object) $dataHolder) {
         *         // Make some modifications to the $dataHolder object
         *     });
         *
         */
        $dataHolder = (object) ['settings' => $settings];
        $this->fireSystemEvent('headstart.template.processSettingsBeforeSave', [$dataHolder]);

        return $dataHolder->settings;
    }

    /**
     * Binds the active form widget to the controller
     * @return void
     */
    protected function bindFormWidgetToController()
    {
        $alias = Request::input('formWidgetAlias');
        $type = Request::input('templateType');
        $object = $this->loadTemplate($type, Request::input('templatePath'));
        $widget = $this->makeTemplateFormWidget($type, $object, $alias);

        $widget->bindToController();
    }

    /**
     * Replaces Windows style (/r/n) line endings with unix style (/n)
     * line endings.
     * @param string $markup The markup to convert to unix style endings
     * @return string
     */
    protected function convertLineEndings($markup)
    {
        $markup = str_replace(["\r\n", "\r"], "\n", $markup);

        return $markup;
    }

}
