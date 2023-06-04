<?php

namespace App\Console\Commands;

use App\Models\Crawl;
use App\Models\CrawlReport;
use App\Models\Event;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CrawlAllEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl-allevents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawls event data from allevents';

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
        $cities = ['istanbul', 'london', 'berlin', 'paris', 'madrid', 'amsterdam', 'kiev', 'rome', 'barcelona', 'dubai'];

        $report = new CrawlReport();
        $report->command = self::class;
        $report->runtime = now();
        $report->result = 'success';

        $createdCount = 0;
        $crawlCount = 0;

        $withoutImageCount = 0;
        $withoutAddressCount = 0;
        $withoutOrganizerCount = 0;

        $bar = $this->output->createProgressBar(100);
        $bar->start();

        try {
            foreach ($cities as $city) {
                $dom = new \DOMDocument('1.0', 'UTF-8');
                $dom->encoding = 'UTF-8';

                libxml_use_internal_errors(true);

                $html = file_get_contents('https://allevents.in/'.$city);
                $dom->loadHTML($html);

                $xpath = new DomXPath($dom);

                $nodeList = $xpath->query("//script[@type='application/ld+json']");

                foreach ($nodeList as $node) {
                    $events = json_decode($node->textContent);

                    foreach ($events as $index => $event) {
                        $bar->advance();
                        $crawlCount++;

                        $data[$index] = [];

                        $data[$index]['url'] = $event->url;
                        $data[$index]['title'] = $event->name;
                        $data[$index]['start_date'] = $event->startDate ?? null;
                        $data[$index]['end_date'] = $event->endDate ?? null;

                        $data[$index]['organizer'] = null;
                        if (!empty($event->organizer)) {
                            $data[$index]['organizer'] = $event->organizer[0]->name;
                        }
                        if (!empty($event->offers)) {
                            $data[$index]['price'] = $event->offers[0]->lowPrice . ' - ' . $event->offers[0]->highPrice;
                        }
                        $data[$index]['location'] = $event->location->name ?? null;
                        $data[$index]['address'] = $event->location->address->streetAddress ?? null;
                        $data[$index]['country'] = $event->location->address->addressCountry ?? null;
                        $data[$index]['city'] = $event->location->address->addressLocality ?? null;

                        $eventDetail = curl_init($event->url);
                        curl_setopt($eventDetail, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($eventDetail, CURLOPT_USERAGENT, "'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0'");
                        $eventDetailCurl = curl_exec($eventDetail);

                        if (curl_getinfo($eventDetail,CURLINFO_HTTP_CODE) == 200) {
                            $domDetail = new \DOMDocument('1.0', 'UTF-8');
                            $domDetail->encoding = 'UTF-8';

                            $domDetail->loadHTML($eventDetailCurl);

                            $xpathDetail = new DOMXPath($domDetail);

                            $nodeListDetail = $xpathDetail->query("//script[@type='application/ld+json']");
                            $tagList = $xpathDetail->query("//a[@class='btn btn-mini toh track']");
                            $descriptions = $xpathDetail->query("//div[@class='event-description-html']");

                            $data[$index]['description'] = null;

                            foreach ($descriptions as $description) {
                                $data[$index]['description'] = trim($description->textContent);
                            }

                            $tags = null;

                            foreach ($tagList as $tag) {
                                $tags .= $tag->textContent . ', ';
                            }

                            $data[$index]['image'] = null;

                            foreach ($nodeListDetail as $detail) {
                                // $data[$index]['description'] = json_decode($detail->textContent)->description ?? null;
                                $data[$index]['image'] = json_decode($detail->textContent)->image ?? null;
                                break;
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
                                    'site' => 'allevents',
                                    'title' => $data[$index]['title'],
                                    'start_date' => $data[$index]['start_date'] ? \Carbon\Carbon::make($data[$index]['start_date'])->format('Y-m-d') : null,
                                    'end_date' => $data[$index]['end_date'] ? \Carbon\Carbon::make($data[$index]['end_date'])->format('Y-m-d') : null,
                                    'organizer' => $data[$index]['organizer'] ?? null,
                                    'description' => $data[$index]['description'],
                                    'price' => $data[$index]['price'] ?? null,
                                    'location' => $data[$index]['location'],
                                    'city' => $data[$index]['city'],
                                    'city_slug' => Str::slug($data[$index]['city']),
                                    'tags' => $tags,
                                    'country' => $data[$index]['country'],
                                    'address' => $data[$index]['address'],
                                    'image' => $data[$index]['image'],
                                    'created_at' => now(),
                                ]
                            );

                            if ($crawl->wasRecentlyCreated) {
                                $createdCount++;
                            }
                        }
                    }
                    break;
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
