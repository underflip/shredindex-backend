<?php namespace Tailor\ContentFields;

use Tailor\Models\EntryRecord;
use Tailor\Models\RepeaterItem;
use Tailor\Classes\BlueprintIndexer;
use Tailor\Classes\Blueprint\StructureBlueprint;
use Tailor\Classes\Relations\CustomMultiJoinRelation;
use Tailor\Classes\Relations\CustomNestedJoinRelation;
use October\Contracts\Element\FormElement;
use October\Contracts\Element\ListElement;
use October\Contracts\Element\FilterElement;
use SystemException;

/**
 * EntriesField allows association to entries
 *
 * @package october\tailor
 * @author Alexey Bobkov, Samuel Georges
 */
class EntriesField extends FallbackField
{
    /**
     * @var string source UUID of the section
     */
    public $source;

    /**
     * @var int|null maxItems allowed
     */
    public $maxItems;

    /**
     * @var string inverse the relationship definition, set to the field name
     */
    public $inverse;

    /**
     * @var string displayMode for the relationship
     */
    public $displayMode = 'recordfinder';

    /**
     * @var array controller config for displayMode: controller
     */
    public $controller = [];

    /**
     * @var mixed sourceCache of the source blueprint
     */
    protected $sourceCache;

    /**
     * @var mixed fieldsetCache of the source content fieldset definition
     */
    protected $fieldsetCache;

    /**
     * defineConfig will process the field configuration.
     */
    public function defineConfig(array $config)
    {
        if (isset($config['source'])) {
            $this->source = (string) $config['source'];
        }

        if (isset($config['maxItems'])) {
            $this->maxItems = (int) $config['maxItems'];
        }

        if (isset($config['inverse'])) {
            $this->inverse = (string) $config['inverse'];
        }

        if (isset($config['displayMode'])) {
            $this->displayMode = (string) $config['displayMode'];
        }

        if (isset($config['controller'])) {
            $this->controller = (array) $config['controller'];
        }
    }

    /**
     * defineFormField will define how a field is displayed in a form.
     */
    public function defineFormField(FormElement $form, $context = null)
    {
        $field = $form->addFormField($this->fieldName, $this->label);

        $config = $this->config + ['nameFrom' => 'title'];

        $field->useConfig($config);

        // Singular and multi display modes
        $supportedDisplays = $this->maxItems === 1
            ? ['recordfinder']
            : ['taglist'];

        $field->displayAs(in_array($this->displayMode, $supportedDisplays)
            ? $this->displayMode
            : 'relation');

        if ($this->displayMode === 'controller') {
            $this->defineFormFieldAsRelationController($field);
        }

        // @deprecated this should be default
        if ($field->type === 'taglist') {
            $field->customTags(false);
        }
    }

    /**
     * defineListColumn
     */
    public function defineListColumn(ListElement $list, $context = null)
    {
        $partial = $this->maxItems === 1 ? 'column_single' : 'column_multi';

        $list->defineColumn($this->fieldName, $this->label)
            ->displayAs('partial')
            ->path("~/modules/tailor/contentfields/entriesfield/partials/_{$partial}.php")
            ->clickable(false)
            ->sortable(false)
            ->shortLabel($this->shortLabel)
            ->useConfig($this->column ?: [])
        ;
    }

    /**
     * defineFilterScope
     */
    public function defineFilterScope(FilterElement $filter, $context = null)
    {
        $filter->defineScope($this->fieldName, $this->label)
            ->displayAs('group')
            ->nameFrom('title')
            ->shortLabel($this->shortLabel)
            ->useConfig($this->scope ?: [])
        ;
    }

    /**
     * extendModelObject will extend the record model.
     */
    public function extendModelObject($model)
    {
        // Define the relationship
        if ($this->inverse) {
            $this->defineInverseModelRelationship($model);
        }
        else {
            $this->defineModelRelationship($model);
        }

        // For defining list columns and form fields
        $model->bindEvent('model.afterRelation', function($name, $related) {
            if ($name === $this->fieldName) {
                $related->extendWithBlueprint($this->getSourceBlueprint()->uuid);
            }
        });
    }

    /**
     * extendDatabaseTable
     */
    public function extendDatabaseTable($table)
    {
        if ($this->maxItems === 1) {
            $table->integer($this->getSingularKeyName())->unsigned()->nullable();
        }
    }

    /**
     * getSingularKeyName
     */
    public function getSingularKeyName()
    {
        return $this->fieldName.'_id';
    }

    /**
     * defineModelRelationship for the direct relationship. These definitions
     * rely on multisite modifying the relation definition.
     *
     * @see \October\Rain\Database\Traits\Multisite::defineMultisiteRelation
     */
    protected function defineModelRelationship($model)
    {
        $relatedMultisite = $this->getSourceBlueprint()->useMultisite();
        $isSingular = $this->maxItems === 1;
        $isNested = $model instanceof RepeaterItem;

        if ($isSingular) {
            $model->belongsTo[$this->fieldName] = [
                EntryRecord::class,
                'key' => $this->getSingularKeyName()
            ];
        }
        elseif ($isNested) {
            $model->belongsToMany[$this->fieldName] = [
                EntryRecord::class,
                'table' => 'tailor_content_joins',
                'relationClass' => CustomNestedJoinRelation::class,
                'relatedKey' => $relatedMultisite ? 'site_root_id' : 'id'
            ];
        }
        else {
            $model->morphedByMany[$this->fieldName] = [
                EntryRecord::class,
                'table' => $model->getBlueprintDefinition()->getJoinTableName(),
                'name' => $this->fieldName,
                'relationClass' => CustomMultiJoinRelation::class,
                'relatedKey' => $relatedMultisite ? 'site_root_id' : 'id'
            ];
        }
    }

    /**
     * defineInverseModelRelationship for the inverse relationship. These definitions
     * do not rely on multisite via propagatable attribute. Inverse relations do not
     * replicate and should be treated as read-only.
     */
    protected function defineInverseModelRelationship($model)
    {
        $otherField = $this->getSourceFieldset()->getField($this->inverse);
        if (!$otherField || !$otherField instanceof EntriesField) {
            throw new SystemException("Invalid inverse field '{$this->inverse}' for source '{$this->source}' for '{$this->fieldName}'.");
        }

        $parentMultisite = $model->getBlueprintDefinition()->useMultisite();
        $relatedMultisite = $this->getSourceBlueprint()->useMultisite();

        $isSingular = $this->maxItems === 1;
        $otherIsSingular = $otherField->maxItems === 1;
        $otherIsPropagatable = $relatedMultisite && ($otherField->translatable === false || $otherField->propagatable === true);

        if ($isSingular) {
            $model->hasOne[$this->fieldName] = [
                EntryRecord::class,
                'key' => $otherField->getSingularKeyName(),
                'otherKey' => $otherIsPropagatable ? 'site_root_id' : 'id',
                'replicate' => false
            ];
        }
        elseif ($otherIsSingular) {
            $model->hasMany[$this->fieldName] = [
                EntryRecord::class,
                'key' => $otherField->getSingularKeyName(),
                'otherKey' => $otherIsPropagatable ? 'site_root_id' : 'id',
                'replicate' => false
            ];
        }
        else {
            $model->morphToMany[$this->fieldName] = [
                EntryRecord::class,
                'table' => $this->getSourceBlueprint()->getJoinTableName(),
                'name' => $this->inverse,
                'relationClass' => CustomMultiJoinRelation::class,
                'relatedKey' => $otherIsPropagatable ? 'site_root_id' : 'id',
                'parentKey' => $parentMultisite ? 'site_root_id' : 'id',
                'replicate' => false
            ];
        }
    }

    /**
     * defineFormFieldAsRelationController
     */
    protected function defineFormFieldAsRelationController($field)
    {
        $blueprint = $this->getSourceBlueprint();

        $customMessages = array_merge((array) $blueprint->customMessages, (array) $this->customMessages);

        $toolbarButtons = $this->toolbarButtons;
        if (!$toolbarButtons) {
            $toolbarButtons = $blueprint->navigation ? 'add|remove' : 'create|delete';
        }

        $fieldConfig = [
            'label' => $this->label,
            'list' => [],
            'form' => [],
            'customMessages' => $customMessages,
            'popupSize' => $this->popupSize,
            'view' => [
                'toolbarButtons' => $toolbarButtons,
                'recordsPerPage' => $this->recordsPerPage,
            ],
            'manage' => [
                'recordsPerPage' => $this->recordsPerPage,
            ]
        ];

        if ($blueprint instanceof StructureBlueprint) {
            $fieldConfig['structure'] = [
                'maxDepth' => $blueprint->getMaxDepth(),
                'showTree' => $blueprint->hasTree(),
            ] + ((array) $blueprint->structure);
        }

        if ($this->span === 'adaptive') {
            $fieldConfig['externalToolbarAppState'] = 'toolbarExtensionPoint';
        }

        // Transfer custom configuration
        if (isset($this->controller['view'])) {
            $fieldConfig['view'] = array_merge($fieldConfig['view'], (array) $this->controller['view']);
        }

        if (isset($this->controller['manage'])) {
            $fieldConfig['manage'] = array_merge($fieldConfig['manage'], (array) $this->controller['manage']);
        }

        $field->controller($fieldConfig);
    }

    /**
     * getSourceBlueprint validates and converts source to a blueprint
     */
    protected function getSourceBlueprint()
    {
        if ($this->sourceCache !== null) {
            return $this->sourceCache;
        }

        if (!$this->source) {
            throw new SystemException("Missing source for '{$this->fieldName}'.");
        }

        $indexer = BlueprintIndexer::instance();

        $uuid = $indexer->hasSection($this->source);
        if (!$uuid) {
            throw new SystemException("Invalid source '{$this->source}' for '{$this->fieldName}'.");
        }

        return $this->sourceCache = BlueprintIndexer::instance()->findSection($uuid);
    }

    /**
     * getSourceFieldset returns the source fieldset definition for checking inverse relations
     */
    protected function getSourceFieldset()
    {
        if ($this->fieldsetCache !== null) {
            return $this->fieldsetCache;
        }

        return $this->fieldsetCache = BlueprintIndexer::instance()->findContentFieldset($this->getSourceBlueprint()->uuid);
    }
}
