<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use WhiteDigital\SiteTree\Entity\Redirect;

class RedirectFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (SiteTreeFixture::$references['redirect'] ?? [] as $reference) {
            /** @noinspection PhpParamsInspection */
            $fixture = (new Redirect())
                ->setIsActive(true)
                ->setCode(307)
                ->setContent('/eos')
                ->setNode($this->getReference($reference));
            $manager->persist($fixture);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SiteTreeFixture::class,
        ];
    }
}
