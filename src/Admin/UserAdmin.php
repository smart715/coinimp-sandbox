<?php

namespace App\Admin;

use App\Admin\Form\PasswordGeneratorButtonType;
use App\Entity\User;
use App\Manager\ProfileManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FOS\UserBundle\Model\UserManagerInterface;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserAdmin extends AbstractAdmin
{
    /** @var UserManagerInterface */
    private $userManager;

    /** @var EntityManagerInterface */
    private $em;

    /** @var LoggerInterface */
    private $logger;

    /** @var ProfileManagerInterface */
    private $profileCreator;

    public function __construct(string $code, string $class, string $baseControllerName, EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->logger = $container->get('monolog.logger.admin_action');
        parent::__construct($code, $class, $baseControllerName);
    }

    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection
            ->remove('delete')
            ->remove('export')
            ->add('reset_password', $this->getRouterIdParameter().'/reset_password');
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        if ('create' == $this->getFormAction())
            $formMapper
                ->add('email', null, ['attr' => ['placeholder' => 'Email']])
                ->add('plainPassword', TextType::class, [
                    'attr' => [
                        'class' => 'password-generator-input',
                        'placeholder' => 'Password',
                    ],
                ])
                ->add('password', PasswordGeneratorButtonType::class, [
                    'label' => false, // remove label above generate button
                ]);

        $formMapper->add('enabled');

        if ($this->isGranted('EDITROLES'))
            $formMapper->add('roles', ChoiceType::class, [
                'multiple' => true,
                'choices' => [
                    'USER' => 'ROLE_USER',
                    'ADMIN' => 'ROLE_ADMIN',
                    'SUPPORTER' => 'ROLE_SUPPORTER',
                    'COPYWRITER' => 'ROLE_COPYWRITER',
                    'SUPER_ADMIN' => 'ROLE_SUPER_ADMIN',
                ],
                'help' => '<p>Roles description:</p>'
                    .'<p>USER: Every account has this role by default (user can\'t access support panel and role can\'t be removed)</p>'
                    .'<p>ADMIN: It has \'view\' permission only</p>'
                    .'<p>SUPPORTER: It has \'view\' and limited \'edit\' permissions (supporter can edit only user status, not roles; and reset password)</p>'
                    .'<p>SUPER_ADMIN: It has \'view\' and \'edit\' permissions.</p>'
                    .'<p>COPYWRITER: it has \'view\' and \'edit\' permissions of the Content and Classification panels, it also has \'view\' permissions in the Users Administration panel.</p>',
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('email')
            ->add('roles');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('email', null, ['route' => ['name' => 'show']])
            ->add('roles', 'choice', [
                'multiple' => true,
                'choices' => [
                    'ROLE_USER' => 'USER',
                    'ROLE_ADMIN' => 'ADMIN',
                    'ROLE_SUPPORTER' => 'SUPPORTER',
                    'ROLE_COPYWRITER' => 'COPYWRITER',
                    'ROLE_SUPER_ADMIN' => 'SUPER_ADMIN',
                ],
            ])
            ->add('enabled');

        if ($this->isGranted('EDIT'))
            $listMapper->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'reset_password' => [
                        'template' => 'admin/list__action_reset_password.html.twig',
                    ],
                ],
            ]);
    }

    public function init(UserManagerInterface $userManager, ProfileManagerInterface $profileCreator): void
    {
        $this->setUserManager($userManager);
        $this->setProfileCreator($profileCreator);
    }

    /** {@inheritdoc} */
    public function prePersist($user): void
    {
        $this->userManager->updatePassword($user);
    }

    /** {@inheritdoc} */
    public function postPersist($user): void
    {
        $this->logger->info('add_user', [$user->getUsername()]);
        $this->profileCreator->createProfile($user->getId());
    }

    /** {@inheritdoc} */
    public function preUpdate($user): void
    {
        $changeSet = $this->changeSet($user);
        $this->logUpdates($user->getUserName(), $changeSet);
        parent::preUpdate($user);
    }

    protected function changeSet(User $object): array
    {
        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSets();
        return $uow->getEntityChangeSet($object);
    }

    protected function logUpdates(string $targetEmail, array $changeSet): void
    {
        if (isset($changeSet['roles'])) {
            $rolesBefore = implode($changeSet['roles'][0], ';');
            $rolesAfter = implode($changeSet['roles'][1], ';');
            $this->logger->info('edit_role', [$targetEmail, $rolesBefore, $rolesAfter]);
        }
        if (isset($changeSet['enabled'])) {
            $message = $changeSet['enabled'][1]? 'unlock_user': 'lock_user';
            $this->logger->info($message, [$targetEmail]);
        }
    }

    /** {@inheritdoc} */
    public function toString($object): string
    {
        return $object instanceof User
            ? $object->getEmail()
            : 'Unknown user';
    }

    private function setUserManager(UserManagerInterface $userManager): void
    {
        if (isset($this->userManager))
            throw new Exception('UserManager Dependency is already set');

        $this->userManager = $userManager;
    }

    private function setProfileCreator(ProfileManagerInterface $profileCreator): void
    {
        if (isset($this->profileCreator))
            throw new Exception('ProfileManager Dependency is already set');

        $this->profileCreator = $profileCreator;
    }

    private function getFormAction(): string
    {
        return $this->id($this->getSubject()) ? 'edit' : 'create';
    }
}
