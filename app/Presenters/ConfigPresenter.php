<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\Config;
use App\Model\Database\EntityManagerDecorator;
use Nette;
use Nette\Application\UI\Form;

final class ConfigPresenter extends Nette\Application\UI\Presenter
{
    private EntityManagerDecorator $entityManager;
    private Config $email, $threshold;
    public function injectEntityManager(EntityManagerDecorator $em)
    {
        $M = 'App\Model\Database';
        $this->entityManager = $em;
        $this->email = $em->find("$M\Config", 'email') ?? new Config('email');
        $em->persist($this->email);
        $this->threshold = $em->find("$M\Config", 'threshold') ?? new Config('threshold');
        $em->persist($this->threshold);
    }

    public function actionSave(Form $form, $data)
    {
        $request = $this->getHttpRequest();
        $this->email->value = $data->email;
        $this->threshold->value = (string)$data->threshold;
        $this->entityManager->flush();
        $this->flashMessage('Saved');
        //$this->redirect('this');
    }

    public function createComponentConfigForm()
    {
        $form = new Form;
        $form->addEmail('email', 'Email address for alerts')
            ->setDefaultValue($this->email->value)
            ->setRequired();
        $form->addFloat('threshold', 'Min price difference for alert')
            ->setDefaultValue($this->threshold->value)
            ->setRequired();
        $form->addSubmit('submit', 'Save');
        $form->onSuccess[] = [$this, 'actionSave'];
        return $form;
    }
}