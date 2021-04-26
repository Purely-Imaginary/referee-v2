<?php

namespace App\Controller\Admin;

use App\Entity\CalculatedMatch;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CalculatedMatchCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CalculatedMatch::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('time'),
        ];
    }
}
