<?php

namespace API\CoreBundle\Services;

use API\TaskBundle\Entity\CompanyAttribute;
use API\TaskBundle\Entity\TaskAttribute;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ProcessEntity
 * @package API\CoreBundle\Services
 */
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
        $message = $this->fillEntity(ClassUtils::getClass($entity), $entity, $data);

        if (\count($message) > 0) {
            return $message;
        }

        /** @var ConstraintViolationList $errors */
        $errors = $this->validator->validate($entity);
        if (\count($errors) > 0) {
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
            if ('id' === $method) {
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

    /**
     * @param CompanyAttribute|TaskAttribute $attributeEntity
     * @param mixed $value
     * @return bool
     */
    public function checkDataValueFormat($attributeEntity, $value): bool
    {
        $expectedDataType = $attributeEntity->getType();

        switch ($expectedDataType) {
            case 'input':
                return (\is_string($value) || is_numeric($value));
                break;
            case 'text_area':
                return (\is_string($value) || is_numeric($value));
                break;
            case 'simple_select':
                $options = $attributeEntity->getOptions();
                return \in_array($value, $options, true);
                break;
            case 'multi_select':
                $options = $attributeEntity->getOptions();
                foreach ($value as $val) {
                    if (!\in_array($val, $options, true)) {
                        return false;
                    }
                }
                return true;
                break;
            case 'date':
                break;
            case 'decimal_number':
                break;
            case 'integer_number':
                break;
            case 'checkbox':
                break;
            default:
                return false;
        }
    }

    /**
     * @param CompanyAttribute|TaskAttribute $attribute
     * @return string
     */
    public function returnExpectedDataFormat($attribute): string
    {
        $type = $attribute->getType();

        switch ($type) {
            case 'input':
                return 'STRING or NUMBER';
                break;
            case 'text_area':
                return 'STRING or NUMBER';
                break;
            case 'simple_select':
                return 'one of the OPTIONS: ' . implode(",", $attribute->getOptions());
                break;
            case 'multi_select':
                return 'one or more of the OPTIONS: ' . implode(",", $attribute->getOptions());
                break;
            case 'date':
                return 'the DATE';
                break;
            case 'decimal_number':
                return 'DECIMAL NUMBER';
                break;
            case 'integer_number':
                return 'INTEGER';
                break;
            case 'checkbox':
                return 'TRUE or FALSE VALUE';
                break;
            default:
                return 'not defined';
        }
    }
}