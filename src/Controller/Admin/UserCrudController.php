<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        $package = new Package(new EmptyVersionStrategy());
        return [
            TextField::new('firstname', 'Prénom'),
            TextField::new('lastname', 'Nom'),
            TextField::new('username', 'Pseudo'),
            TextField::new('email', 'Email'),
            ChoiceField::new('roles', 'Roles')->setChoices(['Administrateur' => 'ROLE_ADMIN', 'Joueur' => 'ROLE_JOUEUR' ])->allowMultipleChoices(),
            DateField::new('birthday', 'Date de Naissance'),
            ImageField::new('ProfilPicture', 'Image de Profil par default')->setUploadDir('public/assets/images/ProfilPicture/defaults')->setBasePath($package->getUrl('assets/images/ProfilPicture/defaults/')),
            
            
        ];
    }
    
}
