<?php namespace Cms\Classes;

use Cms\Helpers\Component as ComponentHelpers;
use ValidationException;

/**
 * Snippet represents a static page snippet.
 *
 * @package october\cms
 * @author Alexey Bobkov, Samuel Georges
 */
class Snippet
{
    /**
     * @var string code specifies the snippet code.
     */
    public $code;

    /**
     * @var string description specifies the snippet description.
     */
    protected $description = null;

    /**
     * @var string name specifies the snippet name.
     */
    protected $name = null;

    /**
     * @var string properties for the snippet.
     */
    protected $properties;

    /**
     * @var string componentClass name for the snippet.
     */
    protected $componentClass = null;

    /**
     * @var bool useAjax for the snippet.
     */
    protected $useAjax = null;

    /**
     * @var array pageSnippetMap is an internal cache of snippet declarations defined on a page.
     */
    protected static $pageSnippetMap = [];

    /**
     * @var \Cms\Classes\ComponentBase componentObj
     */
    protected $componentObj = null;

    /**
     * initFromPartial initializes the snippet from a CMS partial.
     * @param \Cms\Classes\Partial $partial A partial to load the configuration from.
     */
    public function initFromPartial($partial)
    {
        $viewBag = $partial->getViewBag();

        $this->code = $viewBag->property('snippetCode');
        $this->description = $viewBag->property('snippetDescription');
        $this->name = $viewBag->property('snippetName');
        $this->properties = $viewBag->property('snippetProperties', []);
        $this->useAjax = $viewBag->property('snippetAjax', false);
    }

    /**
     * initFromComponentInfo initializes the snippet from a CMS component information.
     * @param string $componentClass Specifies the component class.
     * @param string $componentCode Specifies the component code.
     */
    public function initFromComponentInfo($componentClass, $componentCode)
    {
        $this->code = $componentCode;
        $this->componentClass = $componentClass;
    }

    /**
     * getName returns the snippet name.
     * This method should not be used in the front-end request handling.
     * @return string
     */
    public function getName()
    {
        if ($this->name !== null) {
            return $this->name;
        }

        if ($this->componentClass === null) {
            return null;
        }

        $component = $this->getComponent();

        return $this->name = ComponentHelpers::getComponentName($component);
    }

    /**
     * getDescription returns the snippet description.
     * This method should not be used in the front-end request handling.
     * @return string
     */
    public function getDescription()
    {
        if ($this->description !== null) {
            return $this->description;
        }

        if ($this->componentClass === null) {
            return null;
        }

        $component = $this->getComponent();

        return $this->description = ComponentHelpers::getComponentDescription($component);
    }

    /**
     * useAjaxPartial determines if the snippet should have AJAX enabled.
     * @return bool
     */
    public function useAjaxPartial()
    {
        if ($this->useAjax !== null) {
            return $this->useAjax;
        }

        if ($this->componentClass === null) {
            return null;
        }

        $component = $this->getComponent();

        return $this->useAjax = ComponentHelpers::getComponentSnippetAjax($component);
    }

    /**
     * getComponentClass returns the snippet component class name.
     * If the snippet is a partial snippet, returns NULL.
     * @return string Returns the snippet component class name
     */
    public function getComponentClass()
    {
        return $this->componentClass;
    }

    /**
     * getProperties returns the snippet property list as array, in format
     * compatible with Inspector.
     */
    public function getProperties()
    {
        if (!$this->componentClass) {
            return self::parseIniProperties($this->properties);
        }
        else {
            return ComponentHelpers::getComponentsPropertyConfig($this->getComponent(), false, true);
        }
    }

    /**
     * getComponent returns a component corresponding to the snippet.
     * This method should not be used in the front-end request handling code.
     * @return \Cms\Classes\ComponentBase
     */
    protected function getComponent()
    {
        if ($this->componentClass === null) {
            return null;
        }

        if ($this->componentObj !== null) {
            return $this->componentObj;
        }

        $componentClass = $this->componentClass;

        return $this->componentObj = new $componentClass();
    }

    /**
     * processTemplateSettingsArray
     */
    public static function processTemplateSettingsArray($settingsArray)
    {
        if (!isset($settingsArray['viewBag']['snippetProperties'])) {
            return $settingsArray;
        }

        $properties = [];

        $rows = $settingsArray['viewBag']['snippetProperties'];
        foreach ($rows as $row) {
            $property = array_get($row, 'property');
            $settings = array_only($row, ['title', 'type', 'default', 'options']);

            if (isset($settings['options'])) {
                $settings['options'] = self::dropDownOptionsToArray($settings['options']);
            }

            $properties[$property] = $settings;
        }

        $settingsArray['viewBag']['snippetProperties'] = [];

        foreach ($properties as $name => $value) {
            $settingsArray['viewBag']['snippetProperties'][$name] = $value;
        }

        return $settingsArray;
    }

    /**
     * processTemplateSettings
     */
    public static function processTemplateSettings($template)
    {
        if (!isset($template->viewBag['snippetProperties'])) {
            return;
        }

        $parsedProperties = self::parseIniProperties($template->viewBag['snippetProperties']);
        foreach ($parsedProperties as $index => &$property) {
            if (isset($property['options']) && is_array($property['options'])) {
                $property['options'] = self::dropDownOptionsToString($property['options']);
            }
        }

        $template->viewBag['snippetProperties'] = $parsedProperties;

        // Duplicate changes back to settings
        $template->settings['components']['viewBag'] = $template->viewBag;
    }

    /**
     * parseIniProperties converts a keyed object to an array, converting the index to the "property" value.
     * @return array
     */
    protected static function parseIniProperties($properties)
    {
        foreach ($properties as $index => $value) {
            $properties[$index]['property'] = $index;
        }

        return array_values($properties);
    }

    /**
     * dropDownOptionsToArray
     */
    protected static function dropDownOptionsToArray($optionsString)
    {
        $options = explode('|', $optionsString);

        $result = [];
        foreach ($options as $index => $optionStr) {
            $parts = explode(':', $optionStr, 2);

            if (count($parts) > 1) {
                $key = trim($parts[0]);

                if (strlen($key)) {
                    if (!preg_match('/^[0-9a-z-_]+$/i', $key)) {
                        throw new ValidationException(['snippetProperties' => __("Invalid drop-down option key: :key. Option keys can contain only digits, Latin letters and characters _ and -", ['key'=>$key])]);
                    }

                    $result[$key] = trim($parts[1]);
                }
                else {
                    $result[$index] = trim($optionStr);
                }
            }
            else {
                $result[$index] = trim($optionStr);
            }
        }

        return $result;
    }

    /**
     * dropDownOptionsToString
     * @param mixed $optionsArray
     * @return string
     */
    protected static function dropDownOptionsToString($optionsArray)
    {
        $result = [];
        $isAssoc = (bool) count(array_filter(array_keys($optionsArray), 'is_string'));

        foreach ($optionsArray as $optionIndex => $optionValue) {
            $result[] = $isAssoc
                ? $optionIndex.':'.$optionValue
                : $optionValue;
        }

        return implode(' | ', $result);
    }
}
