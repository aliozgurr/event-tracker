<?php

namespace App\Console\Commands;

use App\Models\Crawl;
use App\Models\CrawlReport;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CrawlFixrCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl-fixr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawls event data from fixr';

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
        $withoutAddressCount = 0;
        $withoutOrganizerCount = 0;

        try {
            $eventApi = file_get_contents('https://api.fixr-app.com/api/v2/home/events?offset=48&mode=&limit=250&latitude=40.9895535&longitude=28.6242858'); // Count normalde istediğimiz bir sayı olabiliyor, schedule edeceğimiz için azaltıldı

            $events = json_decode($eventApi)->data;

            $bar = $this->output->createProgressBar(2026);
            $bar->start();

            foreach ($events as $index => $event) {
                $bar->advance();
                $crawlCount++;

                $data[$index] = [];

                $data[$index]['url'] = 'https://fixr.co/event/' . $event->id;
                $data[$index]['title'] = $event->name;
                $data[$index]['start_date'] = gmdate('Y-m-d', $event->open_time);
                $data[$index]['end_date'] = gmdate('Y-m-d', $event->close_time);
                $data[$index]['organizer'] = $event->promoters[0]->name;
                $data[$index]['price'] = null;
                if ($event->cheapest_ticket) {
                    $data[$index]['price'] = $event->cheapest_ticket->price ?: 0 . ' ' . $event->cheapest_ticket->currency ?: '';
                }
                $data[$index]['location'] = $event->venue->name ?? '';
                $data[$index]['address'] = $event->venue->address ?? '';
                $data[$index]['city'] = $event->venue->city ?? '';
                $data[$index]['country'] = $event->venue->area ?? '';

                $eventDetail = file_get_contents('https://api.fixr-app.com/api/v2/app/event/' . $event->id);

                $data[$index]['description'] = json_decode($eventDetail)->description;

                $data[$index]['image'] = $event->event_image;

                $tagsString = null;

                foreach ($event->tags as $tag) {
                    $tagsString = $tag . ', ';
                }

                if (blank($data[$index]['organizer'])) {
                    $withoutOrganizerCount++;
                }

                if (blank($data[$index]['address'])) {
                    $withoutAddressCount++;
                }

                if (blank($data[$index]['image'])) {
                    $withoutImageCount++;
                }

                $crawl = Event::updateOrCreate(
                    ['url' => $data[$index]['url']],
                    [
                        'site' => 'fixr',
                        'title' => $data[$index]['title'],
                        'start_date' => $data[$index]['start_date'],
                        'end_date' => $data[$index]['end_date'],
                        'organizer' => $data[$index]['organizer'],
                        'description' => $data[$index]['description'],
                        'price' => $data[$index]['price'],
                        'location' => $data[$index]['location'],
                        'address' => $data[$index]['address'],
                        'city' => $data[$index]['city'],
                        'city_slug' => Str::slug($data[$index]['city']),
                        'country' => $data[$index]['country'],
                        'tags' => $tagsString,
                        'image' => $data[$index]['image'],
                        'created_at' => now(),
                    ],
                );

                if ($crawl->wasRecentlyCreated) {
                    $createdCount++;
                }
            }
        } catch (\Exception $exception) {
            Log::error($exception);
            $report->fail_reason = $exception->getMessage();
            $report->result = 'fail';
        }

        $report->created_count = $createdCount;
        $report->updated_count = $crawlCount - $createdCount;
        $report->without_image = $withoutImageCount;
        $report->without_address = $withoutAddressCount;
        $report->without_organizer = $withoutOrganizerCount;

        $report->save();

        $bar->finish();
    }
}
