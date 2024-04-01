<?php namespace Cms\Classes;

use Str;
use Event;
use Config;
use Cookie;
use Request;
use Cms\Models\TrafficStatisticsPageview;
use SystemException;
use Carbon\Carbon;

/**
 * TrafficLogger logs pageviews for Internal Traffic Statistics.
 *
 * @package october\cms
 * @author Alexey Bobkov, Samuel Georges
 */
class TrafficLogger
{
    /**
     * isEnabled checks if the Internal Traffic Statistics feature is enabled.
     * Returns true if the feature is enabled, and false otherwise.
     */
    public static function isEnabled(): bool
    {
        return (bool) Config::get('cms.internal_traffic_statistics.enabled', false);
    }

    /**
     * getTimezone retrieves the timezone setting for the Internal Traffic Statistics feature.
     * If no specific timezone is set for the feature, it defaults to the general CMS timezone.
     * Returns the timezone identifier string.
     */
    public static function getTimezone(): string
    {
        $result = Config::get('cms.internal_traffic_statistics.timezone');

        if (!$result) {
            $result = Config::get('cms.timezone');
        }

        return (string) $result;
    }

    /**
     * getRetentionMonths returns data retention, in months.
     * Returns the number of months or null for indefinite retention.
     */
    public static function getRetentionMonths(): ?int
    {
        $retention = Config::get('cms.internal_traffic_statistics.retention');
        if (strlen($retention)) {
            if (!is_int($retention)) {
                throw new SystemException('cms.internal_traffic_statistics.retention must be a number or null');
            }

            return $retention;
        }

        return null;
    }

    /**
     * logPageview logs a pageview for the Internal Traffic Statistics.
     * This method should be called whenever a page is viewed
     * to keep the statistics up-to-date. Creates a client
     * ID cookie if it doesn't exist.
     */
    public static function logPageview()
    {
        if (!self::isEnabled()) {
            return;
        }

        if (Request::method() !== 'GET') {
            return;
        }

        if (Request::ajax() && !Request::header('X-PJAX')) {
            return;
        }

        $referrer = Request::header('X-PJAX-REFERRER');
        if (!$referrer) {
            $referrer = Request::header('Referer');
        }

        $clientId = self::getClientId();
        $firstTimeVisit = false;
        if (!$clientId) {
            $clientId = self::generateClientId();
            $firstTimeVisit = true;
        }

        $evDateTime = self::makeEventDateTime();

        $pageview = new TrafficStatisticsPageview();
        $pageview->user_authenticated = self::isUserAuthenticated();
        $pageview->ev_datetime = $evDateTime->toDateTimeString();
        $pageview->ev_date = $evDateTime->toDateString();

        $pageview->ev_year_month_day = $evDateTime->toDateString();
        $pageview->ev_year = $evDateTime->copy()->startOfYear()->toDateString();
        $pageview->ev_year_quarter  = $evDateTime->copy()->startOfQuarter()->toDateString();
        $pageview->ev_year_month  = $evDateTime->copy()->startOfMonth()->toDateString();
        $pageview->ev_year_week  = $evDateTime->copy()->startOfWeek(Carbon::MONDAY)->toDateString();

        $pageview->client_id = $clientId;
        $pageview->first_time_visit = $firstTimeVisit;
        $pageview->user_agent = Str::substr(Request::header('User-Agent'), 0, 255);
        $pageview->ip = Str::substr(Request::ip(), 0, 255);
        $pageview->page_path = Str::substr(Request::path(), 0, 255);
        $pageview->referral_domain = Str::substr(parse_url(
            $referrer,
            PHP_URL_HOST
        ), 0, 255);
        $pageview->ev_timestamp = gmdate('Y-m-d H:i:s', time());
        $pageview->save();

        if (rand(1, 100) === 1) {
            TrafficStatisticsPageview::purgeOldRecords();
        }
    }

    /**
     * makeEventDateTime returns the current event date and time in the configured timezone.
     * Returns the event date and time string.
     */
    protected static function makeEventDateTime(): Carbon
    {
        return Carbon::now(self::getTimezone());
    }

    /**
     * getClientId retrieves the client ID from the cookie.
     */
    protected static function getClientId()
    {
        return Cookie::get('oc_clid');
    }

    /**
     * generateClientId generates a random client ID string.
     */
    protected static function generateClientId(): string
    {
        $result = Str::random(32);

        Cookie::queue('oc_clid', $result, 60*24*365*5); // 5 years

        return $result;
    }

    /**
     * isUserAuthenticated checks if the user is currently authenticated. Returns
     * true if the user is authenticated, and false otherwise.
     */
    protected static function isUserAuthenticated(): bool
    {
        /**
         * @event cms.internalTrafficStatistics.isUserAuthenticated
         * Verifies if there's a currently authenticated user.
         *
         * Example usage:
         *
         *     Event::listen('cms.internalTrafficStatistics.isUserAuthenticated', function() {
         *         return true;
         *     });
         *
         */
        $result = Event::fire('cms.internalTrafficStatistics.isUserAuthenticated', [], true);
        if ($result === true) {
            return $result;
        }

        return false;
    }
}
