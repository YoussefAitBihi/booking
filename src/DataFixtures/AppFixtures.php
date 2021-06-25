<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Image;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    /** @var UserPasswordEncoderInterface $encoder contains the password encoder*/
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        define("P_D", dirname(dirname(__DIR__)));

        $faker = Factory::create('fr-Fr');

        // Delete all thumbnails when I want to create new adverts
        $dir_thumbnails = P_D . "/public/uploads/thumbnails";
        // Array contains all thumbnails
        $thumbnails = scandir($dir_thumbnails);

        if (!empty($thumbnails)) {
            foreach ($thumbnails as $thumbnail) {
                if (is_file($dir_thumbnails . "/" . $thumbnail)) {
                    unlink($dir_thumbnails . "/" . $thumbnail);
                }
            }
        }

        // Delete all sliders
        $dir_sliders = P_D . "/public/uploads/sliders";
        // Array contains all sliders
        $sliders = scandir($dir_sliders);

        if (!empty($sliders)) {
            foreach ($sliders as $slider) {
                if (is_file($dir_sliders . '/' . $slider)) {
                    unlink($dir_sliders . '/' . $slider);
                }
            }
        }

        // Delete all avatars

        $dir_avatars = P_D . "/public/uploads/avatars";
        // Array contains all avatars
        $avatars = scandir($dir_avatars);

        if (!empty($avatars)) {
            foreach ($avatars as $avatar) {
                if (is_file($dir_avatars . '/' . $avatar)) {
                    unlink($dir_avatars . '/' . $avatar);
                }
            }
        }

        // Fake users
        for ($i = 0; $i < 20; $i++) {

            $user = new User();

            $firstName = $faker->firstName('male');
            $lastName = $faker->lastName;
            $email = $faker->email;
            $password = $this->encoder->encodePassword($user, 'password');
            $avatar = $faker->file(
                P_D . '/tmp avatars',
                P_D . '/public/uploads/avatars',
                false
            );
            $description = $faker->paragraphs(mt_rand(4, 6), true);

            $user
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setEmail($email)
                ->setPassword($password)
                ->setAvatar($avatar)
                ->setDescription($description);

            $manager->persist($user);

            // Fake Ads
            for ($j = 0; $j < mt_rand(2, 5); $j++) {

                $ad = new Ad();

                $title = $faker->sentence();
                $introduction = $faker->paragraph(mt_rand(2, 3));
                $description = implode(
                    "\n",
                    $faker->paragraphs(mt_rand(3, 5))
                );
                $rooms = $faker->numberBetween(3, 5);
                $price = $faker->randomNumber(3);
                $thumbnail = $faker->file(
                    P_D . "/tmp",
                    P_D . "/public/uploads/thumbnails" . DIRECTORY_SEPARATOR,
                    false
                );
                $city = [
                    "Casablanca", 
                    "Rabat", 
                    "Fes", 
                    "Meknes", 
                    "Tanger", 
                    "Tetouane", 
                    "Oujda", 
                    "Mohammedia"
                ];

                $ad
                    ->setTitle($title)
                    ->setIntroduction($introduction)
                    ->setDescription($description)
                    ->setRooms($rooms)
                    ->setPrice($price)
                    ->setThumbnail($thumbnail)
                    ->setCity($city[mt_rand(0, count($city) - 1)])
                    ->setOwner($user);

                $manager->persist($ad);

                // Fake Sliders
                for ($k = 0; $k < mt_rand(3, 5); $k++) {

                    $image = new Image();

                    $image
                        ->setImage($faker->file(
                            P_D . "/tmp",
                            P_D . "/public/uploads/sliders",
                            false
                        ))
                        ->setAd($ad)
                        ->setCaption($faker->sentence());

                    $manager->persist($image);
                }
            }
        }

        $manager->flush();
    }
}
