<?php

namespace API\CoreBundle\Controller\User;

use API\CoreBundle\Model\BaseModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class UsersController
 * @package API\CoreBundle\Controller
 */
class UsersController extends Controller
{

    /**
     * @ApiDoc(
     *  description="Returns a list of Users with basic Info (user Entity)",
     *  statusCodes={
     *      200="Returned when successful",
     *  })
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listWithBasicInfoAction()
    {
        $baseModel = $this->get('api_base.model');
        $tableName = 'user';
        $values = ['email','username','is_active'];

        $users = $baseModel->fetchResults($tableName,$values);

        return $this->json(['users' => $users]);
    }

    /**
     * @ApiDoc(
     *  description="Returns a list of Users with address Info (user Entity, UserAddress Entity)",
     *  statusCodes={
     *      200="Returned when successful",
     *  })
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listWithAllInfoAction()
    {
        $userModel = $this->get('api_user.model');
        $values = ['email','username','is_active', 'street', 'city', 'zip', 'country', 'name', 'surname', 'titleBefore', 'titleAfter', 'function', 'mobile', 'tel', 'fax', 'signature'];

        $users = $userModel->getAllUsersWithAllInfo($values);

        return $this->json(['users' => $users]);
    }

}
