<?php

function covid19ImpactEstimator($data)
{
    $dataDecode = json_decode(json_encode($data));
    //$dataDecode = json_decode($data);
    //$dataDecode = json_decode($data, true);
    $name = $dataDecode->{'region'}->{'name'}; //$dataDecode['region']['name'];
    $avgAge = $dataDecode->{'region'}->{'avgAge'}; //$dataDecode['region']['avgAge'];
    $avgDailyIncomeInUSD = $dataDecode->{'region'}->{'avgDailyIncomeInUSD'}; //$dataDecode['region']['avgDailyIncomeInUSD'];
    $avgDailyIncomePopulation = $dataDecode->{'region'}->{'avgDailyIncomePopulation'}; //$dataDecode['region']['avgDailyIncomePopulation'];

    $periodType = $dataDecode->{'periodType'}; //$dataDecode['periodType'];
    $timeToElapse = $dataDecode->{'timeToElapse'}; //$dataDecode['timeToElapse'];
    $reportedCases = $dataDecode->{'reportedCases'}; //$dataDecode['reportedCases'];
    $population = $dataDecode->{'population'}; //$dataDecode['population'];
    $totalHospitalBeds = $dataDecode->{'totalHospitalBeds'}; //$dataDecode['totalHospitalBeds'];

    //infected factor = No_days / double_day
    //Estimate the number of infected by x (28)days, note that currentlyInfected doubles every 3 days
    switch ($periodType){
    case 'days':
            $factor = floor(($timeToElapse * 1) / 3);
            $dayPeriod = $timeToElapse * 1;
        break;
    case 'weeks':
        $factor = floor(($timeToElapse * 7) / 3);
        $dayPeriod = $timeToElapse * 7;
        break;
    case 'months':
        $factor = floor(($timeToElapse * 30) / 3);
        $dayPeriod = $timeToElapse * 30;
        break;
    }

    //Output Estimate for impact
    $impact_currentlyInfected = $reportedCases * 10;
    $impact_infectionsByRequestedTime = $impact_currentlyInfected * pow(2, $factor);
    $impact_severeCasesByRequestedTime = floor($impact_infectionsByRequestedTime * (15/100));
    $impact_hospitalBedsByRequestedTime = floor(($totalHospitalBeds * (35/100)) - $impact_severeCasesByRequestedTime);
    $impact_casesForICUByRequestedTime = floor($impact_infectionsByRequestedTime * (5/100));
    $impact_casesForVentilatorsByRequestedTime = floor($impact_infectionsByRequestedTime * (2/100));
    $impact_dollarsInFlight = round($impact_infectionsByRequestedTime * $avgDailyIncomePopulation * $avgDailyIncomeInUSD * $dayPeriod, 2);

    //Output Estimate for severeImpact
    $severeImpact_currentlyInfected = $reportedCases * 50;
    $severeImpact_infectionsByRequestedTime = $severeImpact_currentlyInfected * pow(2, $factor);
    $severeCasesByRequestedTime = floor($severeImpact_infectionsByRequestedTime * (15/100));
    $severe_hospitalBedsByRequestedTime = floor(($totalHospitalBeds * (35/100)) - $severeCasesByRequestedTime);
    $severe_casesForICUByRequestedTime = floor($severeImpact_infectionsByRequestedTime * (5/100));
    $severe_casesForVentilatorsByRequestedTime = floor($severeImpact_infectionsByRequestedTime * (2/100));
    $severe_dollarsInFlight = round($severeImpact_infectionsByRequestedTime * $avgDailyIncomePopulation * $avgDailyIncomeInUSD * $dayPeriod, 2);
    /**
     Return Output
    {
        data: {}, // the input data you got
        impact: {}, // your best case estimation
        severeImpact: {} // your severe case estimation
    }
     */
    $impactData = array('currentlyInfected' => $impact_currentlyInfected, 
      'infectionsByRequestedTime' => $impact_infectionsByRequestedTime,
      'severeCasesByRequestedTime' => $impact_severeCasesByRequestedTime,
      'hospitalBedsByRequestedTime' => $impact_hospitalBedsByRequestedTime,
      'casesForICUByRequestedTime' => $impact_casesForICUByRequestedTime,
      'casesForVentilatorsByRequestedTime' => $impact_casesForVentilatorsByRequestedTime,
      'dollarsInFlight' => $impact_dollarsInFlight
    );
    $severeImpactData = array('currentlyInfected' => $severeImpact_currentlyInfected, 
    'infectionsByRequestedTime' => $severeImpact_infectionsByRequestedTime,
    'severeCasesByRequestedTime' => $severeCasesByRequestedTime,
    'hospitalBedsByRequestedTime' => $severe_hospitalBedsByRequestedTime,
    'casesForICUByRequestedTime' => $severe_casesForICUByRequestedTime,
    'casesForVentilatorsByRequestedTime' => $severe_casesForVentilatorsByRequestedTime,
    'dollarsInFlight' => $severe_dollarsInFlight
    );
    $dataOuput = array('data' => $dataDecode, 'impact' => $impactData, 'severeImpact' => $severeImpactData);
    //return json_encode($dataOuput);
    return $dataOuput;
}
/**
///Testing data 
$reportedCases = '{
  "region":{
    "name":"Africa",
    "avgAge":19.7,
    "avgDailyIncomeInUSD":5,
    "avgDailyIncomePopulation":0.71
  },
  "periodType":"days",
  "timeToElapse":58,
  "reportedCases":674,
  "population":66622705,
  "totalHospitalBeds":1380614
}';
header("Content-Type: application/json");
echo covid19ImpactEstimator($reportedCases);
*/
?>