<?php

namespace API\TaskBundle\Services;

use API\CoreBundle\Entity\Company;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class CompanyService
 *
 * @package API\TaskBundle\Services
 */
class CompanyService
{
    /** @var EntityManager */
    protected $em;

    /** @var  Router */
    protected $router;

    /**
     * ApiBaseService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * @param int $companyId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getFullCompany(int $companyId)
    {
        $query = $this->em->createQuery('
              SELECT c,
              cd FROM APICoreBundle:Company c
              JOIN c.companyData cd
              WHERE c.id = :companyId
          ')
        ->setParameter('companyId',$companyId);

        return $query->getOneOrNullResult();
    }

    /**
     * Return Entity Response which includes all data about Entity and Links to update/partialUpdate/delete
     *
     * @param Company $entity
     * @param string $entityName
     *
     * @return array
     */
    public function getEntityResponse($entity, string $entityName):array
    {
        return [
            'data' => $entity,
            'company_data'=> $entity->getCompanyData(),
            '_links' => $this->getEntityLinks($entity->getId(), $entityName),
        ];
    }

    /**
     * @param int $id
     *
     * @param string $entityName
     * @return array
     */
    private function getEntityLinks(int $id, string $entityName):array
    {
        return [
            'put' => $this->router->generate($entityName.'_update', ['id' => $id]),
            'patch' => $this->router->generate($entityName.'_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('company_delete', ['id' => $id]),
        ];
    }
}