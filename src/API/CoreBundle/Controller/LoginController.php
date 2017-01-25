<?php

namespace API\CoreBundle\Controller;

use API\CoreBundle\Entity\User;
use API\TaskBundle\Entity\UserRole;
use Igsem\APIBundle\Services\StatusCodesHelper;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LoginController
 *
 * @package API\CoreBundle\Controller
 */
class LoginController extends Controller
{
    /**
     *
     * ### Response ###
     *
     *     {
     *        "token": "generated JWT Token"
     *        "id": 1,
     *        "username": "admin",
     *        "email": "admin@admin.sk",
     *        "language": "AJ",
     *        "isActive": "true",
     *        "profileImage": "image link",
     *        "name": "Admin",
     *        "surname": "Adminovic",
     *        "function": "Admin of project",
     *        "signature": null,
     *        "phone": "0904 444 085",
     *        "facebook": "www.facebook.com",
     *        "twitter": null,
     *        "linkdin": null,
     *        "google": null,
     *        "userRoleTitle": "ADMIN",
     *        "userRoleDescription": null,
     *        "userRoleHomepage": "/",
     *        "userRoleAcl": "[\"login_to_system\",\"share_filters\",\"project_shared_filters\",\"report_filters\",\"share_tags\",\"create_projects\",\"sent_emails_from_comments\",\"create_tasks\",\"create_tasks_in_all_projects\",\"update_all_tasks\",\"user_settings\",\"user_role_settings\",\"company_attribute_settings\",\"company_settings\",\"status_settings\",\"task_attribute_settings\",\"unit_settings\",\"system_settings\",\"smtp_settings\",\"imap_settings\"]",
     *        "userRoleOrder": 1,
     *     }
     *
     * @ApiDoc(
     *  description="Returns a JWT Token for authentication",
     *  parameters={
     *      {"name"="username", "dataType"="string", "required"=true, "format"="POST", "description"="username for login purposes"},
     *      {"name"="password", "dataType"="string", "required"=true, "format"="POST", "description"="password for login purposes"}
     *  },
     *  statusCodes={
     *      200="The request has succeeded",
     *      403="Incorrect credentials",
     *      404="User not found"
     *  }
     * )
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \LogicException
     */
    public function tokenAuthenticationAction(Request $request)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        /** @var User $user */
        $user = $this->getDoctrine()->getRepository('APICoreBundle:User')
            ->findOneBy(['username' => $username]);

        if (!$user) {
            return $this->json(['message' => StatusCodesHelper::USER_NOT_FOUND_MESSAGE], StatusCodesHelper::USER_NOT_FOUND_CODE);
        }

        // password check
        if (!$this->get('security.password_encoder')->isPasswordValid($user, $password)) {
            return $this->json(['message' => StatusCodesHelper::INCORRECT_CREDENTIALS_MESSAGE], StatusCodesHelper::INCORRECT_CREDENTIALS_CODE);
        }

        //check if account was not deleted or is not active
        if (!$user->isEnabled()) {
            return $this->json(['message' => StatusCodesHelper::ACCOUNT_DISABLED_MESSAGE], StatusCodesHelper::UNAUTHORIZED_CODE);
        }

        $userImage = $user->getImage();
        if (null !== $userImage) {
            $imageLink = $this->generateUrl('cdn_load_file', ['slug' => $userImage]);
        } else {
            $imageLink = null;
        }

        $userBaseArray = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'language' => $user->getLanguage(),
            'isActive' => $user->getIsActive(),
            'profileImage' => $imageLink,
        ];

        $userRole = [];
        /** @var UserRole $ur */
        $ur = $user->getUserRole();
        if ($ur) {
            $userRole = [
                'userRoleTitle' => $ur->getTitle(),
                'userRoleDescription' => $ur->getDescription(),
                'userRoleHomepage' => $ur->getHomepage(),
                'userRoleAcl' => $ur->getAcl(),
                'userRoleOrder' => $ur->getOrder()
            ];
        }

        $detailData = [];
        if ($user->getDetailData()) {
            $detailData = [
                'name' => $user->getDetailData()->getName(),
                'surname' => $user->getDetailData()->getSurname(),
                'function' => $user->getDetailData()->getFunction(),
                'signature' => $user->getDetailData()->getSignature(),
                'phone' => $user->getDetailData()->getMobile(),
                'facebook' => $user->getDetailData()->getFacebook(),
                'twitter' => $user->getDetailData()->getTwitter(),
                'linkdin' => $user->getDetailData()->getLinkdin(),
                'google' => $user->getDetailData()->getGoogle(),
            ];
        }

        $allDetailsAboutUser = array_merge($userBaseArray, $detailData, $userRole);

        // Use LexikJWTAuthenticationBundle to create JWT token that hold only information about user name
        $token = $this->get('lexik_jwt_authentication.encoder.default')
            ->encode($allDetailsAboutUser);

        // Return genereted token
        return $this->json(['token' => $token], StatusCodesHelper::SUCCESSFUL_CODE);
    }
}
