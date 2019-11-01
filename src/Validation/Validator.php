<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/01/2019
 * Time: 16:50
 */

namespace Keiryo\Validation;

use Rakit\Validation\Validation;
use Rakit\Validation\Validator as BaseValidator;

class Validator extends BaseValidator
{

    /**
     * @param array $inputs
     * @param array $rules
     * @param array $messages
     * @return Validation
     */
    public function validate(array $inputs, array $rules, array $messages = []): Validation
    {
        $validation = parent::validate($inputs, $rules, $messages);

        if ($validation->fails()) {
            throw new ValidationException($validation->errors());
        }

        return $validation;
    }
}
