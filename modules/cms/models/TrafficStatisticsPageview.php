<?php namespace Cms\Models;

use Model;
use Cms\Classes\TrafficLogger;

/**
 * TrafficStatisticsPageview for capturing page views
 *
 * @property string ev_datetime
 * @property string ev_date
 * @property string ev_year_month_day
 * @property string ev_year_month
 * @property string ev_year_quarter
 * @property string ev_year_week
 * @property string ev_year
 * @property string ev_timestamp
 * @property bool user_authenticated
 * @property string client_id
 * @property bool first_time_visit
 * @property string user_agent
 * @property string page_path
 * @property string ip
 * @property string city
 * @property string country
 * @property string referral_domain
 *
 * @package october\cms
 * @author Alexey Bobkov, Samuel Georges
 */
class TrafficStatisticsPageview extends Model
{
    /**
     * @var string table associated with the model
     */
    public $table = 'cms_traffic_stats_pageviews';

    /**
     * @var bool timestamps enabled
     */
    public $timestamps = false;

    /**
     * purgeOldRecords destroys traffic data beyond the configured retention period
     */
    public static function purgeOldRecords()
    {
        $months = TrafficLogger::getRetentionMonths();
        if (!$months) {
            return;
        }

        $obj = new static;
        $obj->where('ev_datetime', '<', now()->subMonths($months)->toDateTimeString())->delete();
    }

    /**
     * purgeAllRecords destroys all traffic data
     */
    public static function purgeAllRecords()
    {
        TrafficStatisticsPageview::truncate();
    }
}
