<?php
/**
 * Created by PhpStorm.
 * User: websolutions
 * Date: 10/21/16
 * Time: 5:13 PM
 */

namespace API\CoreBundle\Services;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProcessEntity
{
    /** @var EntityManager */
    private $em;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(EntityManager $em , ValidatorInterface $validator)
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
     * @return array|bool Returns false if entity persisted errors if validation failed
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function processEntity($entity , array $data = [])
    {
        $this->fillEntity(get_class($entity) , $entity , $data);

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
     * @param array     $data
     */
    public function fillEntity($class , $entity , array $data)
    {
        foreach ($data as $method => $value) {
            if (property_exists($class , $method)) {
                $m = 'set' . $method;
                $entity->$m($value);
            }
        }
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
                'field'   => $error->getPropertyPath() ,
                'message' => $error->getMessage() ,
                'value'   => $error->getInvalidValue() ,
            ];
        }

        return $return;
    }
}