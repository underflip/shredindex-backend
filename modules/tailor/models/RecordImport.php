<?php namespace Tailor\Models;

use Site;
use Backend\Models\ImportModel;
use October\Contracts\Element\ListElement;
use October\Contracts\Element\FormElement;

/**
 * RecordImport for importing records (entries or globals)
 *
 * @package october\tailor
 * @author Alexey Bobkov, Samuel Georges
 */
class RecordImport extends ImportModel
{
    use \Tailor\Models\RecordImport\HasGeneralBlueprint;

    /**
     * @var array rules for validation
     */
    public $rules = [];

    /**
     * defineListColumns
     */
    public function defineListColumns(ListElement $host)
    {
        $host->defineColumn('id', 'ID');
        $host->defineColumn('title', 'Title');
        $host->defineColumn('slug', 'Slug');
        $host->defineColumn('is_enabled', 'Enabled');
        $host->defineColumn('published_at', 'Publish Date');
        $host->defineColumn('expired_at', 'Expiry Date');
        $host->defineColumn('content_group', 'Entry Type');

        if ($this->isEntryStructure()) {
            $host->defineColumn('fullslug', 'Full Slug');
            $host->defineColumn('parent_id', 'Parent');
        }

        $this->getContentFieldsetDefinition()->defineAllListColumns($host, ['context' => 'import']);
    }

    /**
     * defineFormFields
     */
    public function defineFormFields(FormElement $host)
    {
        $host->addFormField('update_existing', "Update Existing Records")->displayAs('checkbox')->comment("Check this box to update records that match the same ID, title or slug.");
    }

    /**
     * importData
     */
    public function importData($results, $sessionKey = null)
    {
        foreach ($results as $row => $data) {
            $id = array_get($data, 'id');
            if (!$id) {
                $this->logSkipped($row, "Missing entry ID");
                continue;
            }

            // Find or create
            $record = $this->findDuplicateRecord($data) ?: $this->resolveBlueprintModel();
            $exists = $record->exists;

            if ($exists) {
                if (!$this->update_existing) {
                    $this->logSkipped($row, "Record ID already exists");
                    continue;
                }

                if ($record->site_id && $record->site_id !== Site::getSiteIdFromContext()) {
                    $this->logSkipped($row, "Record ID exists in another site");
                    continue;
                }
            }

            // Update record
            foreach ($data as $attr => $value) {
                $this->decodeModelAttribute($record, $attr, $value, $sessionKey);
            }
            $record->forceSave(null, $sessionKey);

            if ($exists) {
                $this->logUpdated();
            }
            else {
                $this->logCreated();
            }
        }
    }

    /**
     * findDuplicateRecord
     */
    protected function findDuplicateRecord($data)
    {
        $query = $this->resolveBlueprintModel()->newQueryWithoutScopes();

        if ($id = array_get($data, 'id')) {
            return $query->find($id);
        }

        $record = $query->where('title', array_get($data, 'title'));
        if ($slug = array_get($data, 'slug')) {
            $record->orWhere('slug', $slug);
        }

        return $record->first();
    }

    /**
     * decodeModelAttribute
     */
    public function decodeModelAttribute($model, $attr, $value, $sessionKey)
    {
        if ($model->hasRelation($attr)) {
            $relationModel = $model->makeRelation($attr);
            if ($relationModel instanceof RepeaterItem) {
                $this->decodeRepeaterItems($model, $attr, $value, $sessionKey);
            }
            else {
                $model->setRelationSimpleValue($attr, $value);
            }
        }
        else {
            $model->$attr = $value;
        }
    }

    /**
     * decodeRepeaterItems
     */
    protected function decodeRepeaterItems($model, $attr, $values, $sessionKey)
    {
        if ($model->isRelationTypeSingular($attr)) {
            $values = [$values];
        }

        foreach ($values as $value) {
            $item = $model->makeRelation($attr);
            $item->content_group = $value['content_group'] ?? null;
            $item->extendWithBlueprint();

            $this->decodeRepeaterItem($item, $value, $sessionKey);

            // Repeaters "has many" relations are without a session key
            // and the saving chain is deferred in memory instead
            $model->$attr()->add($item);
        }
    }

    /**
     * decodeRepeaterItem
     */
    protected function decodeRepeaterItem($model, $data, $sessionKey)
    {
        foreach ($data as $attr => $value) {
            $this->decodeModelAttribute($model, $attr, $value, $sessionKey);
        }
    }
}
