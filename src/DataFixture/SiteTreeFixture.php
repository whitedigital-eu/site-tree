<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use WhiteDigital\SiteTree\Entity\SiteTree;

use function array_keys;
use function array_reverse;

class SiteTreeFixture extends Fixture
{
    public static array $references;

    public function __construct(private readonly ParameterBagInterface $bag)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $factory = Factory::create($this->bag->get('stof_doctrine_extensions.default_locale'));
        $factory->seed('whitedigital');

        $types = array_reverse(array_keys($this->bag->get('whitedigital.site_tree.types')));

        for ($i = 0; $i < 3; $i++) {
            $parent = $root = null;
            foreach ($types as $key => $type) {
                $fixture = (new SiteTree())
                    ->setTitle($factory->words(($key + 1) * 2, true))
                    ->setSlug(str_replace(' ', '_', $factory->words(1, true)))
                    ->setIsActive(true)
                    ->setIsVisible(true)
                    ->setMetaTitle($factory->text(25))
                    ->setMetaDescription($factory->text(75))
                    ->setType($type)
                    ->setParent($parent);

                if (0 === $key) {
                    $root = $fixture;
                }

                $fixture->setRoot($root);
                $manager->persist($fixture);
                $manager->flush();
                $parent = $fixture;
                $this->addReference($name = 'node' . $type . $i, $fixture);
                self::$references[$type][] = $name;
            }
        }

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $manager->getRepository(SiteTree::class)->recover();
        $manager->flush();
    }
}
