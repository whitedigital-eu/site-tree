<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Random\Randomizer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use WhiteDigital\SiteTree\Entity\SiteTree;

use function array_column;
use function array_unique;
use function count;
use function in_array;
use function strtolower;
use function strtoupper;

class SiteTreeFixture extends Fixture
{
    public static array $references;

    public function __construct(private readonly ParameterBagInterface $bag) {}

    public function load(ObjectManager $manager): void
    {
        $factory = Factory::create('en_US');
        $factory->seed(2023);

        $levels = [];

        $min = 0;
        if (!in_array(0, array_unique(array_column($this->bag->get('whitedigital.site_tree.types'), 'level')), true)) {
            $min = -1;
        }

        foreach ($this->bag->get('whitedigital.site_tree.types') as $type => $options) {
            $c = $options['single'] ? 1 : 2;
            for ($i = 0; $i < $c; $i++) {
                $title = strtoupper($type) . (1 === $c ? '' : '-' . $i);
                $k = $options['level'] + $min;
                if (0 === $k && !count($levels[$k] ?? [])) {
                    $title = $this->bag->get('stof_doctrine_extensions.default_locale');
                    $min = 0;
                }
                $fixture = (new SiteTree())
                    ->setTitle($title)
                    ->setSlug(strtolower($title))
                    ->setIsActive(true)
                    ->setIsVisible(true)
                    ->setType($type)
                    ->setMetaTitle($factory->words(3, true))
                    ->setMetaDescription($factory->text(75));

                if (0 < $k) {
                    if ([] !== ($levels[$k - 1] ?? [])) {
                        if (null !== ($parent = $levels[$k - 1][self::randomArrayKey($levels[$k - 1])] ?? null)) {
                            $fixture->setParent($parent);
                        }
                    }
                }

                $levels[$k][] = $fixture;
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
