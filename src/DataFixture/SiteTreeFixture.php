<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Random\Randomizer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use WhiteDigital\SiteTree\Entity\SiteTree;

use function count;
use function strtolower;
use function strtoupper;

class SiteTreeFixture extends Fixture
{
    public static array $references;

    public function __construct(private readonly ParameterBagInterface $bag)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $factory = Factory::create('en_US');
        $factory->seed(2023);

        $levels = [];

        foreach ($this->bag->get('whitedigital.site_tree.types') as $type => $options) {
            $c = $options['single'] ? 1 : 2;
            for ($i = 0; $i < $c; $i++) {
                $title = strtoupper($type) . (1 === $c ? '' : '-' . $i);
                if (0 === $options['level'] && !count($levels[$options['level']] ?? [])) {
                    $title = $this->bag->get('stof_doctrine_extensions.default_locale');
                }
                $fixture = (new SiteTree())
                    ->setTitle($title)
                    ->setSlug(strtolower($title))
                    ->setIsActive(true)
                    ->setIsVisible(true)
                    ->setType($type)
                    ->setMetaTitle($factory->words(3, true))
                    ->setMetaDescription($factory->text(75));

                if (0 < $options['level']) {
                    $fixture->setParent($levels[$options['level'] - 1][self::randomArrayKey($levels[$options['level'] - 1])]);
                }

                $levels[$options['level']][] = $fixture;
                $manager->persist($fixture);
                $manager->flush();

                $this->addReference($name = 'node' . $type . $i, $fixture);
                self::$references[$type][] = $name;
            }
        }

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $manager->getRepository(SiteTree::class)->recover();
        $manager->flush();
    }

    protected static function randomArrayKey(array $array): mixed
    {
        return self::randomArrayKeys($array, 1)[0];
    }

    protected static function randomArrayKeys(array $array, ?int $count = null): array
    {
        $count ??= count($array) - 1;

        return (new Randomizer())->pickArrayKeys($array, $count);
    }
}
