<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use WhiteDigital\SiteTree\Entity\Html;

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

        foreach (SiteTreeFixture::$references['html'] ?? [] as $reference) {
            /** @noinspection PhpParamsInspection */
            $fixture = (new Html())
                ->setIsActive(true)
                ->setContent($factory->randomHtml())
                ->setNode($this->getReference($reference));
            $manager->persist($fixture);
        }

        $manager->flush();
    }
}
