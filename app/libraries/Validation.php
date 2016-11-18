<?php

/**
 * Wrapper class for valitron validation library.
 *
 * @author Leo Rudin
 */

namespace App\Libraries;

use App\Accessor;
use Valitron\Validator as v;

class Validation extends Accessor
{
    /**
     * Contains array of errors.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Validates an array of input data against definitions.
     *
     * @param  $req
     * @param  $definitions
     * @return $this
     */
    public function validate($req, $definitions)
    {
        $v = new \Valitron\Validator($req->getParams());

        $labels = array();

        foreach ($definitions as $field => $rules) {
            $labels[explode('|', $field)[0]] = explode('|', $field)[1];
            foreach ($rules as $rule) {
                if (!is_array($rule)) {
                    $v->rule($rule, explode('|', $field)[0]);
                } else {
                    if (isset($rule[2])) {
                        $v->rule($rule[0], explode('|', $field)[0], $rule[1], $rule[2]);
                    } else {
                        $v->rule($rule[0], explode('|', $field)[0] , $rule[1]);
                    }
                }
            }
        }

        $v->labels($labels);
        $v->validate();

        $_SESSION['errors'] = $v->errors();
        $this->errors = $v->errors();

        return $this;
    }

    /**
     * Returns error array.
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Returns true if validation failed.
     *
     * @return bool
     */
    public function failed()
    {
        return !empty($this->errors);
    }
}