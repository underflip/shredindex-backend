<?php namespace Nocio\Headstart\Widgets;

use Cms\Widgets\TemplateList;


class GraphList extends TemplateList
{

    /**
     * @var string Message to display when there are no records in the list.
     */
    public $noRecordsMessage = 'nocio.headstart::lang.graph.no_list_records';

    /**
     * @var string Message to display when the Delete button is clicked.
     */
    public $deleteConfirmation = 'nocio.headstart::lang.graph.delete_confirm';

}
