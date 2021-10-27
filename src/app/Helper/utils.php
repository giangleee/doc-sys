<?php

use App\Models\Branch;
use App\Models\Division;
use App\Models\Store;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

function formatException($exception)
{
    return [
        'exception' => get_class($exception),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'message' => $exception->getMessage(),
    ];
}

function responseError($code, $message, $errors = [])
{
    $output = [
        'message' => $message,
        'errors' => $errors
    ];
    return response()->json($output, $code);
}

function responseOK($data = null)
{
    // if (is_null($data)) {
    //     return response(null, 200)->header('Content-Type', 'application/json');
    // }
    return response()->json($data, 200);
}

function responseCreated($data = null)
{
    return response()->json(($data ?? []), 201);
}

function responseCreateUniqueSoftDelete($data = null)
{
    return response()->json(($data ?? []), 205);
}

function responseUpdatedOrDeleted()
{
    return response(null, 204);
}

function responseBadRequest($message)
{
    return response()->json(['message' => $message], 400);
}

function responseValidate($errors, $message = 'The given data was invalid.')
{
    $output = [
        'message' => $message,
        'errors' => $errors
    ];
    return response()->json($output, 422);
}

function generateImageToken($filename)
{
    return generateTokenMd5($filename);
}

function generateToken($length = 20, $prefix = null)
{
    return $prefix . Str::random($length - strlen($prefix));
}

function generateTokenMd5($filename)
{
    return md5($filename . microtime());
}

function extractByKeys(array $keys, array $sourceArr)
{
    $subArr = [];
    foreach ($sourceArr as $key => $value) {
        if (in_array($key, $keys) && !empty($value)) {
            $subArr[$key] = $value;
        }
    }
    return $subArr;
}

function extractValuesByKeys(array $keys, array $sourceArr)
{
    return array_values(extractByKeys($keys, $sourceArr));
}

function arrayValueByKey($key, array $arr)
{
    return $arr[$key] ?? null;
}

function addNewLine(string $text)
{
    return "\n $text";
}

function isDate($value)
{
    if (!$value) {
        return false;
    }
    try {
        new \DateTime($value);
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

function addSecondToTimeStart($time)
{
    if (is_null($time)) {
        return null;
    }
    return isDate($time) ? Carbon::parse($time)->format('Y-m-d H:i') . ':00' : null;
}

function toStartOfDay($time)
{
    if (is_null($time)) {
        return null;
    }
    return isDate($time) ? Carbon::parse($time)->startOfDay()->format('Y-m-d H:i:s') : null;
}

function toEndOfDay($time)
{
    if (is_null($time)) {
        return null;
    }
    return isDate($time) ? Carbon::parse($time)->endOfDay()->format('Y-m-d H:i:s') : null;
}

function addSecondToTimeEnd($time)
{
    if (is_null($time)) {
        return null;
    }
    return isDate($time) ? Carbon::parse($time)->format('Y-m-d H:i') . ':59' : null;
}

function stringContains(string $haystack, string $needle)
{
    return strpos($haystack, $needle) !== false;
}

function timeRangeArray($from, $to, $format = 'Y-m-d H:i:s')
{
    $carbonDateRange = CarbonPeriod::create($from, $to);
    $dateArr = [];
    foreach ($carbonDateRange as $date) {
        $dateArr[] = $date->format($format);
    }
    return $dateArr;
}

function getTimeToSendMail($from, $repeatValue, $numberOfDays)
{
    return Carbon::parse($from)
        ->addDays($repeatValue * $numberOfDays)
        ->format('Y-m-d');
}

function convertToHalfwidth($str)
{
    return mb_convert_kana($str, 'ak');
}

function convertToFullwidth($str)
{
    return mb_convert_kana($str, 'AK');
}

function multibyteTrim($string)
{
    return preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $string);
}

function formatDate($dateString, $format = 'Y-m-d')
{
    if (empty($dateString)) {
        return '';
    }
    return Carbon::parse($dateString)->format($format);
}

function trimAllItemInSerializeString($string)
{
    return array_map('multibyteTrim', explode(',', $string));
}

function convertCSVToArray($content, $removeHeader = 0, $toEncoding = null, $fromEncoding = null)
{
    $data = [];

    //convert string
    if (!is_null($toEncoding) && !is_null($fromEncoding)) {
        $content = mb_convert_encoding($content, $toEncoding, $fromEncoding);
    }

    //explode csv to array
    $content = str_replace("\r\n", "\n", $content);
    $content = explode("\n", $content);
    // $count = 0;
    foreach ($content as $key => $item) {
        if ($removeHeader && $key == 0) {
            continue;
        }
        //remove line empty
        if (!$item) {
            continue;
        }

        $convertToArr = str_getcsv($item);
        $data[] = trimAllItemInSerializeString(implode(',', $convertToArr));
    }

    return $data;
}

function convertHiragicodeToOffice()
{
    $hiragicode = public_path() . '/hiragicode.txt';
    $hiragicode = file_get_contents($hiragicode);
    $hiragicode = str_replace("\r\n", "\n", $hiragicode);
    $hiragicode = explode("\n", $hiragicode);
    $hiragicodeWithOffice = [];
    foreach ($hiragicode as $item) {
        $item = explode(',', $item);
        $hiragicodeWithOffice[$item[0]] = $item[1];
    }

    return $hiragicodeWithOffice;
}

function hiragicodeNotUse()
{
    $hiragicode = public_path() . '/hiragicode_not_use.txt';
    $hiragicode = file_get_contents($hiragicode);
    $hiragicode = explode(',', $hiragicode);

    return $hiragicode;
}

function userOfficeLevel1()
{
    $users = public_path() . '/user_level1.txt';
    $users = file_get_contents($users);
    $users = str_replace("\r\n", "\n", $users);
    $users = explode("\n", $users);
    $userOffices = [];
    foreach ($users as $item) {
        $item = explode(',', $item);
        $userOffices[$item[0]] = $item[1] ?? null;
    }

    return $userOffices;
}

function userOfficeLevel2()
{
    $users = public_path() . '/user_level2.txt';
    $users = file_get_contents($users);
    $users = str_replace("\r\n", "\n", $users);
    $users = explode("\n", $users);
    $userOffices = [];
    foreach ($users as $item) {
        $item = explode(',', $item);
        $userOffices[$item[0]] = $item[1] ?? null;
    }

    return $userOffices;
}

function mergeOptions($header, $param)
{
    if (empty($header)) {
        $header = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ];
    }
    $options = array_merge($header, $param);
    $options['http_errors'] = false;
    return $options;
}

function get($url, $param = [], $header = [])
{
    $client = new Client([
        'verify' => false
    ]);
    $options = mergeOptions($header, $param);
    $request = $client->get($url, $options);

    return ['statusCode' => $request->getStatusCode(), 'response' => $request->getBody()->getContents()];
}

function post($url, $param, $header = [])
{
    $client = new Client([
        'verify' => false
    ]);
    $options = mergeOptions($header, $param);
    $request = $client->post($url, $options);

    return ['statusCode' => $request->getStatusCode(), 'response' => $request->getBody()->getContents()];
}
