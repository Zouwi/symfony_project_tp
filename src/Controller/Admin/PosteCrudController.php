<?php

namespace App\Controller\Admin;

use App\Entity\Poste;
use App\Entity\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PosteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Poste::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextEditorField::new('content'),
            DateField::new('date'),
            DateField::new('maj'),
            AssociationField::new('tag'),
            TextareaField::new('imageFile')
            ->setFormType(VichImageType::class)
            ->setLabel('Image')
        ];
    }
}
