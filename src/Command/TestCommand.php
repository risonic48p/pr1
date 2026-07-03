<?php

namespace App\Command;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\Panther\Client as PantherClient;
use Facebook\WebDriver\WebDriverBy;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;


#[AsCommand(
    name: 'Test',
    description: 'Add a short description for your command',
)]
class TestCommand extends Command
{

    protected string $cookies;

    protected array $headers;

    protected array $products;

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function selen()
    {
        //$client = PantherClient::createFirefoxClient();
        $client = PantherClient::createChromeClient();

        //$client->request('GET', 'https://www.mvideo.ru/product-list-page?q=onkron');

        //$crawler = $client->waitFor('.product-cards-row.ng-star-inserted');

        $client->request('GET', 'https://www.dns-shop.ru/brand/onkron/');
        $cookies = $client->getCookieJar()->all();
        dump($cookies);

        //$result = $client->getCookieJar();
        //dump($result);

        //$crawler = $client->waitFor('#catalog');

        //dump($crawler);
    }

    protected function guzl()
    {

        $client = new Client();

        $response = $client->get('https://www.mvideo.ru/bff/products/v3/search?offset=0&limit=225&query=onkron', [
            'headers' => [
                'User-Agent'                => 'Mozilla/5.0 (X11; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0',
                'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language'           => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding'           => 'gzip, deflate, br, zstd',
                'Connection'                => 'keep-alive',
                'Cookie'                    => $this->cookies,
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Fetch-Dest'            => 'document',
                'Sec-Fetch-Mode'            => 'navigate',
                'Sec-Fetch-Site'            => 'none',
                'Sec-Fetch-User'            => '?1',
                'DNT'                       => '1',
                'Sec-GPC'                   => '1',
                'Priority'                  => 'u=0, i',
                'TE'                        => 'trailers'
            ]
        ]);

        dump($response->getBody()->getContents());
    }

    protected function mvideoCatalog()
    {

        $client = new Client();

        $response = $client->get('https://www.mvideo.ru/bff/products/v3/search?offset=0&limit=225&query=onkron', [
            'headers' => [
                'User-Agent'                => 'Mozilla/5.0 (X11; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0',
                'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language'           => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding'           => 'gzip, deflate, br, zstd',
                'Connection'                => 'keep-alive',
                'Cookie'                    => $this->cookies,
            ]
        ]);

        $content = json_decode($response->getBody()->getContents(), true);

        dump($content['body']['products']);
    }


    protected function mvideoProduct()
    {

        $client = new Client();

        $response = $client->get('https://www.mvideo.ru/bff/product-details?productId=50140786', [
            'headers' => [
                'User-Agent'                => 'Mozilla/5.0 (X11; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0',
                'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language'           => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding'           => 'gzip, deflate, br, zstd',
                'Connection'                => 'keep-alive',
                'Cookie'                    => $this->cookies,
            ]
        ]);

        $content = json_decode($response->getBody()->getContents(), true);

        dump($content['body']);
    }


    protected function dnsCategoties()
    {

        $client = new Client();

        $response = $client->get('https://www.dns-shop.ru/brand/onkron/', [
            'headers' => [
                'User-Agent'                => 'Mozilla/5.0 (X11; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0',
                'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language'           => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding'           => 'gzip, deflate, br, zstd',
                'Connection'                => 'keep-alive',
                'Cookie'                    => $this->cookies,
            ]
        ]);

        $content = $response->getBody()->getContents();
        $crawler = new Crawler($content);
        $crawler->filter('#catalog a')->each(function (Crawler $node, $i) {
            dump($node->attr('href'));
        });

        //dump($response->getBody()->getContents());
    }



    protected function dns()
    {
        //$client = PantherClient::createFirefoxClient();
        //$client = PantherClient::createChromeClient(null, [
        $client = PantherClient::createChromeClient('/home/user36/Документы/w/m42g6s9/drivers/undetected_chromedriver', [
            //'--no-sandbox',
            '--window-size=1200,1100',
          //  '--disable-gpu',
        ]);

      //  $client = PantherClient::createFirefoxClient(null, [
        //    '--window-size=1200,1100',
    //    ]);

        $client->request('GET', 'https://www.dns-shop.ru/brand/onkron/');
        sleep(5);
        $crawler = $client->waitFor('#catalog');
        dump($crawler);
        $client->close();
    }

    protected function python()
    {
        $command = escapeshellcmd('./python/1.sh');
        $output = shell_exec($command);
        $c = json_decode(trim($output), true);
        //echo $output;
        print_r($c);
    }

    protected function getHeaders(): array
    {
        $result = [];
        $resStr='';
        $needVals = ['qrator_ssid', 'qrator_jsid', 'qrator_jsr', 'city_path', 'current_path'];
        $command = escapeshellcmd('./python/1.sh');
        $output = shell_exec($command);
        $c = json_decode(trim($output), true);

        foreach ($c as $k => $v) {
            if (in_array($v['name'], $needVals)) {
                $result[$v['name']] = $v['value'];
                $resStr.= $v['name'] . '=' . $v['value'] . '; ';
            }
        }

        $headers = [
            'User-Agent'                => 'Mozilla/5.0 (X11; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0',
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language'           => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding'           => 'gzip, deflate, br, zstd',
            'Connection'                => 'keep-alive',
            'Cookie'                    => $resStr,
        ];

        $this->headers = $headers;
        return $this->headers;
    }

    public function grabReviews(): \Generator
    {
        $client = new Client();
        foreach ($this->products as $product) {

            $urlParts = explode('#', $product);

            $loop = true;
            $limit = 300;
            $offset = 0;
            while ($loop) {
                $multipart = [
                    [
                        'name'     => 'cityId',
                        'contents' => '30b7c1f3-03fb-11dc-95ee-00151716f9f5',
                    ],
                    [
                        'name'     => 'objectTypeId',
                        'contents' => '610d4c8e-37fc-416c-a603-bce518d57c15'
                    ],
                    [
                        'name'     => 'objectId',
                        'contents' => $urlParts[1],
                    ],
                    [
                        'name'     => 'limit',
                        'contents' => $limit,
                    ],
                    [
                        'name'     => 'isRealBuyer',
                        'contents' => 0,
                    ],
                    [
                        'name'     => 'hasPhotos',
                        'contents' => 0,
                    ],
                    [
                        'name'     => 'sort',
                        'contents' => 0,
                    ],
                    [
                        'name'     => 'offset',
                        'contents' => $offset,
                    ],
                    [
                        'name'     => 'onlyObjectOpinions',
                        'contents' => 0,
                    ],
                    [
                        'name'     => 'init',
                        'contents' => 1,
                    ],
                ];

                try {
                    sleep(3);
                    dump($product);
                    $resp = $client->request('POST', 'https://restapi.dns-shop.ru/v1/opinion/get-product-opinions', [
                        'multipart' => $multipart,
                        'headers' => $this->headers,
                    ]);

                    $json = json_decode($resp->getBody()->getContents(), true);
                    foreach ($json['data']['opinions'] as $opinion) {
                        $review['product'] = $product;
                        $review['author_name'] = empty($opinion['user']['name']) ? 'Аноним' : $opinion['user']['name'];
                        $review['rate'] = empty($opinion['rating']) ? 0 : (int)$opinion['rating'];

                        $period = empty($opinion['period']) ? null : $opinion['period'];
                        $pros = empty($opinion['plus']) ? null : $opinion['plus'];
                        $cons = empty($opinion['minus']) ? null : $opinion['minus'];
                        $text = empty($opinion['comment']) ? null : $opinion['comment'];

                        $commentData = ['Срок использования' => $period, 'Достоинства' => $pros, 'Недостатки'=> $cons, 'Комментарий' => $text];
                        $review['comment'] = 'ff';
                        $review['created_at'] = empty($opinion['insertStamp']) ? '1988-07-27' : $opinion['insertStamp'];
                        //dump($review);
                        yield $review;
                    }

                    if(count($json['data']['opinions']) >= $limit){
                        $offset+=$limit;
                        $loop = true;
                    } else {
                        $loop = false;
                    }

                } catch (\Throwable $exception)
                {
                    $loop = false;
                    print $exception->getMessage();
                    print $product;
                    yield;
                    break;
                }
            }

        }

    }

    protected function test1()
    {
        $client = PantherClient::createFirefoxClient();
        $client->execute("window.open('https://www.google.com', '_blank');");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        //$this->getDnsCookies();
        //$this->dnsCategoties();
        //$this->dns();
        //$cookStr = $this->getCook();
        //dump($cookStr);
      //$this->selen();
        //$this->guzl($cookStr);
        //$cook = $this->getCookMvideo();
        //$this->guzl(';');
       //$this->mvideo();
        //$this->dns();
        //$this->python();
        //$cook = $this->getDnsCookies();
        //$this->guzl($cook);
        //$this->getCookMvideo();
       // $this->guzl();
        //$this->mvideoCatalog();
        //$this->mvideoProduct();

        $this->getHeaders();

        $this->products = ['https://www.dns-shop.ru/product/ff984c04cf45d763/kronstejn-dla-tv-onkron-fm2-cernyj/#ff984c04-cf45-11eb-a27d-00155dbd7634',
            'https://www.dns-shop.ru/product/00eac371cffe2ff2/kreplenie-dla-monitorov-onkron-g80/#00eac371-cffe-11eb-a27c-00155dd2ff2c'];

        $reviews = [];
        foreach ($this->grabReviews() as $review) {
            //dump($review);
            $reviews[$review['product']][] = $review;
        }
        dump(count($reviews[$this->products[0]]));
        dump(count($reviews[$this->products[1]]));



        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
