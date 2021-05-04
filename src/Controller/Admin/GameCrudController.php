<?php

namespace App\Controller\Admin;


use App\Entity\Partie;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

use Symfony\Component\PropertyAccess\PropertyAccess;


class GameCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Partie::class;
    }

    
    public function configureFields(string $pageName): iterable
    {

        return [
            IdField::new('id'),
            DateField::new('created'),
            TextField::new('User1Id'),
            TextField::new('User2Id'),
            TextField::new('winner'),
            DateField::new('ended'),
        ];
    }
    
}
