<?php declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\Config;
use App\Model\Database\EntityManagerDecorator;
use Nette\Application\UI;

final class ConfigPresenter extends UI\Presenter
{
    private EntityManagerDecorator $entityManager;
    private Config $email, $threshold;
    public function injectEntityManager(EntityManagerDecorator $em)
    {
        $this->entityManager = $em;
        $this->email = $em->find(Config::class, 'email') ?? new Config('email');
        $em->persist($this->email);
        $this->threshold = $em->find(Config::class, 'threshold') ?? new Config('threshold');
        $em->persist($this->threshold);
    }

    public function actionSave(UI\Form $form, $data)
    {
        $this->email->value = $data->email;
        $this->threshold->value = (string)$data->threshold;
        $this->entityManager->flush();
    }

    public function createComponentConfigForm()
    {
        $form = new UI\Form;
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