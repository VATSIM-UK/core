## Upgrade Notes

### Future
* [Depends on feature/atc_stats_tracking_simple] Enable the stats tracking cronjobs:
* * statistics:download

* [Depends on feature/atc_stats_tracking_simple] Publish module files with:
* * php artisan vendor:publish --force --provider="App\Modules\Statistics\StatisticsServiceProvider"
* * php artisan vendor:publish --force --provider="App\Modules\Ais\StatisticsServiceProvider"

### 2.2.0 > 2.2.1
* Enable the SyncMentors cronjob: <NF has the name>