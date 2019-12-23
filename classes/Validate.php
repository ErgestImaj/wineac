<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Validate
{

    private $_passed = false,
        $_errors = array();

    /**
     * Validate all fields
     * @param $source
     * @param array $items
     * @return $this
     */
    public function check($source, $items = array())
    {
        foreach ($items as $item => $rules) {
            foreach($rules as $rule => $rule_value) {

                $value =  trim($source[$item]);

                if ($rule === 'required' && empty($value)) {
                    $this->addError(
                        sprintf(__("%s is required",EVOLVE_AL),$this->getAttribureName($item))
                    );
                } else if(!empty($value)){

                    switch ($rule) {
                        case 'min':
                            if (strlen($value) < $rule_value) {
                                $this->addError(
                                    sprintf(__('%s must be a minimun of %d characters.',EVOLVE_AL),$this->getAttribureName($item),$rule_value)
                                );
                            }
                            break;
                        case 'max':
                            if (strlen($value) > $rule_value) {
                                $this->addError(
                                    sprintf(__('%s must be a maximum  of %d characters.',EVOLVE_AL),$this->getAttribureName($item),$rule_value)
                                );
                            }
                            break;
                        case 'email':
                            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $this->addError(
                                           sprintf(__("%s is not a valid email address",EVOLVE_AL),$value)
                                );
                            }
                            break;
                        default:
                            # code...
                            break;
                    }
                }
            }
        }

        if (empty($this->_errors)) {
            $this->_passed = true;
        }
        return $this;
    }

    /**
     * @param $error
     */
    private function addError($error)
    {
        $this->_errors[] = $error;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->_errors;
    }

    /**
     * @return bool
     */
    public function passed()
    {
        return $this->_passed;
    }

    /**
     * @param $val
     * @return string
     */
    public function getAttribureName($val){
       return ucfirst(str_replace('_', ' ', $val));
    }
}