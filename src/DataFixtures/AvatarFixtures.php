<?php

/**
 * User fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Avatar;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class UserFixtures.
 */
class AvatarFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: UserFixtures::class}
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    /**
     * Load data.
     */
    protected function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        $this->createMany(5, 'avatars', function (int $i) {
            /** @var User $user */
            $user = $this->getReference(sprintf('users_%s', strval($i)));
            $avatar = new Avatar();
            $avatar->setFilename(sprintf('%d.jpg', $i));
            $avatar->setUser($user);

            return $avatar;
        });
        $this->createMany(3, 'admins_avatars', function (int $i) {
            /** @var User $user */
            $user = $this->getReference(sprintf('admins_%s', strval($i)));
            $avatar = new Avatar();
            $avatar->setFilename(sprintf('penguin%d.jpg', $i));
            $avatar->setUser($user);

            return $avatar;
        });
        $this->manager->flush();
    }
}
