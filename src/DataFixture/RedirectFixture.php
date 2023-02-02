<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use WhiteDigital\SiteTree\Entity\Redirect;

use function array_rand;

class RedirectFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            /** @noinspection PhpParamsInspection */
            $fixture = (new Redirect())
                ->setIsActive(true)
                ->setCode(307)
                ->setContent('/eos')
                ->setNode($this->getReference('noderedirect' . $this->randomArrayKey(SiteTreeFixture::$references['redirect'])));

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

    private function randomArrayKey(array $array): string|int|array
    {
        return array_rand(array: $array);
    }
}
