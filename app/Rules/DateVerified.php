<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DateVerified implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value){
        
        $Signo = (strstr($value, '/')) ? '/' : '-';          

        if($attribute == "arrival_date"){
            if(request()->type_trip == 1 || request()->type_trip == 2){
                if($value != null || $value != ""){
                    $date = explode($Signo, $value);
                    if(checkdate($date[1],$date[0],$date[2])){
                        if($value != null || $value != ''){
                            return true;
                        }
                    }
                    return false;
                }
                return true;
            }
        }
        
        if($attribute == "departure_date"){
            if(request()->type_trip == 1 || request()->type_trip == 3){
                if($value != null || $value != ""){
                    
                    $date = explode($Signo, $value);
                    
                    if(checkdate($date[1],$date[0],$date[2])){
                        if($value != null || $value!=''){
                            return true;
                        }
                    }                    
                    return false;
                }
            }            
            return true;
        }
        
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // return 'The validation error message.';
        return 'The :attribute is bad, fix you please.';
    }
}
