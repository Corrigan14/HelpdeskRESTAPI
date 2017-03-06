<?php

namespace API\CoreBundle\Repository;

use API\CoreBundle\Entity\User;
use API\CoreBundle\Entity\UserData;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Traits\UserRepositoryTrait;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    /**
     * Default User fields in case no custom fields are defined
     */
    const LIMIT = 10;

    use UserRepositoryTrait;

    /**
     * Return all info about user (User, UserData Entity)
     *
     * @param int $page
     *
     * @param string $isActive
     * @param string $order
     * @return array
     */
    public function getCustomUsers(int $page = 1, $isActive, string $order)
    {
        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('u')
                ->select('u,d,userRole,company,companyData,companyAttribute')
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->leftJoin('u.company', 'company')
                ->leftJoin('company.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->where('u.is_active = :isActive')
                ->groupBy('u.id')
                ->orderBy('u.username', $order)
                ->setParameter('isActive', $isActiveParam);
        } else {
            $query = $this->createQueryBuilder('u')
                ->select('u,d,userRole,company,companyData,companyAttribute')
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->leftJoin('u.company', 'company')
                ->leftJoin('company.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->groupBy('u.id')
                ->orderBy('u.username', $order);
        }

        // Pagination
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        } else {
            $query->setFirstResult(0);
        }

        $query->setMaxResults(self::LIMIT);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = $paginator->count();

        return [
            'count' => $count,
            'array' => $this->formatData($paginator)
        ];
    }

    /**
     * @param string|bool $term
     * @param int $page
     * @param string|bool $isActive
     * @param string $order
     * @return array
     */
    public function getUsersSearch($term, int $page, $isActive, string $order):array
    {
        $parameters = [];
        if ('true' === $isActive || 'false' === $isActive) {
            if ($isActive === 'true') {
                $isActiveParam = 1;
            } else {
                $isActiveParam = 0;
            }
            $query = $this->createQueryBuilder('u')
                ->select('u,d,userRole,company,companyData,companyAttribute')
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->leftJoin('u.company', 'company')
                ->leftJoin('company.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->where('u.is_active = :isActive')
                ->groupBy('u.id')
                ->orderBy('u.username', $order);
            $parameters['isActive'] = $isActiveParam;
        } else {
            $query = $this->createQueryBuilder('u')
                ->select('u,d,userRole,company,companyData,companyAttribute')
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->leftJoin('u.company', 'company')
                ->leftJoin('company.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->groupBy('u.id')
                ->orderBy('u.username', $order);
        }

        if ($term) {
            $query->andWhere('u.username LIKE :term OR u.email LIKE :term OR company.title LIKE :term');
            $parameters['term'] = '%' . $term . '%';
        }
        $query->setParameters($parameters);

        // Pagination
        if (1 < $page) {
            $query->setFirstResult(self::LIMIT * $page - self::LIMIT);
        } else {
            $query->setFirstResult(0);
        }

        $query->setMaxResults(self::LIMIT);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = $paginator->count();

        return [
            'count' => $count,
            'array' => $this->formatData($paginator)
        ];
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getUserResponse(int $userId): array
    {
        $query = $this->createQueryBuilder('u')
            ->select('u,d,userRole,company,companyData,companyAttribute')
            ->leftJoin('u.detailData', 'd')
            ->leftJoin('u.user_role', 'userRole')
            ->leftJoin('u.company', 'company')
            ->leftJoin('company.companyData', 'companyData')
            ->leftJoin('companyData.companyAttribute', 'companyAttribute')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery();

        return $this->processData($query->getSingleResult());
    }

    /**
     * @return array
     */
    public function getAllUserEntitiesWithIdAndTitle():array
    {
        $query = $this->createQueryBuilder('user')
            ->select('user.id, user.username')
            ->where('user.is_active = :isActive')
            ->setParameter('isActive', true);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $paginator
     * @return array
     */
    private function formatData($paginator):array
    {
        $response = [];
        /** @var User $data */
        foreach ($paginator as $data) {
            $response[] = $this->processData($data);
        }

        return $response;
    }

    /**
     * @param User $data
     * @return array
     */
    private function processData(User $data):array
    {
        $detailData = $data->getDetailData();
        $detailDataArray = [];
        if ($detailData) {
            $detailDataArray = [
                'id' => $data->getDetailData()->getId(),
                'name' => $data->getDetailData()->getName(),
                'surname' => $data->getDetailData()->getSurname(),
                'title_before' => $data->getDetailData()->getTitleBefore(),
                'title_after' => $data->getDetailData()->getTitleAfter(),
                'function' => $data->getDetailData()->getFunction(),
                'mobile' => $data->getDetailData()->getMobile(),
                'tel' => $data->getDetailData()->getTel(),
                'fax' => $data->getDetailData()->getFax(),
                'signature' => $data->getDetailData()->getSignature(),
                'street' => $data->getDetailData()->getStreet(),
                'city' => $data->getDetailData()->getCity(),
                'zip' => $data->getDetailData()->getZip(),
                'country' => $data->getDetailData()->getCountry(),
                'facebook' => $data->getDetailData()->getFacebook(),
                'twitter' => $data->getDetailData()->getTwitter(),
                'linkdin' => $data->getDetailData()->getLinkdin(),
                'google' => $data->getDetailData()->getGoogle(),
            ];
        }

        $company = $data->getCompany();
        $companyArray = [];
        if ($company) {
            $companyArray = [
                'id' => $company->getId(),
                'title' => $company->getTitle()
            ];
        }

        $response = [
            'id' => $data->getId(),
            'username' => $data->getUsername(),
            'email' => $data->getEmail(),
            'language' => $data->getLanguage(),
            'is_active' => $data->getIsActive(),
            'image' => $data->getImage(),
            'detailData' => $detailDataArray,
            'user_role' => [
                'id' => $data->getUserRole()->getId(),
                'title' => $data->getUserRole()->getTitle(),
                'description' => $data->getUserRole()->getDescription(),
                'homepage' => $data->getUserRole()->getHomepage(),
                'acl' => $data->getUserRole()->getAcl(),
                'order' => $data->getUserRole()->getOrder(),
            ],
            'company' => $companyArray
        ];

        return $response;
    }
}
