<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use WhiteDigital\SiteTree\Entity\Html;

use function array_rand;

class HtmlFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly ParameterBagInterface $bag)
    {
    }

    public function getDependencies(): array
    {
        return [
            SiteTreeFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $factory = Factory::create($this->bag->get('stof_doctrine_extensions.default_locale'));
        $factory->seed('whitedigital');

        for ($i = 0; $i < 10; $i++) {
            /** @noinspection PhpParamsInspection */
            $fixture = (new Html())
                ->setIsActive(true)
                ->setContent($factory->randomHtml())
                ->setNode($this->getReference('nodehtml' . $this->randomArrayKey(SiteTreeFixture::$references['html'])));

            $manager->persist($fixture);
        }

        $manager->flush();
    }

    private function randomArrayKey(array $array): string|int|array
    {
        return array_rand(array: $array);
    }
}
