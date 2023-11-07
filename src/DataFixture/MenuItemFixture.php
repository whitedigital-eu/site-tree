<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataFixture;

use Doctrine\Persistence\ObjectManager;
use WhiteDigital\Config\DataFixture\AbstractFixture;
use WhiteDigital\SiteTree\Entity\MenuItem;

class MenuItemFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $fixture = (new MenuItem())
            ->setNode($this->getNode('menu_item'))
            ->setTitle(self::words());

        $manager->persist($fixture);
        $manager->flush();

        $this->reference($fixture);
    }

    public function getDependencies(): array
    {
        $dependencies = parent::getDependencies();
        $dependencies[] = SiteTreeFixture::class;

        return $dependencies;
    }
}
