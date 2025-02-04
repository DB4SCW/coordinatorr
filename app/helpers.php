<?php

use App\Models\Callsign;

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

function db4scw_assure_appmode_in_env() : void
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
    if ($keyPosition !== false) 
    {
        //do nothing
        return;
    } else {
        $envcontent .= "\n{$key}={$value}";
    }

    //write new env file
    try 
    {
        file_put_contents($envFile, $envcontent);
    } catch (\Throwable $th) 
    {
        //nothing we can do here if that does not work...
    }

    //close function
    return;
}

function db4scw_get_new_distict_muted_color() : string 
{
    //define some colors
    $colors = [
        "#4B4E6D",
        "#5E6A71",
        "#7A7265",
        "#8C8377",
        "#746A6D",
        "#595E68",
        "#6B7A78",
        "#83786E",
        "#4F5358",
        "#6A6164",
        "#5C6E6F",
        "#76797A",
        "#857D72",
        "#7C6E75",
        "#50555A",
        "#697270",
        "#7A847A",
        "#645B5D",
        "#586462",
        "#73776D"
    ];

    //shuffle the array
    shuffle($colors);

    //get existing colors
    $existing_colors = Callsign::where('calendar_color', '!=', null)->distinct()->select('calendar_color')->get()->pluck('calendar_color')->toArray();

    //get remaining colors
    $remaining_colors = array_diff($colors, $existing_colors);

    //if we got remaining colors, use the first random one, else use the first random color from the whole list
    if(count($remaining_colors) > 0)
    {
        return $remaining_colors[0];
    }else
    {
        return $colors[0];
    }
}

function stalinsort(array $array, bool $reverse = false): array {
    
    //if array is empty, return empty array
    if (empty($array)) {
        return [];
    }

    //only add elements that are already sorted to the array, eliminate the rest of the elements
    foreach ($array as $element) {

        //first element is always fine
        if(empty($sortedArray))
        {
            $sortedArray[] = $element;
            continue;
        }

        //only add element if greater or equal than the last one
        if ($element >= end($sortedArray)) {
            $sortedArray[] = $element;
        }
    }

    //return result, reverse if needed
    return $reverse ? array_reverse($sortedArray) : $sortedArray;
}