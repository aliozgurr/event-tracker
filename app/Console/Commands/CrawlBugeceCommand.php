<?php

namespace App\Console\Commands;

use App\Models\CrawlReport;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CrawlBugeceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl-bugece';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawls event data from bugece';

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

        $bar = $this->output->createProgressBar(100);
        $bar->start();
        try {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->encoding = 'UTF-8';

            libxml_use_internal_errors(true);

            $html = file_get_contents('https://bugece.co/tr/events');

            $dom->loadHTML($html);

            $xpath = new \DOMXPath($dom);
            $json = $xpath->query("//script[@type='application/json']");

            foreach ($json as $siteData) {
                $events = json_decode($siteData->textContent)->props->stores->event->eventsRequest->result->data->items;
            }

            foreach ($events as $index => $event) {
                $data[$index] = [];
                $bar->advance();
                $crawlCount++;

                $data[$index]['title'] = $event->name;
                $data[$index]['url'] = 'https://bugece.co/tr/events/'.$event->slug;
                $data[$index]['start_date'] = $event->start_time;
                $data[$index]['end_date'] = $event->end_time;
                $data[$index]['location'] = $event->venue->name;
                $data[$index]['image'] = $event->image;
                $data[$index]['price'] = !empty($event->price_list[0]->price) ? $event->price_list[0]->price : null;


                $domDetail = new \DOMDocument('1.0', 'UTF-8');
                $domDetail->encoding = 'UTF-8';

                $htmlDetail = file_get_contents($data[$index]['url']);

                $domDetail->loadHTML($htmlDetail);

                $xpathDetail = new \DOMXPath($domDetail);
                $jsonDetail = $xpathDetail->query("//script[@type='application/json']");

                foreach ($jsonDetail as $detailData) {
                    $detail = json_decode($detailData->textContent)->props->stores->event->eventDetailRequest->result->data;
                }

                $data[$index]['description'] = $detail->turkish_desc;
                $data[$index]['address'] = $detail->venue->address;
                $data[$index]['country'] = isset($detail->venue->country->country) ? $detail->venue->country->country : null ;
                $data[$index]['city'] = isset($detail->venue->city->name) ? $detail->venue->city->name : null;
                $data[$index]['city_slug'] = Str::slug($data[$index]['city']);


                if (blank($data[$index]['address'])) {
                    $withoutAddressCount++;
                }

                if (blank($data[$index]['image'])) {
                    $withoutImageCount++;
                }

                $crawl = Event::updateOrCreate(
                    ['url' => $data[$index]['url']],
                    [
                        'site' => 'bugece',
                        'title' => $data[$index]['title'],
                        'start_date' => $data[$index]['start_date'],
                        'end_date' => $data[$index]['end_date'],
                        'description' => $data[$index]['description'],
                        'location' => $data[$index]['location'],
                        'address' => $data[$index]['address'],
                        'country' => $data[$index]['country'],
                        'city' => $data[$index]['city'],
                        'city_slug' => $data[$index]['city_slug'],
                        'image' => $data[$index]['image'],
                        'price' => $data[$index]['price'],
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

        $report->save();

        $bar->finish();
    }
}
