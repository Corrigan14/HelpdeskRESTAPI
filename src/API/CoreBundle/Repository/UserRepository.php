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
     * @param int $limit
     * @return array
     */
    public function getCustomUsers(int $page = 1, $isActive, string $order, int $limit)
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
                ->orderBy('u.username', $order)
                ->distinct()
                ->setParameter('isActive', $isActiveParam);
        } else {
            $query = $this->createQueryBuilder('u')
                ->select('u,d,userRole,company,companyData,companyAttribute')
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->leftJoin('u.company', 'company')
                ->leftJoin('company.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->distinct()
                ->orderBy('u.username', $order);
        }

        if (999 !== $limit) {
            // Pagination
            if (1 < $page) {
                $query->setFirstResult($limit * $page - $limit);
            } else {
                $query->setFirstResult(0);
            }

            $query->setMaxResults($limit);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            $count = $paginator->count();

            return [
                'count' => $count,
                'array' => $this->formatData($paginator)
            ];
        } else {
            // Return all entities
            return [
                'array' => $this->formatData($query->getQuery()->getArrayResult(), true)
            ];
        }

    }

    /**
     * @param string|bool $term
     * @param int $page
     * @param string|bool $isActive
     * @param string $order
     * @param int $limit
     * @return array
     */
    public function getUsersSearch($term, int $page, $isActive, string $order, int $limit): array
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
                ->orderBy('u.username', $order)
                ->distinct();
            $parameters['isActive'] = $isActiveParam;
        } else {
            $query = $this->createQueryBuilder('u')
                ->select('u,d,userRole,company,companyData,companyAttribute')
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->leftJoin('u.company', 'company')
                ->leftJoin('company.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->orderBy('u.username', $order)
                ->distinct();
        }

        if ($term) {
            $query->andWhere('u.username LIKE :term OR u.email LIKE :term OR company.title LIKE :term');
            $parameters['term'] = '%' . $term . '%';
        }
        $query->setParameters($parameters);

        if (999 !== $limit) {
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
        }else{
            // Return all entities
            return [
                'array' => $this->formatData($query->getQuery()->getArrayResult(), true)
            ];
        }
    }

    /**
     * @param int $userId
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getUserResponse(int $userId): array
    {
        $query = $this->createQueryBuilder('u')
            ->select('u')
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
    public function getAllUserEntitiesWithIdAndTitle(): array
    {
        $query = $this->createQueryBuilder('user')
            ->select('user.id, user.username')
            ->where('user.is_active = :isActive')
            ->setParameter('isActive', true);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @return array
     */
    public function findAllActiveAdmins(): array
    {
        $query = $this->createQueryBuilder('user')
            ->select()
            ->where('user.is_active = :isActive')
            ->andWhere('user.roles LIKE :adminRole')
            ->setParameters([
                'isActive' => true,
                'adminRole' => 'ROLE_ADMIN'
            ]);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param $paginator
     * @param bool $array
     * @return array
     */
    private function formatData($paginator, $array = false): array
    {
        $response = [];
        /** @var User $data */
        foreach ($paginator as $data) {
            if ($array) {
                $response[] = $this->processArrayData($data);
            } else {
                $response[] = $this->processData($data);
            }
        }

        return $response;
    }

    /**
     * @param User $data
     * @return array
     */
    private function processData(User $data): array
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

    /**
     * @param $data
     * @return array
     */
    private function processArrayData(array $data): array
    {
        $detailData = $data['detailData'];
        $detailDataArray = [];
        if ($detailData) {
            $detailDataArray = [
                'id' => $data['detailData']['id'],
                'name' => $data['detailData']['name'],
                'surname' => $data['detailData']['surname'],
                'title_before' => $data['detailData']['title_before'],
                'title_after' => $data['detailData']['title_after'],
                'function' => $data['detailData']['function'],
                'mobile' => $data['detailData']['mobile'],
                'tel' => $data['detailData']['tel'],
                'fax' => $data['detailData']['fax'],
                'signature' => $data['detailData']['signature'],
                'street' => $data['detailData']['street'],
                'city' => $data['detailData']['city'],
                'zip' => $data['detailData']['zip'],
                'country' => $data['detailData']['country'],
                'facebook' => $data['detailData']['facebook'],
                'twitter' => $data['detailData']['twitter'],
                'linkdin' => $data['detailData']['linkdin'],
                'google' => $data['detailData']['google'],
            ];
        }

        $company = $data['company'];
        $companyArray = [];
        if ($company) {
            $companyArray = [
                'id' => $company['id'],
                'title' => $company['title']
            ];
        }

        $response = [
            'id' => $data['id'],
            'username' => $data['username'],
            'email' => $data['email'],
            'language' => $data['language'],
            'is_active' => $data['is_active'],
            'image' => $data['image'],
            'detailData' => $detailDataArray,
            'user_role' => [
                'id' => $data['user_role']['id'],
                'title' => $data['user_role']['title'],
                'description' => $data['user_role']['description'],
                'homepage' => $data['user_role']['homepage'],
                'acl' => json_decode($data['user_role']['acl']),
                'order' => $data['user_role']['order'],
            ],
            'company' => $companyArray
        ];

        return $response;
    }
}
