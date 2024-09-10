<?php

function skd_validatorerrors(\Illuminate\Validation\Validator $validator) : string
{
    return implode(" | ", $validator->errors()->all());
}

function db4scw_getcallsignwithoutadditionalinfo(string $input) : string
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

function db4scw_assure_appmode_in_env()
{
    //define env key
    $key = "COORDINATORR_MODE";
    $value = "SINGLEOP";

    //get environment file
    $envFile = app()->environmentFilePath();
    $envcontent = file_get_contents($envFile);

    //check if key exists
    $keyPosition = strpos($envcontent, "{$key}=");

    // If key exists, replace it. Otherwise, add the new key-value pair.
    if ($keyPosition !== false) {
        //do nothing
    } else {
        $envcontent .= "\n{$key}={$value}";
    }

    //write new env file
    try {
        file_put_contents($envFile, $envcontent);
    } catch (\Throwable $th) {
        //nothing we can do here if that does not work...
    }

    //close function
    return;
}