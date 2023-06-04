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

class CrawlBiletinoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl-biletino';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawls event data from biletino';

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
     */
    public function handle()
    {
        $find = ['Ä±', 'Ä°', 'Ä', 'Å', 'â', 'Ã§', '\n', 'Å', 'Ä'];
        $replace = ['ı', 'İ', 'ğ', 'ş', '', 'ç', ' ', 'Ş', 'Ğ'];

        $pregFind = ['/\s\s+/'];
        $pregReplace = [' '];

        $report = new CrawlReport();
        $report->command = self::class;
        $report->runtime = now();
        $report->result = 'success';

        $createdCount = 0;
        $crawlCount = 0;

        $withoutImageCount = 0;
        $withoutOrganizerCount = 0;

        try {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->encoding = 'UTF-8';

            libxml_use_internal_errors(true);

            $html = file_get_contents('https://biletino.com/tr/search/?count=250'); // Count normalde istediğimiz bir sayı olabiliyor, schedule edeceğimiz için azaltıldı
            $dom->loadHTML($html);

            $xpath = new DomXPath($dom);

            $bar = $this->output->createProgressBar(1000);
            $bar->start();

            $nodeList = $xpath->query("//div[@class='col-md-4 product']");
            foreach ($nodeList as $index => $node) {
                $bar->advance();
                $crawlCount++;

                $data[$index] = [];
                $domCard = new \DOMDocument('1.0', 'UTF-8');
                $domCard->encoding = 'UTF-8';

                $domCard->loadHTML(mb_convert_encoding($node->ownerDocument->saveHTML($node), 'HTML-ENTITIES', 'UTF-8'));
                $titles = $domCard->getElementsByTagName('h3');
                $locations = $domCard->getElementsByTagName('p');
                $urls = $domCard->getElementsByTagName('a');

                $data[$index]['title'] = null;
                foreach ($titles as $title) {
                    $data[$index]['title'] = trim(preg_replace($pregFind, $pregReplace, str_replace($find, $replace, $title->textContent)));
                }

                $data[$index]['location'] = null;
                foreach ($locations as $location) {
                    $data[$index]['location'] = trim(preg_replace($pregFind, $pregReplace, str_replace($find, $replace, $location->textContent)));
                }
                foreach ($urls as $url) {
                    $data[$index]['url'] = 'https://biletino.com' . $url->getAttribute('href');
                    break;
                }

                $eventDetailHtml = file_get_contents($data[$index]['url']);
                $eventDetailDom = new \DOMDocument('1.0', 'UTF-8');
                $eventDetailDom->encoding = 'UTF-8';

                $eventDetailDom->loadHTML($eventDetailHtml);
                $xpathDetail = new DOMXPath($eventDetailDom);
                $nodeListPrice = $xpathDetail->query("//div[@class='event-price']");

                $data[$index]['price'] = null;
                foreach ($nodeListPrice as $price) {
                    $data[$index]['price'] = trim(preg_replace($pregFind, $pregReplace, $price->lastChild->wholeText));
                }

                $nodeListOrganizator = $xpathDetail->query("//a[@class='event-owner-name']");

                $data[$index]['organizator'] = null;
                foreach ($nodeListOrganizator as $organizator) {
                    $data[$index]['organizator'] = trim(preg_replace($pregFind, $pregReplace, str_replace($find, $replace, $organizator->textContent)));
                }

                $nodeListTags = $xpathDetail->query("//button[@class='btn']");

                $data[$index]['tags'] = null;
                foreach ($nodeListTags as $tag) {
                    $tagString = '';
                    $tagString .= str_replace($find, $replace, $tag->textContent).',';

                    $data[$index]['tags'] = trim(preg_replace($pregFind, $pregReplace, $tagString));
                }

                $nodeListEventTitles = $xpathDetail->query("//h4[@class='event-detail-title']");

                $data[$index]['event_detail']['title'] = null;
                foreach ($nodeListEventTitles as $title) {
                    $data[$index]['event_detail']['title'] = trim(preg_replace($pregFind, $pregReplace, str_replace($find, $replace, $title->textContent)));
                }

                $nodeListEventContent = $xpathDetail->query("//div[@class='event-detail-content']");

                $data[$index]['event_detail']['content'] = null;
                foreach ($nodeListEventContent as $eventContent) {
                    $data[$index]['event_detail']['content'] = trim(preg_replace($pregFind, $pregReplace, str_replace($find, $replace, $eventContent->textContent)));
                }

                $jsonDetail = $xpathDetail->query("//script[@type='application/ld+json']");

                $data[$index]['start_date'] = null;
                $data[$index]['end_date'] = null;

                $data[$index]['country'] = null;
                $data[$index]['city'] = null;

                $data[$index]['image'] = null;

                foreach ($jsonDetail as $detail) {
                    $detail = json_decode($detail->textContent);
                    $data[$index]['start_date'] = $detail->startDate;
                    $data[$index]['end_date'] = $detail->endDate;

                    $data[$index]['country'] = trim(preg_replace($pregFind, $pregReplace, str_replace($find, $replace, $detail->location[0]->addressCountry ?? '')));
                    $data[$index]['city'] = trim(preg_replace($pregFind, $pregReplace, str_replace($find, $replace, $detail->location[0]->addressRegion ?? '')));

                    $data[$index]['image'] = $detail->image[0];
                    break;
                }


                if (blank($data[$index]['organizator'])) {
                    $withoutOrganizerCount++;
                }

                if (blank($data[$index]['image'])) {
                    $withoutImageCount++;
                }

                $crawl = Event::updateOrCreate(
                    ['url' => $data[$index]['url']],
                    [
                        'site' => 'biletino',
                        'title' => $data[$index]['title'],
                        'start_date' => $data[$index]['start_date'] ? \Carbon\Carbon::make($data[$index]['start_date'])->format('Y-m-d') : null,
                        'end_date' => $data[$index]['end_date'] ? \Carbon\Carbon::make($data[$index]['end_date'])->format('Y-m-d') : null,
                        'organizer' => $data[$index]['organizator'],
                        'location' => $data[$index]['location'],
                        'country' => $data[$index]['country'],
                        'city' => $data[$index]['city'],
                        'city_slug' => Str::slug($data[$index]['city']),
                        'description' => $data[$index]['event_detail']['content'],
                        'tags' => $data[$index]['tags'],
                        'price' => $data[$index]['price'],
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
            $this->error($exception);
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
