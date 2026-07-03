<?php

namespace App\Service\ReviewParser\Strategy;


use App\Service\ReviewParser\Strategy\PartnerStrategyAbstract;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Enum\PartnerEnum;
use App\Entity\Product;
use App\Entity\Review;
use GuzzleHttp\Psr7;

final class DnsStrategy extends PartnerStrategyAbstract
{

    private string $partner;

    protected string $host = 'https://www.dns-shop.ru';
    protected string $apiHost = 'https://restapi.dns-shop.ru';

    protected int $sleep = 3;
    protected EntityRepository $productRepository;
    protected EntityRepository $reviewRepository;

    protected Client $client;

    protected array $headers;

    protected array $categories;

    protected int $expiry;



    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->productRepository = $this->entityManager->getRepository(Product::class);
        $this->reviewRepository = $this->entityManager->getRepository(Review::class);
        $this->partner = PartnerEnum::Dns->value;
        $this->client = new Client([]);
    }


    protected function getHeaders(): array
    {
        $result = [];
        $resStr='';
        $command = escapeshellcmd('./python/dns-shop-cookies.sh');
        $output = shell_exec($command);

        $c = json_decode(trim($output), true);

        foreach ($c as $k => $v) {
                $result[$v['name']] = $v['value'];
                $resStr.= $v['name'] . '=' . $v['value'] . '; ';

                if($v['name'] === 'qrator_ssid2') {
                    $this->expiry = (int)$v['expiry'];
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

    private function getCategoties()
    {
        $client = new Client();
        $response = $client->get($this->host . '/brand/onkron/', [
            'headers' => $this->headers
        ]);

        $crawler = new Crawler($response->getBody()->getContents());
        $crawler->filter('#catalog a')->each(function (Crawler $node, $i) {
            $this->categories[] = $node->attr('href');
        });
    }


    public function grabProducts(): \Generator
    {
        $client = new Client();

        foreach ($this->categories as $catUrl) {
            sleep($this->sleep);
            $i = 1;
            $loop = true;

            while ($loop) {
                sleep($this->sleep);
                $productsPerPage = [];
                $reqUrl = $this->host . $catUrl . '&p=' . $i;
                $response = $client->get($reqUrl, [
                    'headers' => $this->headers
                ]);
                $crawler = new Crawler($response->getBody()->getContents());

                $crawler->filter('.catalog-products .catalog-product')
                    ->each(function (Crawler $node, $i) use (&$productsPerPage) {
                    $product = [];
                    $linkNode = $node->filter('a.catalog-product__name');
                    $product['title'] = $linkNode->text();
                    $product['url'] = $this->host . $linkNode->attr('href') . '#' . $node->attr('data-product');
                    $product['partner'] = $this->partner;
                    $productsPerPage[] = $product;
                });
                foreach ($productsPerPage as $product) {
                    yield $product;
                }

                if(empty($productsPerPage)) {
                    $loop = false;
                }
                $i++;
            }
        }

       yield;
    }

    protected function checkExpiry():void
    {
        $term = strtotime('-3 minutes', $this->expiry);
        $currentTimestamp = time();
        if($term <= $currentTimestamp) {
            $this->getHeaders();
        }

    }

    public function grabReviews(): \Generator
    {
        $client = new Client();
        foreach ($this->products as $product) {

            $urlParts = explode('#', $product->getUrl());

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
                    $this->checkExpiry();
                    sleep($this->sleep);
                    $resp = $client->request('POST', $this->apiHost . '/v1/opinion/get-product-opinions', [
                        'multipart' => $multipart,
                        'headers' => $this->headers,
                    ]);

                    $json = json_decode($resp->getBody()->getContents(), true);
                    foreach ($json['data']['opinions'] as $opinion) {
                        $review['product'] = $product;
                        $review['author_name'] = empty($opinion['user']['name']) ? 'Аноним' : $opinion['user']['name'];
                        $review['rate'] = empty($opinion['rating']) ? 0 : (int)$opinion['rating'];
                        $review['market_id'] = empty($opinion['id']) ? null : $opinion['id'];

                        $period = empty($opinion['period']) ? null : $opinion['period'];
                        $pros = empty($opinion['plus']) ? null : $opinion['plus'];
                        $cons = empty($opinion['minus']) ? null : $opinion['minus'];
                        $text = empty($opinion['comment']) ? null : $opinion['comment'];

                        $commentData = ['Достоинства' => $pros, 'Недостатки'=> $cons, 'Комментарий' => $text];
                        $review['comment'] = $this->commentTemplate($commentData);
                        $review['created_at'] = empty($opinion['insertStamp']) ? '1988-07-27' : $opinion['insertStamp'];
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
                    print $product->getUrl();
                    yield;
                    break;
                }
            }

        }

    }


    public function grab(): bool
    {
        $this->getHeaders();
        sleep($this->sleep);
        $this->getCategoties();
        sleep($this->sleep);
        $this->productRepository->insertProducts($this->grabProducts());
        $this->products = $this->productRepository->findBy(['partner' => $this->partner]);
        sleep($this->sleep);
        $this->reviewRepository->insertReviews($this->grabReviews());

        sleep($this->sleep);
        return true;
    }

}

