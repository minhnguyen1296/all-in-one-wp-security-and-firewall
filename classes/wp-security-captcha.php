<?php
class AIOWPSecurity_Captcha
{

    function __construct() 
    {
        //NOP
    }
    
    function display_captcha_form()
    {
        $cap_form = '<p class="aiowps-captcha"><label>'.__('Please enter an answer in digits:','aiowpsecurity').'</label>';
        $cap_form .= '<div class="aiowps-captcha-equation"><strong>';
        $maths_question_output = $this->generate_maths_question();
        $cap_form .= $maths_question_output . '</strong></div></p>';
        echo $cap_form;
    }
    
    function generate_maths_question()
    {
        //For now we will only do plus, minus, multiplication
        $equation_string = '';
        $operator_type = array('&#43;', '&#8722;', '&#215;');
        
        $operand_display = array('word', 'number');
        
        //let's now generate an equation
        $first_digit = rand(1,20);
        if($operand_display[rand(0,1)] == 'word'){
            $first_operand = $this->number_word_mapping($first_digit);
        }else{
            $first_operand = $first_digit;
        }
        
        $operator = $operator_type[rand(0,2)];
        
        $second_digit = rand(1,20); 
        if($operand_display[rand(0,1)] == 'word'){
            $second_operand = $this->number_word_mapping($second_digit);
        }else{
            $second_operand = $second_digit;
        }

        //Let's caluclate the result and construct the equation string
        if($operator === '&#43;')
        {
            //Addition
            $result = $first_digit+$second_digit;
            $equation_string = $first_operand . ' ' . $operator . ' ' . $second_operand . ' = ';
        }
        else if($operator === '&#8722;')
        {
            //Subtraction
            //If we are going to be negative let's swap operands around
            if($first_digit < $second_digit){
                $equation_string = $second_operand . ' ' . $operator . ' ' . $first_operand . ' = ';
                $result = $second_digit-$first_digit;
            }else{
                $equation_string = $first_operand . ' ' . $operator . ' ' . $second_operand . ' = ';
                $result = $first_digit-$second_digit;
            }
        }
        elseif($operator === '&#215;')
        {
            //Multiplication
            $equation_string = $first_operand . ' ' . $operator . ' ' . $second_operand . ' = ';
            $result = $first_digit*$second_digit;
        }
        
        $equation_string .= '<input type="text" size="2" length="2" id="aiowps-captcha-answer" name="aiowps-captcha-answer" value="" />';
        //Save the result in a transient
        AIOWPSecurity_Utility::is_multisite_install() ? set_site_transient('aiowps_captcha', $result, 120) : set_transient('aiowps_captcha', $result, 120);
        return $equation_string;
    }
    
    function number_word_mapping($num)
    {
        $number_map = array(
            1 => __('one', 'aiowpsecurity'),
            2 => __('two', 'aiowpsecurity'),
            3 => __('three', 'aiowpsecurity'),
            4 => __('four', 'aiowpsecurity'),
            5 => __('five', 'aiowpsecurity'),
            6 => __('six', 'aiowpsecurity'),
            7 => __('seven', 'aiowpsecurity'),
            8 => __('eight', 'aiowpsecurity'),
            9 => __('nine', 'aiowpsecurity'),
            10 => __('ten', 'aiowpsecurity'),
            11 => __('eleven', 'aiowpsecurity'),
            12 => __('twelve', 'aiowpsecurity'),
            13 => __('thirteen', 'aiowpsecurity'),
            14 => __('fourteen', 'aiowpsecurity'),
            15 => __('fifteen', 'aiowpsecurity'),
            16 => __('sixteen', 'aiowpsecurity'),
            17 => __('seventeen', 'aiowpsecurity'),
            18 => __('eighteen', 'aiowpsecurity'),
            19 => __('nineteen', 'aiowpsecurity'),
            20 => __('twenty', 'aiowpsecurity'),
        ); 
        return $number_map[$num];
    }

}