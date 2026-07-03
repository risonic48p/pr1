<?php
namespace App\Service\ReviewParser\Strategy;

use Doctrine\ORM\EntityRepository;

abstract class PartnerStrategyAbstract
{
    abstract protected function grabProducts(): \Generator;
    abstract protected function grabReviews(): \Generator;

    abstract public function grab(): bool;

    public function commentTemplate(array $args): string
    {
        $res = '';
        foreach ($args as $k => $arg) {
            if($arg) {
                $arg = strip_tags($arg);
                $res.= "<p><strong>$k</strong><span>$arg</span></p>";
            }
        }
        return $res;
    }

}
