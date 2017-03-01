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
     * @return array
     */
    public function getCustomUsers(int $page = 1, $isActive)
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
                ->orderBy('u.id')
                ->distinct()
                ->where('u.is_active = :isActive')
                ->setParameter('isActive', $isActiveParam);
        } else {
            $query = $this->createQueryBuilder('u')
                ->select('u,d,userRole,company,companyData,companyAttribute')
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->leftJoin('u.company', 'company')
                ->leftJoin('company.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute')
                ->orderBy('u.id')
                ->distinct();
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
     * @return array
     */
    public function getUsersSearch($term, int $page, $isActive):array
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
                ->where('u.is_active = :isActive');
            $parameters['isActive'] = $isActiveParam;
        } else {
            $query = $this->createQueryBuilder('u')
                ->select('u,d,userRole,company,companyData,companyAttribute')
                ->leftJoin('u.detailData', 'd')
                ->leftJoin('u.user_role', 'userRole')
                ->leftJoin('u.company', 'company')
                ->leftJoin('company.companyData', 'companyData')
                ->leftJoin('companyData.companyAttribute', 'companyAttribute');
        }

        if ($term) {
            $query->andWhere('u.username LIKE :term')
                ->orWhere('u.email LIKE :term')
                ->orWhere('company.title LIKE :term');
            $parameters['term'] = '%' . $term . '%';
        }

        $query->setParameters($parameters);
        $query->setMaxResults(self::LIMIT);
        $query->setFirstResult(self::LIMIT * $page - self::LIMIT);

        return $query->getQuery()->getArrayResult();
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

        return $query->getArrayResult();
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

            $response[] = [
                'id' => $data->getId(),
                'username' => $data->getUsername(),
                'email' => $data->getEmail(),
                'language' => $data->getLanguage(),
                'is_active' => $data->getIsActive(),
                'image' => $data->getImage(),
                'detailData' => $detailDataArray,
                'user_role'=>[
                    'id' => $data->getUserRole()->getId(),
                    'title' => $data->getUserRole()->getTitle(),
                    'description' => $data->getUserRole()->getDescription(),
                    'homepage' => $data->getUserRole()->getHomepage(),
                    'acl' => $data->getUserRole()->getAcl(),
                    'order' => $data->getUserRole()->getOrder(),
                ]
            ];
        }
        return $response;
    }
}
