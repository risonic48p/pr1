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

final class CitilinkStrategy extends PartnerStrategyAbstract
{

    private string $partner;

    protected string $host = 'https://www.citilink.ru';
    protected int $sleep = 8;
    protected EntityRepository $productRepository;
    protected EntityRepository $reviewRepository;

    protected Client $client;



    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->productRepository = $this->entityManager->getRepository(Product::class);
        $this->reviewRepository = $this->entityManager->getRepository(Review::class);
        $this->partner = PartnerEnum::Citilink->value;
        $this->client = new Client([]);
    }


    public function grabProducts(): \Generator
    {
        $perPage = 20;
        $i = 1;
        $loop = true;

        while ($loop) {
            $body = '{"query":"query GetFullSearchProductsFilter($fullSearchProductsFilterInput:CatalogFilter_FullSearchFilterInput\u0021){fullSearchFilter(filter:$fullSearchProductsFilterInput){record{...FullSearchProductsFilter},error{... on CatalogFilter_ProductsFilterInternalError{__typename,message},... on CatalogFilter_ProductsFilterIncorrectArgumentsError{__typename,message}}}}fragment FullSearchProductsFilter on CatalogFilter_ProductsFilter{__typename,products{...ProductSnippetFull},sortings{...ProductsFilterSorting},groups{...SubcategoryProductsFilterGroup},categories{...FilterCategoryInfo},pageInfo{...Pagination},partialPageInfo{limit,offset},searchStrategy}fragment ProductSnippetFull on Catalog_Product{...ProductSnippetShort,propertiesShort{...ProductProperty},rating,counters{opinions,reviews},delivery{...ProductDelivery},stock{...ProductStock}}fragment ProductSnippetShort on Catalog_Product{...ProductSnippetBase,labels{...ProductLabel},yandexPay{withYandexSplit}}fragment ProductSnippetBase on Catalog_Product{id,name,shortName,slug,isAvailable,images{citilink{...Image}},price{...ProductPrice},category{id,name},brand{name},multiplicity,quantityInPackageFromSupplier,recommendations{hasAnalogProducts}}fragment Image on Image{sources{url,size}}fragment ProductPrice on Catalog_ProductPrice{current,old,club,clubPriceViewType,discount{percent},bonusPoints,sbpBonus}fragment ProductLabel on Catalog_Label{id,type,title,description,target{...Target},textColor,backgroundColor,expirationTime}fragment Target on Catalog_Target{action{...TargetAction},url,inNewWindow}fragment TargetAction on Catalog_TargetAction{id}fragment ProductProperty on Catalog_Property{name,value}fragment ProductDelivery on Catalog_ProductDelivery{__typename,self{...ProductSelfDelivery}}fragment ProductSelfDelivery on Catalog_ProductSelfDelivery{availabilityByDays{__typename,deliveryTime,storeCount},availableInFavoriteStores{store{id,shortName},productsCount}}fragment ProductStock on Catalog_Stock{__typename,countInStores,maxCountInStock}fragment ProductsFilterSorting on CatalogFilter_Sorting{id,name,slug,directions{id,isSelected,name,slug,isDefault}}fragment SubcategoryProductsFilterGroup on CatalogFilter_FilterGroup{id,isCollapsed,isDisabled,name,showInShortList,isGlobal,description,filter{... on CatalogFilter_ListFilter{__typename,isSearchable,logic,filters{id,isDisabled,isInShortList,isInTagList,isSelected,name,total,childGroups{id,isCollapsed,isDisabled,name,filter{... on CatalogFilter_ListFilter{__typename,isSearchable,logic,filters{id,isDisabled,isInShortList,isInTagList,name,isSelected,total}},... on CatalogFilter_RangeFilter{__typename,fromValue,isInTagList,maxValue,minValue,serifValues,scaleStep,toValue,unit}}}}},... on CatalogFilter_RangeFilter{__typename,fromValue,isInTagList,maxValue,minValue,serifValues,scaleStep,toValue,unit}}}fragment FilterCategoryInfo on CatalogFilter_CategoryInfo{category{...Category},isSelected,productsCount}fragment Category on Catalog_Category{__typename,id,name,slug}fragment Pagination on PageInfo{hasNextPage,hasPreviousPage,perPage,page,totalItems,totalPages}","variables":{"fullSearchProductsFilterInput":{"categoryId":"0","pagination":{"page":'. $i .',"perPage": '. $perPage .'},"conditions":[],"sorting":{"id":"","direction":"SORT_DIRECTION_DESC"},"searchText":"onkron","popularitySegmentId":"THREE"}}}';
            $res = $this->client->request('POST', $this->host . '/graphql/', [
                'body' => $body,
                'headers' => [
                    'content-type' => 'application/json',
                    'origin' => $this->host,
                    'sec-fetch-mode' => 'cors',
                    'sec-fetch-site' => 'same-origin'
                ]
            ]);

            $content = $res->getBody()->getContents();
            $json = json_decode($content, true);
            $productsPart = $json['data']['fullSearchFilter']['record']['products'];

            if(empty($productsPart)) {
                $loop = false;
                break;
            }

            $product = [];
            foreach($productsPart as $part) {
                $product['title'] = $part['name'];
                $product['url'] = $this->host . '/product/' . $part['slug'] . '-' . $part['id'];
                $product['partner'] = $this->partner;

                yield $product;
            }
            unset($content);
            unset($json);
            unset($productsPart);


            sleep($this->sleep);
            $i++;
        }

    }


    public function grabReviews(): \Generator
    {
        $perPage = 20;

        foreach($this->products as $product) {

            $urlParts = explode('-', $product->getUrl());
            $outId = (int)array_pop($urlParts);

            $i = 1;
            $loop = true;
            while ($loop) {
                $body = '{"query":"query($filter1:Catalog_ProductFilterInput\u0021$input2:UGC_OpinionsInput\u0021){product_ffff:product(filter:$filter1){opinions_03450_dce30:opinions(input:$input2){payload{summary{rating ratingCounters{__typename count percentage rating}bestOpinion{id creationDate pros cons text rating isBest author{id b2c{__typename ...on B2C_PublicUserNotFoundError{message}...on B2C_PublicUserB2C{id userInfo{nickname firstName avatar{sources{__typename url size}}}expert{isExpert}}}counters{__typename ...on B2C_UserActivityCountersNotFoundError{message}...on B2C_UserActivityCounters{review opinion question}}vendor{__typename ...on B2C_VendorNotFoundError{message}...on B2C_Vendor{brand{id name}categories{__typename id}}}}status source{sourceType description}vendor voteInfo{info{type counters{likes dislikes}isVoted}target{id}}authorNickname abuse{reasons{__typename id name targetType isMessageRequired withMessage}target}product{shortName}}}items{__typename id creationDate pros cons text rating isBest author{id b2c{__typename ...on B2C_PublicUserNotFoundError{message}...on B2C_PublicUserB2C{id userInfo{nickname firstName avatar{sources{__typename url size}}}expert{isExpert}}}counters{__typename ...on B2C_UserActivityCountersNotFoundError{message}...on B2C_UserActivityCounters{review opinion question}}vendor{__typename ...on B2C_VendorNotFoundError{message}...on B2C_Vendor{brand{id name}categories{__typename id}}}}status source{sourceType description}vendor voteInfo{info{type counters{likes dislikes}isVoted}target{id}}authorNickname abuse{reasons{__typename id name targetType isMessageRequired withMessage}target}product{shortName}}sortings{__typename id name sort isSelected}}pageInfo{page perPage totalItems totalPages hasNextPage hasPreviousPage}}}}","variables":{"filter1":{"id":"'. $outId .'"},"input2":{"pagination":{"page":'. $i .',"perPage":'. $perPage .'},"withGroup":true}}}';
                $res = $this->client->request('POST', $this->host . '/graphql/', [
                    'body' => $body,
                    'headers' => [
                        'content-type' => 'application/json',
                        'origin' => $this->host,
                        'sec-fetch-mode' => 'cors',
                        'sec-fetch-site' => 'same-origin'
                    ]
                ]);

                $content = $res->getBody()->getContents();
                $json = json_decode($content, true);
                $reviewsPart = $json['data']['product_ffff']['opinions_03450_dce30']['payload']['items'];

                if(empty($reviewsPart)) {
                    $loop = false;
                    break;
                }

                foreach($reviewsPart as $part) {
                    $review['product'] = $product;
                    $review['author_name'] = $part['authorNickname'];
                    $review['rate'] = (int)$part['rating'];
                    $review['market_id'] = empty($part['id']) ? null : $part['id'];

                    $pros = empty($part['pros']) ? null : $part['pros'];
                    $cons = empty($part['cons']) ? null : $part['cons'];
                    $text = empty($part['text']) ? null : $part['text'];
                    $commentData = ['Достоинства' => $pros, 'Недостатки' => $cons, 'Комментарий' => $text];

                    $review['comment'] = $this->commentTemplate($commentData);
                    $review['created_at'] = $part['creationDate'];

                    yield $review;
                }
                unset($content);
                unset($json);
                unset($reviewsPart);

                sleep($this->sleep);
                $i++;
            }

            sleep($this->sleep);
        }

    }


    public function grab(): bool
    {
        $this->productRepository->insertProducts($this->grabProducts());
        $this->products = $this->productRepository->findBy(['partner' => $this->partner]);
        $this->reviewRepository->insertReviews($this->grabReviews());
        sleep($this->sleep);
        return true;
    }

}

