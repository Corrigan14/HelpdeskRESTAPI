<?php
/**
 * Created by PhpStorm.
 * User: websolutions
 * Date: 10/21/16
 * Time: 5:13 PM
 */

namespace API\CoreBundle\Services;


use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProcessEntity
{
    /** @var EntityManager */
    private $em;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(EntityManager $em, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    /**
     * Get an entity pre-fill with data check for errors, return false on success errors on error
     *
     * @param       $entity
     * @param array $data
     *
     * @return Response|array|bool
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function processEntity($entity, array $data = [])
    {
        $message = $this->fillEntity(get_class($entity), $entity, $data);

        if (count($message) > 0) {
            return $message;
        }

        /** @var ConstraintViolationList $errors */
        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            return $this->getErrorsFromValidation($errors);
        }

        return false;
    }


    /**
     * Fills entity with data if method exists, pay attention on security e.g. Roles, Passwords, Emails
     *
     * @param \stdClass $class
     * @param           $entity
     * @param array $data
     * @return array
     */
    public function fillEntity($class, $entity, array $data)
    {
        $message = [];
        foreach ($data as $method => $value) {
            if('id' === $method){
                $message[] = 'You can not change ID of entity!';
            }
            if (property_exists($class, $method)) {
                $method = str_replace('_', '', $method);
                $m = 'set' . $method;
                $entity->$m($value);
            }
        }

        return $message;
    }

    /**
     * @param ConstraintViolationList $errors
     *
     * @return array
     */
    public function getErrorsFromValidation(ConstraintViolationList $errors): array
    {
        $return = [];

        foreach ($errors as $error) {
            $return[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
                'value' => $error->getInvalidValue(),
            ];
        }

        return $return;
    }
}