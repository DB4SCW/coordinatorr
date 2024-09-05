<?php

function skd_validatorerrors(\Illuminate\Validation\Validator $validator) : string
{
    return implode(" | ", $validator->errors()->all());
}

function swolf_getcallsignwithoutadditionalinfo(string $input) : string
{
    $result = strtoupper($input);
    $result = preg_replace("/^[A-Z, 0-9]{1,3}\//", "", $result); //delete prefix
    $result = preg_replace("/\/\w{0,}$/", "", $result); //delete suffix
    
    //return pure callsign
    return $result;
}