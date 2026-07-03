<?php

namespace App\Service\ReviewParser;

use App\Enum\PartnerEnum;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ReviewParser\Strategy\CitilinkStrategy;
use App\Service\ReviewParser\Strategy\DnsStrategy;
use App\Service\ReviewParser\Strategy\MvideoStrategy;

final class ReviewParserService
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }
    private PartnerEnum $parserMode;

    private function getStrategies(): array
    {
        $strategies = [];

        switch ($this->parserMode->value) {
            case PartnerEnum::Citilink->value:
                $strategies[] = new CitilinkStrategy($this->entityManager);
                break;
            case PartnerEnum::Dns->value:
                $strategies[] = new DnsStrategy($this->entityManager);
                break;
            case PartnerEnum::Mvideo->value:
                $strategies[] = new MvideoStrategy($this->entityManager);
                break;
            case PartnerEnum::All->value:
                $strategies[] = new CitilinkStrategy($this->entityManager);
                $strategies[] = new MvideoStrategy($this->entityManager);
                $strategies[] = new DnsStrategy($this->entityManager);
                break;
        }

        return $strategies;
    }


    public function run(string $mode): bool
    {
        $this->parserMode = PartnerEnum::from($mode);
        $strategies = $this->getStrategies();

        foreach ($strategies as $strategy) {
            $strategy->grab();
        }

        return true;
    }

}

