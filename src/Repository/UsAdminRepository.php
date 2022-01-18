<?php

namespace App\Repository;

use App\Entity\UsAdmin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Repository\V1\UsAdminEm;

/**
 * @method UsAdmin|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsAdmin|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsAdmin[]    findAll()
 * @method UsAdmin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsAdminRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsAdmin::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof UsAdmin) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1($entityManager) {
        return new UsAdminEm($entityManager);
    }

    /**
     * Obtenermos el repositorio de esta clase de la version 1
     */
    public function getV1SCPCotz($entityManager) {
        return new UsAdminEm($entityManager);
    }
}
