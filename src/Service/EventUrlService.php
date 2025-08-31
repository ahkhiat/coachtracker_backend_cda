<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Convocation;
use App\Controller\Admin\EventCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class EventUrlService
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator) {}

    public function generateShowEventUrl(Event $event): string
    {
        return $this->adminUrlGenerator
            ->setController(EventCrudController::class)
            ->setAction('show')
            ->setEntityId($event->getId())
            ->generateUrl();
    }

    public function generateCreateConvocationsUrl(Event $event): string
    {
        return $this->adminUrlGenerator
            ->setController(EventCrudController::class)
            ->setAction('createConvocationsAction')
            ->setEntityId($event->getId())
            ->generateUrl();
    }

    public function generateCreatePresencesUrl(Event $event): string
    {
        return $this->adminUrlGenerator
            ->setController(EventCrudController::class)
            ->setAction('createPresencesAction')
            ->setEntityId($event->getId())
            ->generateUrl();
    }

    public function generateDeleteConvocationUrl(Event $event, string $convocationId): string
    {
        return $this->adminUrlGenerator
            ->setController(EventCrudController::class)
            ->setAction('deleteConvocationAction')
            ->setEntityId($event->getId())
            ->set('convocationId', $convocationId)
            ->generateUrl();
    }

}
