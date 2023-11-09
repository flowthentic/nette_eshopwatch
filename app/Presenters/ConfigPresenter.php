<?php declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\Config;
use App\Model\Database\EntityManagerDecorator;
use Nette\Application\UI;

final class ConfigPresenter extends UI\Presenter
{
    private EntityManagerDecorator $em;
    private Config $email, $threshold;
    public function injectEntityManager(EntityManagerDecorator $em) {
        $repo = $em->getRepository(Config::class);
        $this->email = $repo->getByKey('email');
        $this->threshold = $repo->getByKey('threshold');
        $this->em = $em;
    }

    public function actionSave(UI\Form $form, $data) {
        $this->email->value = $data->email;
        $this->threshold->value = (string)$data->threshold;
        $this->em->flush();
    }

    public function createComponentConfigForm() {
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
