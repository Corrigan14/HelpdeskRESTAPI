<?php

namespace API\CoreBundle\Services;

use API\CoreBundle\Entity\Company;
use API\CoreBundle\Repository\CompanyRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class CompanyService
 * @package API\CoreBundle\Services
 */
class CompanyService
{
    /**
     * @var EntityManager
     */
    private $em;

    /** @var Router */
    private $router;


    /**
     * UserService constructor.
     *
     * @param EntityManager $em
     * @param Router $router
     */
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * Return's array of all companies
     *
     * @param int $page
     *
     * @return array
     */
    public function getCompaniesResponse(int $page)
    {
        /** @var CompanyRepository $companyRepository */
        $companyRepository = $this->em->getRepository('APICoreBundle:Company');
        $companies = $companyRepository->getAllEntities(false,$page);

        $response = [
            'data' => $companies,
        ];

        $pagination = HateoasHelper::getPagination(
            $this->router->generate('company_list'),
            $page,
            $companyRepository->countEntities(false),
            CompanyRepository::LIMIT
        );

        return array_merge($response, $pagination);
    }

    /**
     * Return Company Response which includes all data about Company Entity and Links to update/partialUpdate/delete
     *
     * @param Company $company
     * @return array
     */
    public function getCompanyResponse(Company $company)
    {
        return[
            'data' => $company,
            '_links' => $this->getCompanyLinks($company->getId()),
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getCompanyLinks(int $id)
    {
        return [
            'put' => $this->router->generate('company_update', ['id' => $id]),
            'patch' => $this->router->generate('company_partial_update', ['id' => $id]),
            'delete' => $this->router->generate('company_delete', ['id' => $id]),
        ];
    }
}