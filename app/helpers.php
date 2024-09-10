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

function db4scw_add_mode_constrictions($input, $appmode, $bandid = null, $modeid = null)
{
    switch ($appmode) {
        case 'SINGLEOP':
            //do nothing additional
            break;
        case 'MULTIOPBAND':
            //add band constriction
            $input->where('band_id', $bandid);
            break;
        case 'MULTIOPMODE':
            //add band and mode  constriction
            $input->where('band_id', $bandid)->where('mode_id', $modeid);
            break;
    }

    return $input;
}