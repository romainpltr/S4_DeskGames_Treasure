<?php

namespace App\Controller\Admin;

use App\Entity\Carte;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

class CardCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Carte::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        $package = new Package(new EmptyVersionStrategy());
        return [
            IdField::new('valeur', 'Numéro de carte'),
            ImageField::new('image', 'Image de la carte')->setUploadDir('public/assets/images/objets/')->setBasePath($package->getUrl('assets/images/objets/')),
            TextField::new('objectif_id', 'Correspondance'),

        ];
    }
    
}
