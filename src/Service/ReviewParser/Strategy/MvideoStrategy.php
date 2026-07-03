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
use Symfony\Component\Panther\Client as PantherClient;

final class MvideoStrategy extends PartnerStrategyAbstract
{

    private string $partner;

    protected string $host = 'https://www.mvideo.ru';
    protected int $sleep = 6;
    protected EntityRepository $productRepository;
    protected EntityRepository $reviewRepository;

    protected $client;

    protected array $headers;

    protected array $catalog;



    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->productRepository = $this->entityManager->getRepository(Product::class);
        $this->reviewRepository = $this->entityManager->getRepository(Review::class);
        $this->partner = PartnerEnum::Mvideo->value;
    }

    private function getHeaders(): array
    {
        $client = PantherClient::createFirefoxClient();
        $client->request('GET', $this->host);
        $cookStr = '';
        $cookies = $client->getCookieJar()->all();
        foreach ($cookies as $cookie) {
            $cookStr.= $cookie->getName() . '=' . $cookie->getValue() . '; ';
        }

        $headers = [
            'User-Agent'                => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0',
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language'           => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding'           => 'gzip, deflate, br, zstd',
            'Connection'                => 'keep-alive',
            //'Referer'                   => 'https://www.mvideo.ru/promo/promocatalog?from=under_search',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest'            => 'document',
            'Sec-Fetch-Mode'            => 'navigate',
            'Sec-Fetch-Site'            => 'none',
            'Sec-Fetch-User'            => '?1',
            'DNT'                       => '1',
            'Sec-GPC'                   => '1',
            'Priority'                  => 'u=0, i',
            'TE'                        => 'trailers',
            'Cookie' => $cookStr,
    ];

        $this->headers = $headers;
        $client->quit();
        return $this->headers;
    }

    private function getCatalog(): array
    {
        $client = new Client();

        $response = $client->get('https://www.mvideo.ru/bff/products/v3/search', [
            'query' => [
                'offset' => '0',
                'limit' => '9999',
                'query' => 'onkron'
            ],
            'headers' => $this->headers
        ]);


        $content = json_decode($response->getBody()->getContents(), true);
        $this->catalog = $content['body']['products'];
        return $this->catalog;
    }


    public function grabProducts(): \Generator
    {
        $client = new Client();
        foreach ($this->catalog as $productId) {
            sleep($this->sleep);
            $product = [];
            try {
                $response = $client->get($this->host . '/bff/product-details?productId='. $productId, [
                    'headers' => $this->headers
                ]);
                $content = json_decode($response->getBody()->getContents(), true);

                $product['title'] = $content['body']['name'];
                $product['url'] = $this->host . '/products/' . $content['body']['nameTranslit'] . '-' . $content['body']['productId'];
                $product['partner'] = $this->partner;
                yield $product;

            } catch (\Throwable $exception)
            {
                yield $product;
                continue;
            }
        }

    }


    public function grabReviews(): \Generator
    {
        $client = new Client();
        $i = 0;
        foreach ($this->products as $product) {
            $i++;
            if($i >= 50) {
                $i = 1;
                $this->getHeaders();
            }
            sleep($this->sleep);
            $urlParts = explode('-', $product->getUrl());
            $outId = (int)array_pop($urlParts);

            try {
                $response = $client->get($this->host . '/bff/reviews/product?productId='. $outId, [
                    'headers' => $this->headers
                ]);
                $content = json_decode($response->getBody()->getContents(), true);
                $reviews = $content['body']['reviews'];

                foreach ($reviews as $r) {
                    $review = [];
                    $review['product'] = $product;
                    $review['author_name'] = empty($r['name']) ? 'Аноним' : $r['name'];
                    $review['rate'] = empty($r['score']) ? 0 : (int)$r['score'];
                    $review['market_id'] = empty($r['reviewId']) ? null : $r['reviewId'];


                    $pros = empty($r['benefits']) ? null : $r['benefits'];
                    $cons = empty($r['drawbacks']) ? null : $r['drawbacks'];
                    $text = empty($r['text']) ? null : $r['text'];

                    $commentData = ['Достоинства' => $pros, 'Недостатки' => $cons, 'Комментарий' => $text];
                    $review['comment'] = $this->commentTemplate($commentData);
                    $review['created_at'] = empty($r['date']) ? '1988-07-27' : $r['date'];
                    yield $review;
                }

            } catch (\Throwable $exception)
            {
                print $exception->getMessage();
                yield $review;
                continue;
            }

        }

    }


    public function grab(): bool
    {
        $this->getHeaders();
        sleep($this->sleep);
        $this->getCatalog();
       sleep($this->sleep);
        $this->productRepository->insertProducts($this->grabProducts());
        $this->products = $this->productRepository->findBy(['partner' => $this->partner]);
        $this->getHeaders();
        sleep($this->sleep);
        $this->reviewRepository->insertReviews($this->grabReviews());

        sleep($this->sleep);
        return true;
    }

}

