<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 05/02/2019
 * Time: 18:52
 */

namespace Keiryo\Helper;

use Rakit\Validation\Validation;
use Keiryo\Validation\Validator;

trait ValidatorTrait
{

    /**
     * Validate incoming inputs
     *
     * @param array $input
     * @param array $messages
     * @return Validation
     */
    protected function validate(array $input, array $messages = []): Validation
    {
        return (new Validator())
            ->validate($input, $this->getRules(), $messages);
    }

    /**
     * Get validation rules
     *
     * @return array
     */
    abstract protected function getRules(): array;
}
