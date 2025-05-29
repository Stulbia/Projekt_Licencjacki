<?php
/**
 * Class AppFixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class AppFixtures.
 *
 * This class is used to load data fixtures for testing or initial application setup.
 */
class AppFixtures extends Fixture
{
    /**
     * Load data fixtures into the database.
     *
     * This method is called when the fixtures are loaded. It can be used to create and persist
     * entities to the database for testing or initial setup purposes.
     *
     * @param ObjectManager $manager The ObjectManager instance used for persisting entities
     */
    public function load(ObjectManager $manager): void
    {
        $manager->flush();
    }
}
