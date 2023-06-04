<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use App\Models\CrawlReport;
use Illuminate\Support\Facades\Log;

class CrawlKommunityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl-kommunity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawls event data from kommunity';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $report = new CrawlReport();
        $report->command = self::class;
        $report->runtime = now();
        $report->result = 'success';

        $createdCount = 0;
        $crawlCount = 0;

        $withoutImageCount = 0;
        $withoutOrganizerCount = 0;

        $toBeCalledUrls = [
            'https://services.kommunity.com/upcomings?page=1&city_id=3410&online=1&language=tr&limit=50&date=next%2030%20days&timezone=Europe/Istanbul',
            'https://services.kommunity.com/upcomings?page=1&city_id=3410&language=tr&limit=50&date=next%2030%20days&timezone=Europe/Istanbul',
        ];

        try {
            foreach ($toBeCalledUrls as $toBeCalledUrl) {
                $eventApi = file_get_contents($toBeCalledUrl);
                $events = json_decode($eventApi)->data;
                $bar = $this->output->createProgressBar(100);
                $bar->start();

                foreach ($events as $index => $event) {
                    $bar->advance();
                    $crawlCount++;
                    $data[$index] = [];
                    $data[$index]['title'] = $event->name;
                    $data[$index]['url'] = 'https://kommunity.com/' . $event->community->slug . '/events/' . $event->slug;
                    $data[$index]['start_date'] = $event->start_date->date;
                    $data[$index]['end_date'] = $event->end_date->date;
                    $data[$index]['image'] = $event->highlight_photo;
                    $data[$index]['organizer'] = $event->community->name;
                    $data[$index]['attendee_count'] = $event->users_count;

                    $eventDetail = curl_init('https://api.kommunity.com/api/v3/' . $event->community->slug . '/events/' . $event->slug);
                    curl_setopt($eventDetail, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($eventDetail, CURLOPT_USERAGENT, "'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0'");
                    $eventDetailCurl = curl_exec($eventDetail);

                    if (blank($data[$index]['organizer'])) {
                        $withoutOrganizerCount++;
                    }

                    if (blank($data[$index]['image'])) {
                        $withoutImageCount++;
                    }

                    if(curl_getinfo($eventDetail,CURLINFO_HTTP_CODE) == 200) {
                        $eventData = json_decode($eventDetailCurl)->data;

                        $data[$index]['description'] = $eventData->detail;

                        $data[$index]['location'] = null;
                        $data[$index]['address'] = null;
                        $data[$index]['city'] = null;
                        $data[$index]['country'] = null;

                        if (!empty($eventData->venue)) {
                            $data[$index]['location'] = $eventData->venue->name;
                            $data[$index]['address'] = $eventData->venue->address;
                            $data[$index]['city'] = $eventData->venue->city->name;
                            $data[$index]['city_slug'] = $data[$index]['city'];
                            $data[$index]['country'] = $eventData->venue->country->name;
                        }

                        $tags = null;

                        foreach ($eventData->tags as $tag) {
                            $tags .= $tag->name.', ';
                        }

                        $crawl = Event::updateOrCreate(
                            ['url' => $data[$index]['url']],
                            [
                                'site' => 'kommunity',
                                'title' => $data[$index]['title'],
                                'start_date' => $data[$index]['start_date'],
                                'end_date' => $data[$index]['end_date'],
                                'organizer' => $data[$index]['organizer'],
                                'attendee_count' => $data[$index]['attendee_count'],
                                'location' => $data[$index]['location'] ?? 'Online',
                                'address' => $data[$index]['address'],
                                'city' => $data[$index]['city'],
                                'city_slug' => $data[$index]['city_slug'],
                                'country' => $data[$index]['country'],
                                'tags' => $tags,
                                'description' => $data[$index]['description'],
                                'image' => $data[$index]['image'],
                                'created_at' => now(),
                            ],
                        );
                        if ($crawl->wasRecentlyCreated) {
                            $createdCount++;
                        }
                    }
                }
            }
        } catch (\Exception $exception){
            Log::error($exception);
            $report->fail_reason = $exception->getMessage();
            $report->result = 'fail';
        }
        $report->created_count = $createdCount;
        $report->updated_count = $crawlCount - $createdCount;
        $report->without_image = $withoutImageCount;
        $report->without_organizer = $withoutOrganizerCount;

        $report->save();

        $bar->finish();
    }
}
